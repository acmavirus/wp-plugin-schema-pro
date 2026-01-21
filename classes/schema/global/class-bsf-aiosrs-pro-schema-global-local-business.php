<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Global_Local_Business' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_Global_Local_Business {

		/**
		 * Render Schema.
		 *
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $post ) {

			$schema           = array();
			$general_settings = BSF_AIOSRS_Pro_Admin::get_options( 'wp-schema-pro-general-settings' );
			$social_profiles  = BSF_AIOSRS_Pro_Admin::get_options( 'wp-schema-pro-social-profiles' );

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = ( isset( $general_settings['local-business-type'] ) && ! empty( $general_settings['local-business-type'] ) ) ? $general_settings['local-business-type'] : 'LocalBusiness';
			$schema['name']     = ( isset( $general_settings['site-name'] ) && ! empty( $general_settings['site-name'] ) ) ? $general_settings['site-name'] : wp_strip_all_tags( get_bloginfo( 'name' ) );
			$schema['url']      = wp_strip_all_tags( get_bloginfo( 'url' ) );

			if ( isset( $general_settings['street-address'] ) && ! empty( $general_settings['street-address'] ) ) {
				$schema['address'] = array(
					'@type'           => 'PostalAddress',
					'streetAddress'   => $general_settings['street-address'],
					'addressLocality' => isset( $general_settings['locality'] ) ? $general_settings['locality'] : '',
					'postalCode'      => isset( $general_settings['postal-code'] ) ? $general_settings['postal-code'] : '',
					'addressRegion'   => isset( $general_settings['region'] ) ? $general_settings['region'] : '',
					'addressCountry'  => isset( $general_settings['country'] ) ? $general_settings['country'] : '',
				);
			}

			if ( isset( $general_settings['telephone'] ) && ! empty( $general_settings['telephone'] ) ) {
				$schema['telephone'] = $general_settings['telephone'];
			}

			if ( isset( $general_settings['price-range'] ) && ! empty( $general_settings['price-range'] ) ) {
				$schema['priceRange'] = $general_settings['price-range'];
			}

			$logo_id = '';
			if ( isset( $general_settings['site-logo'] ) && 'custom' == $general_settings['site-logo'] ) {
				$logo_id = isset( $general_settings['site-logo-custom'] ) ? $general_settings['site-logo-custom'] : '';
			} elseif ( isset( $general_settings['site-logo'] ) && 'customizer-logo' == $general_settings['site-logo'] ) {
				if ( function_exists( 'the_custom_logo' ) ) {
					if ( has_custom_logo() ) {
						$logo_id = get_theme_mod( 'custom_logo' );
					}
				}
			}
			if ( $logo_id ) {
				$logo_image     = wp_get_attachment_image_src( $logo_id, 'full' );
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $logo_image, 'ImageObject' );
			}

			foreach ( $social_profiles as $social_link ) {
				if ( ! empty( $social_link ) ) {
					$schema['sameAs'][] = $social_link;
				}
			}

			return apply_filters( 'wp_schema_pro_global_schema_local_business', $schema, $post );
		}

	}
}
