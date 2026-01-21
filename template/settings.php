<?php
/**
 * Schema Pro Admin Init
 *
 * @package Schema Pro
 */

$setting_url     = self::get_page_url( 'settings' );
$current_section = isset( $_GET['section'] ) ? $_GET['section'] : 'general';
?>

<div id="wp-schema-pro-setting-links">
	<a href="<?php echo esc_url( $setting_url ); ?>" <?php echo ( 'general' == $current_section ) ? 'class="active"' : ''; ?> ><?php esc_html_e( 'General', 'wp-schema-pro' ); ?></a> |
	<a href="<?php echo esc_url( $setting_url . '&section=social-profiles' ); ?>" <?php echo ( 'social-profiles' == $current_section ) ? 'class="active"' : ''; ?>><?php esc_html_e( 'Social Profiles', 'wp-schema-pro' ); ?></a> |
	<a href="<?php echo esc_url( $setting_url . '&section=global-schemas' ); ?>" <?php echo ( 'global-schemas' == $current_section ) ? 'class="active"' : ''; ?>><?php esc_html_e( 'Other Schemas', 'wp-schema-pro' ); ?></a> |
	<a href="<?php echo esc_url( $setting_url . '&section=star-ratings' ); ?>" <?php echo ( 'star-ratings' == $current_section ) ? 'class="active"' : ''; ?>><?php esc_html_e( 'Star Ratings', 'wp-schema-pro' ); ?></a> |
	<a href="<?php echo esc_url( $setting_url . '&section=advanced-settings' ); ?>" <?php echo ( 'advanced-settings' == $current_section ) ? 'class="active"' : ''; ?>><?php esc_html_e( 'Advanced Settings', 'wp-schema-pro' ); ?></a>
</div>
<div class="wrap bsf-aiosrs-pro clear">
	<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content">
				<?php
				switch ( $current_section ) {
					case 'general':
						$settings = self::get_options( 'wp-schema-pro-general-settings' );
						?>
						<!-- General Settings -->
						<div class="postbox wp-schema-pro-general-settings" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'General', 'wp-schema-pro' ); ?></span>
								<?php
								if ( WP_Schema_Pro_Yoast_Compatibility::$activated ) {
									$message  = __( 'Looks like you have Yoast SEO plugin installed. So we\'ve gone ahead and disabled some features which comes with Yoast SEO as well.', 'wp-schema-pro' );
									$message .= '<br><br>' . __( 'If you would still like to enable then,', 'wp-schema-pro' );
									$message .= ' <a href="' . esc_url( $setting_url . '&section=advanced-settings#aiosrs-pro-settings-yoast-compatibility' ) . '">Click Here</a>';
									BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
								}
								?>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'These are the general settings where you can tell what your website represents and add the name and logo associated with it. This information will be used in Google\'s Knowledge Graph Card.', 'wp-schema-pro' ); ?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'wp-schema-pro-general-settings-group' ); ?>
									<?php do_settings_sections( 'wp-schema-pro-general-settings-group' ); ?>
									<table class="form-table">
										<tr class="wp-schema-pro-site-logo-wrap">
											<th><?php _e( 'Site Logo', 'wp-schema-pro' ); ?></th>
											<td>
												<select name="wp-schema-pro-general-settings[site-logo]" class="wp-schema-pro-custom-option-select">
													<option  <?php selected( $settings['site-logo'], 'custom' ); ?> value="custom"><?php _e( 'Add Custom Logo', 'wp-schema-pro' ); ?></option>
													<option  <?php selected( $settings['site-logo'], 'customizer-logo' ); ?> value="customizer-logo"><?php _e( 'Use Logo From Customizer', 'wp-schema-pro' ); ?></option>
												</select>
												<div class="custom-field-wrapper site-logo-custom-wrap" <?php echo ( 'custom' != $settings['site-logo'] ) ? 'style="display: none;"' : ''; ?> >
													<input type="hidden" class="single-image-field" name="wp-schema-pro-general-settings[site-logo-custom]" value="<?php echo esc_attr( $settings['site-logo-custom'] ); ?>" />
													<?php
													if ( ! empty( $settings['site-logo-custom'] ) ) {
														$image_url = wp_get_attachment_url( $settings['site-logo-custom'] );
													}
													?>
													<div class="image-field-wrap <?php echo ( ! empty( $image_url ) ) ? 'bsf-custom-image-selected' : ''; ?>"">
														<a href="#" class="aiosrs-image-select button"><span class="dashicons dashicons-format-image"></span><?php esc_html_e( 'Select Image', 'wp-schema-pro' ); ?></a>
														<a href="#" class="aiosrs-image-remove dashicons dashicons-no-alt wp-ui-text-highlight"></a>
														<?php if ( isset( $image_url ) && ! empty( $image_url ) ) : ?>
															<a href="#" class="aiosrs-image-select img"><img src="<?php echo esc_url( $image_url ); ?>" /></a>
														<?php endif; ?>
													</div>
												</div>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'This Website Represent a', 'wp-schema-pro' ); ?>
											</th>
											<td>
												<select name="wp-schema-pro-general-settings[site-represent]">
													<option <?php selected( $settings['site-represent'], '' ); ?> value=""> <?php _e( '--None--', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['site-represent'], 'organization' ); ?> value="organization"> <?php _e( 'Company', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['site-represent'], 'local-business' ); ?> value="local-business"> <?php _e( 'Local Business', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['site-represent'], 'person' ); ?> value="person"> <?php _e( 'Person', 'wp-schema-pro' ); ?></option>
												</select>
											</td>
										</tr>
										<tr class="wp-schema-pro-person-name-wrap" <?php echo ( 'person' != $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
											<th><?php _e( 'Person Name', 'wp-schema-pro' ); ?></th>
											<td>
												<input type="text" name="wp-schema-pro-general-settings[person-name]" value="<?php echo esc_attr( $settings['person-name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
											</td>
										</tr>
										<tr class="wp-schema-pro-site-name-wrap" <?php echo ( ! in_array( $settings['site-represent'], array( 'organization', 'local-business' ) ) ) ? 'style="display: none;"' : ''; ?>>
											<th><?php _e( 'Company/Business Name', 'wp-schema-pro' ); ?></th>
											<td>
												<input type="text" name="wp-schema-pro-general-settings[site-name]" value="<?php echo esc_attr( $settings['site-name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
											</td>
										</tr>
										<tr class="wp-schema-pro-local-business-wrap" <?php echo ( 'local-business' != $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
											<th><?php _e( 'Business Type', 'wp-schema-pro' ); ?></th>
											<td>
												<select name="wp-schema-pro-general-settings[local-business-type]">
													<option <?php selected( $settings['local-business-type'], 'LocalBusiness' ); ?> value="LocalBusiness">Local Business</option>
													<option <?php selected( $settings['local-business-type'], 'Store' ); ?> value="Store">Store</option>
													<option <?php selected( $settings['local-business-type'], 'ProfessionalService' ); ?> value="ProfessionalService">Professional Service</option>
													<option <?php selected( $settings['local-business-type'], 'Organization' ); ?> value="Organization">Organization</option>
												</select>
											</td>
										</tr>
										<tr class="wp-schema-pro-local-business-wrap" <?php echo ( 'local-business' != $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
											<th><?php _e( 'Address', 'wp-schema-pro' ); ?></th>
											<td>
												<input type="text" name="wp-schema-pro-general-settings[street-address]" value="<?php echo esc_attr( $settings['street-address'] ); ?>" placeholder="Street Address" style="width: 100%; margin-bottom: 5px;" />
												<div style="display: flex; gap: 5px;">
													<input type="text" name="wp-schema-pro-general-settings[locality]" value="<?php echo esc_attr( $settings['locality'] ); ?>" placeholder="City/Locality" style="flex: 2;" />
													<input type="text" name="wp-schema-pro-general-settings[postal-code]" value="<?php echo esc_attr( $settings['postal-code'] ); ?>" placeholder="Postcode" style="flex: 1;" />
												</div>
												<div style="display: flex; gap: 5px; margin-top: 5px;">
													<input type="text" name="wp-schema-pro-general-settings[region]" value="<?php echo esc_attr( $settings['region'] ); ?>" placeholder="Region/State" style="flex: 1;" />
													<input type="text" name="wp-schema-pro-general-settings[country]" value="<?php echo esc_attr( $settings['country'] ); ?>" placeholder="Country" style="flex: 1;" />
												</div>
											</td>
										</tr>
										<tr class="wp-schema-pro-local-business-wrap" <?php echo ( 'local-business' != $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
											<th><?php _e( 'Contact Info', 'wp-schema-pro' ); ?></th>
											<td>
												<input type="text" name="wp-schema-pro-general-settings[telephone]" value="<?php echo esc_attr( $settings['telephone'] ); ?>" placeholder="Telephone" style="width: 100%; margin-bottom: 5px;" />
												<input type="text" name="wp-schema-pro-general-settings[price-range]" value="<?php echo esc_attr( $settings['price-range'] ); ?>" placeholder="Price Range (e.g. $$)" />
											</td>
										</tr>
										<tr> 
											<th colspan="2">
												<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
											</th>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<?php
						break;

					case 'social-profiles':
						$settings = self::get_options( 'wp-schema-pro-social-profiles' );
						?>
						<!-- Social Profiles -->
						<div class="postbox wp-schema-pro-social-profiles" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'Social Profiles', 'wp-schema-pro' ); ?></span>
								<?php
								if ( WP_Schema_Pro_Yoast_Compatibility::$activated ) {
									$message  = __( 'Looks like you have Yoast SEO plugin installed. So we\'ve gone ahead and disabled some features which comes with Yoast SEO as well.', 'wp-schema-pro' );
									$message .= '<br><br>' . __( 'If you would still like to enable then,', 'wp-schema-pro' );
									$message .= ' <a href="' . esc_url( $setting_url . '&section=advanced-settings#aiosrs-pro-settings-yoast-compatibility' ) . '">Click Here</a>';
									BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
								}
								?>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'You can add your social profile links here. This will help Schema Pro tell search engines a little more about you and your social presence.', 'wp-schema-pro' ); ?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'wp-schema-pro-social-profiles-group' ); ?>
									<?php do_settings_sections( 'wp-schema-pro-social-profiles-group' ); ?>
									<table class="form-table">
										<tr>
											<th><?php _e( 'Facebook', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[facebook]"  value="<?php echo esc_attr( $settings['facebook'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'Twitter', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[twitter]"  value="<?php echo esc_attr( $settings['twitter'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'Google+', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[google-plus]"  value="<?php echo esc_attr( $settings['google-plus'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'Instagram', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[instagram]"  value="<?php echo esc_attr( $settings['instagram'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'YouTube', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[youtube]"  value="<?php echo esc_attr( $settings['youtube'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'LinkedIn', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[linkedin]"  value="<?php echo esc_attr( $settings['linkedin'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'Pinterest', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[pinterest]"  value="<?php echo esc_attr( $settings['pinterest'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'SoundCloud', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[soundcloud]"  value="<?php echo esc_attr( $settings['soundcloud'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'Tumblr', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[tumblr]"  value="<?php echo esc_attr( $settings['tumblr'] ); ?>" /></td>
										</tr>
										<tr> 
											<th colspan="2">
												<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
											</th>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<?php
						break;

					case 'global-schemas':
						$settings = self::get_options( 'wp-schema-pro-global-schemas' );
						?>
						<!-- Global Schemas -->
						<div class="postbox wp-schema-pro-global-schemas" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'Other Schemas', 'wp-schema-pro' ); ?></span>
								<?php
								if ( WP_Schema_Pro_Yoast_Compatibility::$activated ) {
									$message  = __( 'Looks like you have Yoast SEO plugin installed. So we\'ve gone ahead and disabled some features which comes with Yoast SEO as well.', 'wp-schema-pro' );
									$message .= '<br><br>' . __( 'If you would still like to enable then,', 'wp-schema-pro' );
									$message .= ' <a href="' . esc_url( $setting_url . '&section=advanced-settings#aiosrs-pro-settings-yoast-compatibility' ) . '">Click Here</a>';
									BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
								}
								?>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'Apply some other global schemas for your site.', 'wp-schema-pro' ); ?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'wp-schema-pro-global-schemas-group' ); ?>
									<?php do_settings_sections( 'wp-schema-pro-global-schemas-group' ); ?>
									<table class="form-table">
										<tr>
											<th>
												<?php _e( 'Select About Page', 'wp-schema-pro' ); ?>
												<?php
													$message = __( 'Select your about page from the dropdown list.', 'wp-schema-pro' );
													self::get_tooltip( $message );
												?>
											</th>
											<td>
												<select name="wp-schema-pro-global-schemas[about-page]">
													<option value=""><?php esc_html_e( 'None', 'wp-schema-pro' ); ?></option>
													<?php foreach ( self::$pages as $page_id => $page_title ) { ?>
														<option <?php selected( $page_id, $settings['about-page'] ); ?> value="<?php echo esc_attr( $page_id ); ?>"><?php echo esc_html( $page_title ); ?></option>
													<?php } ?>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Select Contact Page', 'wp-schema-pro' ); ?>
												<?php
													$message = __( 'Select your contact page from the dropdown list.', 'wp-schema-pro' );
													self::get_tooltip( $message );
												?>
											</th>
											<td>
												<select name="wp-schema-pro-global-schemas[contact-page]">
													<option value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
													<?php foreach ( self::$pages as $page_id => $page_title ) { ?>
														<option <?php selected( $page_id, $settings['contact-page'] ); ?> value="<?php echo esc_attr( $page_id ); ?>"><?php echo esc_html( $page_title ); ?></option>
													<?php } ?>
												</select>
											</td>
										</tr>
										<tr>
											<th class="tooltip-with-image-wrapper">
												<?php _e( 'Select Menu for SiteLinks Schema', 'wp-schema-pro' ); ?>
												<?php
													$message  = __( 'This helps Google understand the most important pages on your website and can generate Rich Snippet as below.', 'wp-schema-pro' );
													$message .= '<br /><img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/sitelinks.jpg' ) . '" />';
													BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
												?>
											</th>
											<td>
												<?php $nav_menus = wp_get_nav_menus(); ?>
												<select name="wp-schema-pro-global-schemas[site-navigation-element]" >
													<option <?php selected( '', $settings['site-navigation-element'] ); ?> value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
													<?php foreach ( $nav_menus as $menu ) { ?>
														<option <?php selected( $menu->term_id, $settings['site-navigation-element'] ); ?> value="<?php echo esc_attr( $menu->term_id ); ?>"><?php echo esc_html( $menu->name ); ?></option>
													<?php } ?>
												</select>
											</td>
										</tr>
										<tr>
											<th class="tooltip-with-image-wrapper">
												<?php _e( 'Enable Breadcrumb Schema?', 'wp-schema-pro' ); ?>
												<?php
													$message  = __( 'If enabled, Google can Breadcrumb for your website Search results.', 'wp-schema-pro' );
													$message .= '<br /><img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/breadcrumbs.jpg' ) . '" />';
													BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
												?>
											</th>
											<td>
												<label>
													<input type="hidden" name="wp-schema-pro-global-schemas[breadcrumb]" value="disabled" />
													<input type="checkbox" name="wp-schema-pro-global-schemas[breadcrumb]" <?php checked( '1', $settings ['breadcrumb'] ); ?> value="1" /> <?php _e( 'Yes', 'wp-schema-pro' ); ?>
												</label>
											</td>
										</tr>
										<tr>
											<th class="tooltip-with-image-wrapper">
												<?php _e( 'Enable Sitelinks Search Box?', 'wp-schema-pro' ); ?>
												<?php
													$message  = __( 'If enabled, Google can display a search box with your Search results.', 'wp-schema-pro' );
													$message .= '<br /><img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/sitelink-search.jpg' ) . '" />';
													BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
												?>
											</th>
											<td>
												<label>
													<input type="hidden" name="wp-schema-pro-global-schemas[sitelink-search-box]" value="disabled" />
													<input type="checkbox" name="wp-schema-pro-global-schemas[sitelink-search-box]" <?php checked( '1', $settings['sitelink-search-box'] ); ?> value="1" /> <?php _e( 'Yes', 'wp-schema-pro' ); ?>
												</label>
											</td>
										</tr>
										<tr> 
											<th colspan="2">
												<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
											</th>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<?php
						break;
					
					case 'star-ratings':
						$settings = self::get_options( 'wp-schema-pro-ratings-settings' );
						$all_post_types = get_post_types( array( 'public' => true ), 'objects' );
						unset( $all_post_types['attachment'] );
						unset( $all_post_types['aiosrs-schema'] );
						?>
						<!-- Star Ratings Settings -->
						<div class="postbox wp-schema-pro-star-ratings" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'Star Ratings (KK Star Replacement)', 'wp-schema-pro' ); ?></span>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'Enable and configure the built-in star rating system. This replaces the need for extra plugins like KK Star Ratings.', 'wp-schema-pro' ); ?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'wp-schema-pro-ratings-settings-group' ); ?>
									<?php do_settings_sections( 'wp-schema-pro-ratings-settings-group' ); ?>
									<table class="form-table">
										<tr>
											<th><?php _e( 'Enable Star Ratings?', 'wp-schema-pro' ); ?></th>
											<td>
												<label>
													<input type="hidden" name="wp-schema-pro-ratings-settings[enable-star-ratings]" value="disabled" />
													<input type="checkbox" name="wp-schema-pro-ratings-settings[enable-star-ratings]" <?php checked( '1', $settings['enable-star-ratings'] ); ?> value="1" /> <?php _e( 'Yes', 'wp-schema-pro' ); ?>
												</label>
											</td>
										</tr>
										<tr>
											<th><?php _e( 'Display Position', 'wp-schema-pro' ); ?></th>
											<td>
												<select name="wp-schema-pro-ratings-settings[ratings-position]">
													<option <?php selected( $settings['ratings-position'], 'top' ); ?> value="top"><?php _e( 'Top of Content', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['ratings-position'], 'bottom' ); ?> value="bottom"><?php _e( 'Bottom of Content', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['ratings-position'], 'both' ); ?> value="both"><?php _e( 'Both Top & Bottom', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['ratings-position'], 'manual' ); ?> value="manual"><?php _e( 'Manual (Shortcode only)', 'wp-schema-pro' ); ?></option>
												</select>
											</td>
										</tr>
										<tr>
											<th><?php _e( 'Star Color', 'wp-schema-pro' ); ?></th>
											<td><input type="color" name="wp-schema-pro-ratings-settings[star-color]" value="<?php echo esc_attr( $settings['star-color'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'Star Size (px)', 'wp-schema-pro' ); ?></th>
											<td><input type="number" name="wp-schema-pro-ratings-settings[star-size]" value="<?php echo esc_attr( $settings['star-size'] ); ?>" min="10" max="100" /></td>
										</tr>
										<tr>
											<th><?php _e( 'Rating Text', 'wp-schema-pro' ); ?></th>
											<td><input type="text" name="wp-schema-pro-ratings-settings[rating-text]" value="<?php echo esc_attr( $settings['rating-text'] ); ?>" /></td>
										</tr>
										<tr>
											<th><?php _e( 'Enable on Post Types', 'wp-schema-pro' ); ?></th>
											<td>
												<?php foreach ( $all_post_types as $pt_slug => $pt_obj ) { ?>
													<label style="display: block; margin-bottom: 5px;">
														<input type="checkbox" name="wp-schema-pro-ratings-settings[post-types][]" value="<?php echo esc_attr( $pt_slug ); ?>" <?php echo ( is_array( $settings['post-types'] ) && in_array( $pt_slug, $settings['post-types'] ) ) ? 'checked' : ''; ?> />
														<?php echo esc_html( $pt_obj->labels->name ); ?>
													</label>
												<?php } ?>
											</td>
										</tr>
										<tr> 
											<th colspan="2">
												<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
											</th>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<?php
						break;

					case 'advanced-settings':
						$settings = self::get_options();
						// Get list of current General entries.
						$entries = self::get_admin_menu_positions();

						$select_box = '<select name="aiosrs-pro-settings[menu-position]" >' . "\n";
						foreach ( $entries as $page => $entry ) {
							$select_box .= '<option ' . selected( $page, $settings['menu-position'], false ) . ' value="' . $page . '">' . $entry . "</option>\n";
						}
						$select_box .= "</select>\n";

						?>
						<!-- Settings -->
						<div class="postbox wp-schema-pro-advanced-settings" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'Advanced Settings', 'wp-schema-pro' ); ?></span>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'Some prerequisite settings you might want to look into before moving forward.', 'wp-schema-pro' ); ?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'aiosrs-pro-settings-group' ); ?>
									<?php do_settings_sections( 'aiosrs-pro-settings-group' ); ?>
									<table class="form-table">
										<tr> 
											<th scope="row">
												<?php esc_html_e( 'Enable Test Schema Link in Toolbar', 'wp-schema-pro' ); ?>
												<?php
													$message  = __( 'Enable this if you want to enable the test schema link in the toolbar.', 'wp-schema-pro' );
													$message .= ' <a href="https://wpschema.com/docs/enable-test-schema-link-mean/?utm_source=wp-dashboard&utm_medium=schema-pro-tooltips" target="_blank" rel="noopener">' . __( 'Know more', 'wp-schema-pro' ) . '</a>';
													self::get_tooltip( $message );
												?>
											</th>
											<td>					
												<select id="aiosrs-pro-settings-quick-test" name="aiosrs-pro-settings[quick-test]" >
													<option <?php selected( 1, $settings['quick-test'] ); ?> value="1"><?php esc_attr_e( 'Yes', 'wp-schema-pro' ); ?></option>
													<option <?php selected( 'disabled', $settings['quick-test'] ); ?> value="disabled"><?php esc_attr_e( 'No', 'wp-schema-pro' ); ?></option>
												</select>
											</td>
										</tr>
										<tr> 
											<th scope="row">
												<?php esc_html_e( 'Display Schema Pro Menu Under', 'wp-schema-pro' ); ?>
												<?php
													$message  = __( 'Decide where you wish to see the Schema Pro menu in your WordPress dashboard.', 'wp-schema-pro' );
													$message .= ' <a href="https://wpschema.com/docs/advanced-settings-schema-pro/?utm_source=wp-dashboard&utm_medium=schema-pro-tooltips#admin-menu" target="_blank" rel="noopener">' . __( 'Know more', 'wp-schema-pro' ) . '</a>';
													self::get_tooltip( $message );
												?>
											</th>
											<td><?php echo $select_box; ?></td>
										</tr>
										<tr> 
											<th scope="row">
												<?php esc_html_e( 'Add Schema Code In', 'wp-schema-pro' ); ?>
												<?php
													$message  = __( 'Select where you wish to add the schema code.', 'wp-schema-pro' );
													$message .= ' <a href="https://wpschema.com/docs/advanced-settings-schema-pro/?utm_source=wp-dashboard&utm_medium=schema-pro-tooltips#schema-location" target="_blank" rel="noopener">' . __( 'Know more', 'wp-schema-pro' ) . '</a>';
													self::get_tooltip( $message );
												?>
											</th>
											<td>
												<select id="aiosrs-pro-settings-schema-location" name="aiosrs-pro-settings[schema-location]" >
													<option <?php selected( 'head', $settings['schema-location'] ); ?> value="head"><?php esc_html_e( 'Head', 'wp-schema-pro' ); ?></option>
													<option <?php selected( 'footer', $settings['schema-location'] ); ?> value="footer"><?php esc_html_e( 'Footer', 'wp-schema-pro' ); ?></option>
												</select>
											</td>
										</tr>
										<?php if ( WP_Schema_Pro_Yoast_Compatibility::$activated ) { ?>
											<tr class="wp-schema-pro-yoast-compatibilty-wrap">
												<th>
													<?php _e( 'Disable Duplicate Features that Yoast SEO Offers?', 'wp-schema-pro' ); ?>
													<?php
														$message  = __( 'When disabled, Schema Pro does not output duplicate markup that Yoast SEO Offers.', 'wp-schema-pro' );
														$message .= '<br/><br/>' . __( 'These are the features that will be disabled:', 'wp-schema-pro' ) . '<br/>';
														$message .= '<ol>';
														$message .= '<li>' . __( 'Organization/Person', 'wp-schema-pro' ) . '</li>';
														$message .= '<li>' . __( 'Social Profiles', 'wp-schema-pro' ) . '</li>';
														$message .= '<li>' . __( 'Breadcrumb', 'wp-schema-pro' ) . '</li>';
														$message .= '<li>' . __( 'Sitelink Search Box', 'wp-schema-pro' ) . '</li>';
														$message .= '</ol>';
														self::get_tooltip( $message );
													?>
												</th>
												<td>
													<label>
														<input type="hidden" name="aiosrs-pro-settings[yoast-compatibility]" value="disabled" />
														<input type="checkbox" name="aiosrs-pro-settings[yoast-compatibility]" id="aiosrs-pro-settings-yoast-compatibility" <?php checked( '1', $settings ['yoast-compatibility'] ); ?> value="1" /> <?php _e( 'Yes', 'wp-schema-pro' ); ?>
													</label>
												</td>
											</tr>
										<?php } else { ?>
											<input type="hidden" name="aiosrs-pro-settings[yoast-compatibility]" value="<?php echo esc_attr( $settings ['yoast-compatibility'] ); ?>" />
										<?php } ?>
										<tr>
											<th scope="row">
												<?php esc_html_e( 'Auto WooCommerce Schema', 'wp-schema-pro' ); ?>
												<?php
													$message = __( 'Automatically generate Product schema for WooCommerce products and categories.', 'wp-schema-pro' );
													self::get_tooltip( $message );
												?>
											</th>
											<td>
												<label>
													<input type="hidden" name="aiosrs-pro-settings[woocommerce-auto-schema]" value="disabled" />
													<input type="checkbox" name="aiosrs-pro-settings[woocommerce-auto-schema]" <?php checked( '1', $settings['woocommerce-auto-schema'] ); ?> value="1" /> <?php _e( 'Yes', 'wp-schema-pro' ); ?>
												</label>
											</td>
										</tr>
										<tr>
											<th scope="row">
												<?php esc_html_e( 'Auto FAQ Schema', 'wp-schema-pro' ); ?>
												<?php
													$message = __( 'Automatically extract FAQs from content and generate FAQPage schema.', 'wp-schema-pro' );
													self::get_tooltip( $message );
												?>
											</th>
											<td>
												<label>
													<input type="hidden" name="aiosrs-pro-settings[auto-faq-schema]" value="disabled" />
													<input type="checkbox" name="aiosrs-pro-settings[auto-faq-schema]" <?php checked( '1', $settings['auto-faq-schema'] ); ?> value="1" /> <?php _e( 'Yes', 'wp-schema-pro' ); ?>
												</label>
											</td>
										</tr>
										<tr> 
											<th colspan="2">
												<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
											</th>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<?php
						break;
				}
				?>
			</div>
			<div class="postbox-container" id="postbox-container-1">
				<div id="side-sortables" style="">
					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Setup Wizard', 'wp-schema-pro' ); ?></span></h2>
						<div class="inside">
							<div>
								<p><?php esc_html_e( 'Need help configure Schema Pro step by step?', 'wp-schema-pro' ); ?></p>
								<a href="<?php echo esc_url( admin_url( 'index.php?page=aiosrs-pro-setup-wizard' ) ); ?>" class="button button-large button-primary"><?php esc_html_e( 'Start setup wizard &raquo;', 'wp-schema-pro' ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
