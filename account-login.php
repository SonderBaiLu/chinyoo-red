<?php
/**
 * Account — Sign In / Create Account（单页 Tab 切换）。
 *
 * 路由：/login/（rewrite，见 inc/account-routes.php）
 *
 * @package chinyoo-red
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$ew_has_details = false;
?>

<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e( 'Home' ); ?></a>
	<span> › </span><span><?php ew_esc_html_e( 'Sign In' ); ?></span>
</div></div>

<div class="page-head" data-animation-wrap>
	<div class="container">
		<h1 data-animation="1"><?php ew_esc_html_e( 'Sign In' ); ?> / <?php ew_esc_html_e( 'Create Account' ); ?></h1>
		<p class="entry-content" data-animation="2"><?php ew_esc_html_e( 'Sign in to keep your wishlist, save your details, and send inquiries faster.' ); ?></p>
	</div>
</div>

<div class="container account-shell">

	<div class="account-tabs" role="tablist" aria-label="<?php ew_esc_attr_e( 'Sign In' ); ?>">
		<button type="button" class="account-tab is-active" role="tab" id="tab-signin"
			aria-controls="panel-signin" aria-selected="true" data-tab="signin">
			<?php ew_esc_html_e( 'Sign In' ); ?>
		</button>
		<button type="button" class="account-tab" role="tab" id="tab-register"
			aria-controls="panel-register" aria-selected="false" data-tab="register">
			<?php ew_esc_html_e( 'Create Account' ); ?>
		</button>
	</div>

	<!-- ===== Sign In ===== -->
	<section class="account-panel is-active" id="panel-signin" role="tabpanel" aria-labelledby="tab-signin">
		<form class="account-form" id="ew-login-form" novalidate>
			<div class="account-feedback" role="alert" hidden></div>

			<p class="account-field">
				<label for="login-email"><?php ew_esc_html_e( 'Email' ); ?> <span class="req">*</span></label>
				<input id="login-email" type="email" name="email" autocomplete="email" required>
			</p>

			<p class="account-field">
				<label for="login-password"><?php ew_esc_html_e( 'Password' ); ?> <span class="req">*</span></label>
				<input id="login-password" type="password" name="password" autocomplete="current-password" required>
			</p>

			<p class="account-check">
				<label for="login-remember">
					<input id="login-remember" type="checkbox" name="remember" value="1">
					<span><?php ew_esc_html_e( 'Remember me' ); ?></span>
				</label>
			</p>

			<button type="submit" class="btn btn--inquiry btn--lg account-submit">
				<?php ew_esc_html_e( 'Sign In' ); ?>
			</button>

			<p class="account-alt">
				<?php ew_esc_html_e( "Don't have an account?" ); ?>
				<button type="button" class="account-link" data-tab-goto="register"><?php ew_esc_html_e( 'Create one' ); ?></button>
			</p>
		</form>
	</section>

	<!-- ===== Create Account ===== -->
	<section class="account-panel" id="panel-register" role="tabpanel" aria-labelledby="tab-register">
		<form class="account-form" id="ew-register-form" novalidate>
			<div class="account-feedback" role="alert" hidden></div>

			<p class="account-hint"><?php ew_esc_html_e( 'Fields marked * are required.' ); ?></p>

			<p class="account-field">
				<label for="reg-email"><?php ew_esc_html_e( 'Email' ); ?> <span class="req">*</span></label>
				<input id="reg-email" type="email" name="email" autocomplete="email" required>
			</p>

			<p class="account-field">
				<label for="reg-password"><?php ew_esc_html_e( 'Password' ); ?> <span class="req">*</span></label>
				<input id="reg-password" type="password" name="password" autocomplete="new-password" minlength="8" placeholder="<?php ew_esc_attr_e( 'Password must be at least 8 characters.' ); ?>" required>
			</p>

			<p class="account-field">
				<label for="reg-phone"><?php ew_esc_html_e( 'Phone Number' ); ?> <span class="req">*</span></label>
				<input id="reg-phone" type="tel" name="phone" autocomplete="tel" placeholder="+86 138 0013 8000" required>
			</p>

			<details class="account-details" id="reg-details">
				<summary><?php ew_esc_html_e( 'Company & shipping details (optional)' ); ?></summary>

				<p class="account-field">
					<label for="reg-company"><?php ew_esc_html_e( 'Company Name' ); ?></label>
					<input id="reg-company" type="text" name="company" autocomplete="organization">
				</p>

				<div class="account-field-row">
					<p class="account-field">
						<label for="reg-country"><?php ew_esc_html_e( 'Country / Region' ); ?></label>
						<input id="reg-country" type="text" name="country" maxlength="2" placeholder="US" autocomplete="country">
					</p>
					<p class="account-field">
						<label for="reg-state"><?php ew_esc_html_e( 'State / Province' ); ?></label>
						<input id="reg-state" type="text" name="state" autocomplete="address-level1">
					</p>
				</div>

				<p class="account-field">
					<label for="reg-city"><?php ew_esc_html_e( 'City' ); ?></label>
					<input id="reg-city" type="text" name="city" autocomplete="address-level2">
				</p>

				<p class="account-field">
					<label for="reg-address-1"><?php ew_esc_html_e( 'Address Line 1' ); ?></label>
					<input id="reg-address-1" type="text" name="address_1" autocomplete="address-line1">
				</p>

				<p class="account-field">
					<label for="reg-address-2"><?php ew_esc_html_e( 'Address Line 2' ); ?></label>
					<input id="reg-address-2" type="text" name="address_2" autocomplete="address-line2">
				</p>

				<p class="account-field">
					<label for="reg-postcode"><?php ew_esc_html_e( 'Postal Code' ); ?></label>
					<input id="reg-postcode" type="text" name="postcode" autocomplete="postal-code">
				</p>
			</details>

			<button type="submit" class="btn btn--inquiry btn--lg account-submit">
				<?php ew_esc_html_e( 'Create Account' ); ?>
			</button>

			<button type="button" class="btn btn--outline btn--lg account-submit account-skip" data-register-skip="1">
				<?php ew_esc_html_e( 'Skip details, create account now' ); ?>
			</button>

			<p class="account-alt">
				<?php ew_esc_html_e( 'Already have an account?' ); ?>
				<button type="button" class="account-link" data-tab-goto="signin"><?php ew_esc_html_e( 'Sign In' ); ?></button>
			</p>
		</form>
	</section>

</div>

<?php
get_footer();
