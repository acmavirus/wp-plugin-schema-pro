<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Collection_Page' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Collection_Page {

		/**
		 * Render Schema.
		 *
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'CollectionPage';

			if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
				$schema['name'] = wp_strip_all_tags( $data['name'] );
			}

			if ( isset( $data['description'] ) && ! empty( $data['description'] ) ) {
				$schema['description'] = wp_strip_all_tags( $data['description'] );
			}

			return apply_filters( 'wp_schema_pro_schema_collection_page', $schema, $data, $post );
		}

	}
}
