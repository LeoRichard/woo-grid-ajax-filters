<?php

/*
 Plugin Name: Woocommerce Ajax Grid
 Plugin URI: https://richardleo.me
 Author: Richard Leo
 Author URI: https://richardleo.me
 Version: 1.0.0
 Description: A simple WordPress plugin that helps you filter your products by category terms with Ajax
 */


 /**
  * wag_scripts Enqueue Scripts
  */
function wag_scripts() {

	wp_enqueue_style( 'wag-styles', plugin_dir_url( __FILE__ ) . 'assets/css/wag-stylesheet.css' );
	wp_enqueue_script( 'jquery' );
	wp_register_script( 'wag-js', plugin_dir_url( __FILE__ ) . 'assets/js/wag.js', 'jquery', '1.0');
	wp_enqueue_script( 'wag-js' );

	wp_localize_script( 'wag-js', 'wag_ajax_params',
		array(
        'wag_ajax_nonce' => wp_create_nonce( 'wag_ajax_nonce' ),
        'wag_ajax_url'   => admin_url( 'admin-ajax.php' ),
    )
  );

}

add_action( 'wp_enqueue_scripts', 'wag_scripts' );

/**
 * wag_shortcode_mapper Shortcode
 */
function wag_shortcode_mapper() {

	$filters_array = array(
			'filters'  => 'yes',
			'category' => '',
		);

	//display filters
	wag_filters( $filters_array );

    $content = '

    <div class="wag-ajax-container">
	    <div class="wag-loader">
	    	<img src="' . plugin_dir_url( __FILE__ ) . 'assets/throbber.gif' . '" alt="Loading...">
	    </div>
	    <div class="wag-filter-result"></div>
    </div>';

    echo $content;

}

add_shortcode( 'woo_ajax_grid', 'wag_shortcode_mapper' );


// Ajax actions
add_action('wp_ajax_wag_filter_products', 'wag_ajax_functions');
add_action('wp_ajax_nopriv_wag_filter_products', 'wag_ajax_functions');


/**
 * wag_ajax_functions Ajax main function
 */
function wag_ajax_functions() {

	// Verify nonce
	if ( !isset( $_POST['wag_ajax_nonce'] ) || !wp_verify_nonce( $_POST['wag_ajax_nonce'], 'wag_ajax_nonce' ) )
  	die('Permission denied');

	$term_ID = sanitize_text_field( intval($_POST['term_ID']) );

	if ( $term_ID == -1 ) {
		//post query
		$query = new WP_Query( array(

			'post_type' => 'product',
			'posts_per_page' => 12,
			'order'     => 'ASC',
		));
	} else {
		//post query
		$query = new WP_Query( array(

			'post_type' => 'product',
			'posts_per_page' => -1,
			'order'     => 'ASC',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => $term_ID,
				)
			)
		));
	}

	?>

	<div id="wag-isotope-container" class="wag-isotope-wrapper clearfix no-preloaderspin">
		<div class="products-grids wag-isotope-container dcl-m-4 clearfix">
			<div class="wag-flex">

				<?php

				if ( $query->have_posts() ):
					while ( $query->have_posts()): $query->the_post();

						wag_get_template_part( 'content', 'product' );

					endwhile;
					wp_reset_postdata();
				else:
					echo __('<h2>No products found</h2>', 'wag_td');
				endif;
				wp_reset_query();
				die();

				?>

			</div>
		</div>
	</div>

<?php
}

/**
 * woo_filters Display list of filters by category
 *
 * @param array  $filters_array
 */
function wag_filters( $filters_array = array() ) {

	global $post;
	$show_filters   = $filters_array['filters'];
	$i              = 0;
	$just_those_cat = array();

	if ( $filters_array['category'] != '' ) {
		$just_those_cat = explode( ',', preg_replace( '/\s+/', '', ( $filters_array['category'] ) ) );
	}

	$terms = get_terms( 'product_cat' );

	if ( $show_filters != 'yes-all' ) {
		$current = 'class="current"';
	} else {
		$current = '';
	}

	if ( $show_filters == 'yes-all' || wag_am_i_true( $show_filters ) ) {
		?>
        <nav class="filters-box filters animated animatedFadeInUp fadeInUp" id="filters">
            <ul>
				<?php if ( wag_am_i_true( $show_filters ) ) { ?>
                <li class="current"><a data-filter="*" data_id="-1" class="wag_taxonomy"><?php esc_html_e( 'All', 'woo-ajax-grid' ); ?></a></li>
				<?php }

			 foreach ( $terms as $term ) {

					$term_class = sanitize_html_class( $term->slug, $term->term_id );
					if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
						$term_class = $term->term_id;
					}

					if ( $i > 0 ) {
						$current = '';
					}
					if ( is_array( $just_those_cat ) && ! empty( $just_those_cat ) ) {
						if ( in_array( $term->term_id, $just_those_cat ) ) {
							?>
                <li>
                    <a data-filter="<?php echo esc_attr( $term_class ); ?>" data_id="<?php echo $term->term_id ?>" class="wag_taxonomy">
											<?php echo esc_attr( $term->name ); ?>
                        <span><?php echo esc_attr( $term->count ) ?></span>
										</a>
                </li>
							<?php
						}
					} else {

						?>
                <li>
                    <a data-filter="<?php echo esc_attr( $term_class ); ?>" data_id="<?php echo $term->term_id ?>" class="wag_taxonomy">
											<?php echo esc_attr( $term->name ); ?>
                        <span><?php echo esc_attr( $term->count ) ?></span>
										</a>
                </li>
					<?php }
					$i ++;
				} ?>
            </ul>
        </nav>

		<?php
	}
}

/**
 * am_i_true Boolean helper
 */
function wag_am_i_true( $boolean ) {
	if ( is_bool( $boolean ) ) {
		return $boolean;
	}
	switch ( $boolean ) {
		case '1':
		case 'true':
		case 'yes':
		case 'on':
			return true;
		break;
		default:
			return false;
		break;
	}
}

/**
 * wag_get_template_part Get templates
 */
function wag_get_template_part( $slug, $name = '' ) {

	$fallback = plugin_dir_path( __FILE__ ) . "templates/{$slug}-{$name}.php";
	$template = file_exists( $fallback ) ? $fallback : '';

	if ( $template ) {
		load_template( $template, false);
	}
}


/**
 * wag_add_to_cart_btn Add to cart button
 */
function wag_add_to_cart_btn( $id ) {

    $_product = wc_get_product( $id );
    $button_link = '#';
    $button_txt = '';
    $ajax_add_to_cart = '';
    $product_type = $_product->get_type();
		$button_txt = '<span class="svg_basket"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48">
										<path d="M34.42 18L25.66 4.89c-.38-.58-1.02-.85-1.66-.85-.64 0-1.28.28-1.66.85L13.58 18H4c-1.1 0-2 .9-2 2 0
										.19.03.37.07.54l5.07 18.54C7.61 40.76 9.16 42 11 42h26c1.84 0 3.39-1.24 3.85-2.93l5.07-18.54c.05-.16.08-.34.08-.53 0-1.1-.9-2-2-2h-9.58zM18
										 18l6-8.8 6 8.8H18zm6 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"></path></svg></span>';

		switch ( $product_type ) {
        case 'external':
            $button_link = get_permalink( $id );
        break;
        case 'grouped':
            $button_link = get_permalink( $id );
        break;
        case 'simple':
            $ajax_add_to_cart = 'ajax_add_to_cart';
            $button_link = esc_url( $current_url.'?add-to-cart='.$id );
        break;
        case 'variable':
            $button_link = get_permalink( $id );
        break;
        default:
            $button_link = get_permalink( $id );
    } ?>

		<a href="<?php echo esc_url( $button_link ); ?>" data-quantity="1" data-product_id="<?php echo esc_attr( $id ); ?>" data-product_sku=""
			 class="button product_type_variable add_to_cart_button  <?php echo esc_attr( $ajax_add_to_cart ); ?>" ><?php echo $button_txt; ?></a>

     <?php
}

/**
 * woo_filters Map Visual Composer shortcode with vc_map()
 */
add_action( 'vc_before_init', 'wag_vc_map' );

function wag_vc_map() {
	vc_map(
		array(
			'base'				=> 'woo_ajax_grid',
			'name'				=> 'Woocommerce Ajax Grid',
			'weight'			=> 490,
			'class'				=> 'dima-vc-element dima-vc-element-portfolio',
			'icon'				=> 'shopping',
			'category'		=> 'Woo Ajax Grid',
			'description'	=> 'Show products in grid with ajax category filters',
		)
	);
}
