<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Global_Organization' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_Global_Organization {

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
			$schema['@type']    = 'Organization';
			$schema['name']     = ( isset( $general_settings['site-name'] ) && ! empty( $general_settings['site-name'] ) ) ? $general_settings['site-name'] : wp_strip_all_tags( get_bloginfo( 'name' ) );
			$schema['url']      = wp_strip_all_tags( get_bloginfo( 'url' ) );

			if ( isset( $general_settings['telephone'] ) && ! empty( $general_settings['telephone'] ) ) {
				$schema['contactPoint'] = array(
					'@type'       => 'ContactPoint',
					'telephone'   => $general_settings['telephone'],
					'contactType' => 'customer service',
				);
				$global_settings = BSF_AIOSRS_Pro_Admin::get_options( 'wp-schema-pro-global-schemas' );
				if ( ! empty( $global_settings['contact-page'] ) ) {
					$schema['contactPoint']['url'] = get_permalink( $global_settings['contact-page'] );
				}
			}

			$logo_id = get_post_thumbnail_id( $post['ID'] );
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
				$schema['logo'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $logo_image, 'ImageObject' );
			}

			foreach ( $social_profiles as $social_link ) {
				if ( ! empty( $social_link ) ) {
					$schema['sameAs'][] = $social_link;
				}
			}

			return apply_filters( 'wp_schema_pro_global_schema_organization', $schema, $post );
		}

	}
}
