<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

GLOBAL $product;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

// Check stock status
$out_of_stock = get_post_meta( $post->ID, '_stock_status', true ) == 'outofstock';

// Extra post classes
$classes   = array();
$classes[] = 'wag-product';
$classes[] = 'wag-product article-separation clearfix woo-hentry has-hover';

if ( $out_of_stock ) {
	$classes[] = 'out-of-stock';
}
?>
<article <?php wc_product_class( $classes ); ?> >

		<?php
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'woocommerce_thumbnail' );
		?>

	<div class="product-img box-image">
		<figure>
			<img src="<?php  echo $image[0]; ?>" data-id="<?php echo $post->ID; ?>">
		</figure>
		<a class="all-over-thumb-link" href="<?php the_permalink(); ?>" aria-label="<?php the_title(); ?>"></a>
		<div class="post-icon link_overlay">
			<ul class="icons-media">
				<li>
					<a href="<?php the_permalink(); ?>" aria-label="More">
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48">
							<path d="M12 20c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm24 0c-2.21 0-4 1.79-4 4s1.79 4 4
							4 4-1.79 4-4-1.79-4-4-4zm-12 0c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4z"></path>
						</svg>
					</a>
				</li>
				<li>
					<?php wag_add_to_cart_btn( $post->ID ); ?>
				</li>
			</ul>
		</div>
	</div>

	<div class="wag-product-content">
    <header class="entry-header">
        <div class="product-content">
            <a href="<?php the_permalink(); ?>">
                <h5 class="product-name"><?php the_title(); ?></h5>
                <span class="wag-divider line-start line-hr small-line"></span>
            </a>
            <div class="wag-price price"><?php echo( $product->get_price_html() ); ?></div>
			<?php
			if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) {

				$rating = wc_get_rating_html( $product->get_average_rating() );

				if ( ! empty( $rating ) ) {
					echo '<div class="star-rating-container aggregate">' . $rating . '</div>';
				} else {
			?>
            <div class="star-rating"
                 title="<?php echo sprintf( esc_html__( 'Rated %d out of 5', 'woocommerce' ), $rating ) ?>"></div>
			<?php
				}
			}
			?>

        </div>
    </header>
	</div>
</article>
