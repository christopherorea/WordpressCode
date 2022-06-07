<?php
//Add product variation
function add_to_cart_form_shortcode( $atts ) {
        if ( empty( $atts ) ) {
            return '';
        }

        if ( ! isset( $atts['id'] ) && ! isset( $atts['sku'] ) ) {
            return '';
        }

        $args = array(
            'posts_per_page'      => 1,
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => 1,
            'no_found_rows'       => 1,
        );

        if ( isset( $atts['sku'] ) ) {
            $args['meta_query'][] = array(
                'key'     => '_sku',
                'value'   => sanitize_text_field( $atts['sku'] ),
                'compare' => '=',
            );

            $args['post_type'] = array( 'product', 'product_variation' );
        }

        if ( isset( $atts['id'] ) ) {
            $args['p'] = absint( $atts['id'] );
        }

        $single_product = new WP_Query( $args );

        $preselected_id = '0';


        if ( isset( $atts['sku'] ) && $single_product->have_posts() && 'product_variation' === $single_product->post->post_type ) {

            $variation = new WC_Product_Variation( $single_product->post->ID );
            $attributes = $variation->get_attributes();


            $preselected_id = $single_product->post->ID;


            $args = array(
                'posts_per_page'      => 1,
                'post_type'           => 'product',
                'post_status'         => 'publish',
                'ignore_sticky_posts' => 1,
                'no_found_rows'       => 1,
                'p'                   => $single_product->post->post_parent,
            );

            $single_product = new WP_Query( $args );
        ?>
            <script type="text/javascript">
                jQuery( document ).ready( function( $ ) {
                    var $variations_form = $( '[data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>"]' ).find( 'form.variations_form' );
                    <?php foreach ( $attributes as $attr => $value ) { ?>
                        $variations_form.find( 'select[name="<?php echo esc_attr( $attr ); ?>"]' ).val( '<?php echo esc_js( $value ); ?>' );
                    <?php } ?>
                });
            </script>
        <?php
        }

        $single_product->is_single = true;
        ob_start();
        global $wp_query;

        $previous_wp_query = $wp_query;

        $wp_query          = $single_product;

        wp_enqueue_script( 'wc-single-product' );
        while ( $single_product->have_posts() ) {
            $single_product->the_post()
            ?>
            <span class="woocommerce-Price-amount amount">
				<?php woocommerce_template_single_price(); ?>
			</span>
            <div class="single-product" data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>">
                <?php woocommerce_template_single_add_to_cart(); ?>
            </div>
            <?php
        }

        $wp_query = $previous_wp_query;

        wp_reset_postdata();
        return '<div class="woocommerce">' . ob_get_clean() . '</div>';
}
add_shortcode( 'add_to_cart_form', 'add_to_cart_form_shortcode' );