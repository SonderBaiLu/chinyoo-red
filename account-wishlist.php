<?php
/**
 * Account — My Wishlist。
 *
 * 路由：/wishlist/（rewrite，见 inc/account-routes.php）
 * 该页按用户渲染，已由 inc/account-routes.php 设置 no-store。
 *
 * @package chinyoo-red
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$ew_ids = ew_wishlist_ids();

$ew_products = array();
foreach ( $ew_ids as $ew_pid ) {
	if ( 'product' !== get_post_type( $ew_pid ) || 'publish' !== get_post_status( $ew_pid ) ) {
		continue;
	}
	$ew_product = function_exists( 'wc_get_product' ) ? wc_get_product( $ew_pid ) : null;
	if ( $ew_product ) {
		$ew_products[] = $ew_product;
	}
}
?>

<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e( 'Home' ); ?></a>
	<span> › </span><span><?php ew_esc_html_e( 'My Wishlist' ); ?></span>
</div></div>

<div class="page-head" data-animation-wrap>
	<div class="container">
		<h1 data-animation="1"><?php ew_esc_html_e( 'My Wishlist' ); ?></h1>
		<p class="entry-content" data-animation="2" id="ew-wishlist-count">
			<?php
			$ew_count = count( $ew_products );
			echo esc_html(
				sprintf(
					/* translators: %d: number of saved products */
					ew__( 1 === $ew_count ? '%d model saved' : '%d models saved' ),
					$ew_count
				)
			);
			?>
		</p>
	</div>
</div>

<div class="container account-shell">
	<?php if ( ! empty( $ew_products ) ) : ?>
		<div class="catalog-grid" data-wishlist-grid>
			<?php
			foreach ( $ew_products as $ew_product ) {
				get_template_part( 'template-parts/product-catalog-card', null, array( 'product' => $ew_product ) );
			}
			?>
		</div>
		<div class="account-empty" data-wishlist-empty hidden>
			<p><?php ew_esc_html_e( 'Your wishlist is empty.' ); ?></p>
			<a class="btn btn--inquiry" href="<?php echo esc_url( ew_page_url( 'product-categories' ) ); ?>">
				<?php ew_esc_html_e( 'Browse Products' ); ?>
			</a>
		</div>
	<?php else : ?>
		<div class="account-empty" data-wishlist-empty>
			<p><?php ew_esc_html_e( 'Your wishlist is empty.' ); ?></p>
			<a class="btn btn--inquiry" href="<?php echo esc_url( ew_page_url( 'product-categories' ) ); ?>">
				<?php ew_esc_html_e( 'Browse Products' ); ?>
			</a>
		</div>
		<div class="catalog-grid" data-wishlist-grid hidden></div>
	<?php endif; ?>

	<?php if ( ! is_user_logged_in() ) : ?>
		<p class="account-note">
			<?php ew_esc_html_e( 'Your wishlist is saved in this browser. Sign in to keep it on your account.' ); ?>
			<a class="account-link" href="<?php echo esc_url( ew_account_page_url( 'login' ) ); ?>"><?php ew_esc_html_e( 'Sign In' ); ?></a>
		</p>
	<?php endif; ?>
</div>

<?php
get_footer();
