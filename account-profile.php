<?php
/**
 * Account — 账户资料（仅登录用户；未登录已在路由层跳转）。
 *
 * 路由：/account/（rewrite，见 inc/account-routes.php）
 * 该页按用户渲染，已由 inc/account-routes.php 设置 no-store。
 *
 * @package chinyoo-red
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$ew_user    = wp_get_current_user();
$ew_profile = ew_account_get_profile( $ew_user->ID );
?>

<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e( 'Home' ); ?></a>
	<span> › </span><span><?php ew_esc_html_e( 'Account' ); ?></span>
</div></div>

<div class="page-head" data-animation-wrap>
	<div class="container">
		<h1 data-animation="1"><?php ew_esc_html_e( 'Account Details' ); ?></h1>
		<p class="entry-content" data-animation="2">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: customer display name */
					ew__( 'Signed in as %s' ),
					$ew_user->display_name
				)
			);
			?>
		</p>
	</div>
</div>

<div class="container account-shell">

	<form class="account-form" id="ew-profile-form" novalidate>
		<div class="account-feedback" role="alert" hidden></div>

		<p class="account-field">
			<label for="profile-email"><?php ew_esc_html_e( 'Email' ); ?></label>
			<input id="profile-email" type="email" value="<?php echo esc_attr( $ew_user->user_email ); ?>" disabled>
		</p>

		<p class="account-field">
			<label for="profile-phone"><?php ew_esc_html_e( 'Phone Number' ); ?> <span class="req">*</span></label>
			<input id="profile-phone" type="tel" name="phone" value="<?php echo esc_attr( $ew_profile['phone'] ); ?>" required>
		</p>

		<p class="account-field">
			<label for="profile-company"><?php ew_esc_html_e( 'Company Name' ); ?></label>
			<input id="profile-company" type="text" name="company" value="<?php echo esc_attr( $ew_profile['company'] ); ?>">
		</p>

		<div class="account-field-row">
			<p class="account-field">
				<label for="profile-country"><?php ew_esc_html_e( 'Country / Region' ); ?></label>
				<input id="profile-country" type="text" name="country" maxlength="2" value="<?php echo esc_attr( $ew_profile['country'] ); ?>" placeholder="US">
			</p>
			<p class="account-field">
				<label for="profile-state"><?php ew_esc_html_e( 'State / Province' ); ?></label>
				<input id="profile-state" type="text" name="state" value="<?php echo esc_attr( $ew_profile['state'] ); ?>">
			</p>
		</div>

		<p class="account-field">
			<label for="profile-city"><?php ew_esc_html_e( 'City' ); ?></label>
			<input id="profile-city" type="text" name="city" value="<?php echo esc_attr( $ew_profile['city'] ); ?>">
		</p>

		<p class="account-field">
			<label for="profile-address-1"><?php ew_esc_html_e( 'Address Line 1' ); ?></label>
			<input id="profile-address-1" type="text" name="address_1" value="<?php echo esc_attr( $ew_profile['address_1'] ); ?>">
		</p>

		<p class="account-field">
			<label for="profile-address-2"><?php ew_esc_html_e( 'Address Line 2' ); ?></label>
			<input id="profile-address-2" type="text" name="address_2" value="<?php echo esc_attr( $ew_profile['address_2'] ); ?>">
		</p>

		<p class="account-field">
			<label for="profile-postcode"><?php ew_esc_html_e( 'Postal Code' ); ?></label>
			<input id="profile-postcode" type="text" name="postcode" value="<?php echo esc_attr( $ew_profile['postcode'] ); ?>">
		</p>

		<button type="submit" class="btn btn--inquiry btn--lg account-submit">
			<?php ew_esc_html_e( 'Save Changes' ); ?>
		</button>
	</form>

	<p class="account-links">
		<a class="account-link" href="<?php echo esc_url( ew_account_page_url( 'wishlist' ) ); ?>"><?php ew_esc_html_e( 'My Wishlist' ); ?></a>
		<a class="account-link account-link--muted" href="<?php echo esc_url( ew_account_logout_url() ); ?>"><?php ew_esc_html_e( 'Sign Out' ); ?></a>
	</p>

</div>

<?php
get_footer();
