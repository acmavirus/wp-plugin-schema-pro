<?php
/**
 * Posts and Categories Auto Schema Integration
 *
 * @package Schema Pro
 * @since 1.2.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Posts' ) ) {

	/**
	 * Posts Auto Schema Class
	 *
	 * @since 1.2.0
	 */
	class BSF_AIOSRS_Pro_Posts {

		/**
		 * Instance
		 *
		 * @var object
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_head', array( $this, 'posts_auto_schema' ), 5 );
		}

		/**
		 * Output auto schema on post categories and archives
		 *
		 * @return void
		 */
		public function posts_auto_schema() {
			// Skip if WooCommerce category (handled by WooCommerce class)
			if ( class_exists( 'WooCommerce' ) && ( is_product_category() || is_product_tag() || is_shop() ) ) {
				return;
			}

			// Category/Archive/Search/Home page schema
			if ( is_category() || is_tag() || is_archive() || is_home() || is_search() ) {
				$this->render_archive_schema();
			}
		}

		/**
		 * Render Archive Schema (CollectionPage + ItemList)
		 *
		 * @return void
		 */
		private function render_archive_schema() {
			$schema = array();

			// CollectionPage
			if ( is_category() || is_tag() || is_tax() ) {
				$collection = $this->get_collection_page_schema();
				if ( ! empty( $collection ) ) {
					$schema[] = $collection;
				}
			}

			// ItemList
			$item_list = $this->get_item_list_schema();
			if ( ! empty( $item_list ) ) {
				$schema[] = $item_list;
			}

			if ( ! empty( $schema ) ) {
				echo '<!-- Post Category Schema by Schema Pro -->';
				foreach ( $schema as $s ) {
					echo '<script type="application/ld+json">';
					echo wp_json_encode( $s, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
					echo '</script>';
				}
				echo '<!-- / Post Category Schema -->';
			}
		}

		/**
		 * Get CollectionPage Schema for categories/tags
		 *
		 * @return array
		 */
		private function get_collection_page_schema() {
			$term = get_queried_object();

			if ( ! $term || ! is_a( $term, 'WP_Term' ) ) {
				return array();
			}

			$schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'CollectionPage',
				'name'        => wp_strip_all_tags( $term->name ),
				'url'         => get_term_link( $term ),
			);

			if ( ! empty( $term->description ) ) {
				$schema['description'] = wp_strip_all_tags( $term->description );
			}

			return apply_filters( 'wp_schema_pro_posts_collection_page', $schema, $term );
		}

		/**
		 * Get ItemList Schema
		 *
		 * @return array
		 */
		private function get_item_list_schema() {
			global $wp_query;

			$posts = $wp_query->posts;
			if ( empty( $posts ) ) {
				return array();
			}

			$name = '';
			if ( is_category() || is_tag() || is_tax() ) {
				$term = get_queried_object();
				$name = is_a( $term, 'WP_Term' ) ? $term->name : '';
			} elseif ( is_home() ) {
				$name = get_bloginfo( 'name' );
			} elseif ( is_search() ) {
				$name = sprintf( __( 'Search Results for: %s', 'wp-schema-pro' ), get_search_query() );
			} elseif ( is_author() ) {
				$name = get_the_author();
			} else {
				$name = get_the_archive_title();
			}

			$schema = array(
				'@context'        => 'https://schema.org',
				'@type'           => 'ItemList',
				'name'            => wp_strip_all_tags( $name ),
				'itemListElement' => array(),
			);

			foreach ( $posts as $index => $post ) {
				$item = array(
					'@type'    => 'ListItem',
					'position' => $index + 1,
					'url'      => get_permalink( $post->ID ),
					'name'     => wp_strip_all_tags( get_the_title( $post->ID ) ),
				);

				// Add featured image if exists
				if ( has_post_thumbnail( $post->ID ) ) {
					$item['image'] = get_the_post_thumbnail_url( $post->ID, 'full' );
				}

				$schema['itemListElement'][] = $item;
			}

			return apply_filters( 'wp_schema_pro_posts_item_list', $schema );
		}
	}
}

/**
 * Initialize Posts integration
 */
BSF_AIOSRS_Pro_Posts::get_instance();
