<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Item_List' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Item_List {

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
			$schema['@type']    = 'ItemList';

			if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
				$schema['name'] = wp_strip_all_tags( $data['name'] );
			}

			$items = array();
			if ( is_archive() || is_home() || is_search() ) {
				global $wp_query;
				$posts = $wp_query->posts;
				if ( ! empty( $posts ) ) {
					foreach ( $posts as $index => $p ) {
						$items[] = array(
							'@type'    => 'ListItem',
							'position' => $index + 1,
							'url'      => get_permalink( $p->ID ),
							'name'     => wp_strip_all_tags( get_the_title( $p->ID ) ),
						);
					}
				}
			}

			if ( ! empty( $items ) ) {
				$schema['itemListElement'] = $items;
			}

			return apply_filters( 'wp_schema_pro_schema_item_list', $schema, $data, $post );
		}

	}
}
