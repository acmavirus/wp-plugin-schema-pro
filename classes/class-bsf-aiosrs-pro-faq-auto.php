<?php
/**
 * FAQ Auto-generation Schema
 *
 * @package Schema Pro
 * @since 1.2.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_FAQ_Auto' ) ) {

	/**
	 * FAQ Auto Schema Class
	 *
	 * @since 1.2.0
	 */
	class BSF_AIOSRS_Pro_FAQ_Auto {

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
			add_filter( 'wp_schema_pro_faq_questions', array( $this, 'auto_extract_faq' ), 10, 2 );
			add_action( 'wp_head', array( $this, 'auto_faq_schema' ), 5 );
		}

		/**
		 * Output auto FAQ schema on posts/products
		 *
		 * @return void
		 */
		public function auto_faq_schema() {
			if ( ! is_singular() ) {
				return;
			}

			$settings = BSF_AIOSRS_Pro_Admin::get_options();
			$auto_faq = isset( $settings['auto-faq-schema'] ) ? $settings['auto-faq-schema'] : '1';

			if ( '1' !== $auto_faq ) {
				return;
			}

			$post_id = get_the_ID();
			$faqs = $this->get_faq_from_content( $post_id );

			// Also check for custom FAQ meta
			$meta_faqs = get_post_meta( $post_id, '_schema_pro_faq', true );
			if ( ! empty( $meta_faqs ) && is_array( $meta_faqs ) ) {
				$faqs = array_merge( $faqs, $meta_faqs );
			}

			// Apply filter for AI-generated FAQs
			$faqs = apply_filters( 'wp_schema_pro_auto_faq_questions', $faqs, $post_id );

			if ( empty( $faqs ) ) {
				return;
			}

			$schema = $this->build_faq_schema( $faqs );

			if ( ! empty( $schema['mainEntity'] ) ) {
				echo '<!-- FAQ Schema by Schema Pro -->';
				echo '<script type="application/ld+json">';
				echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
				echo '</script>';
				echo '<!-- / FAQ Schema -->';
			}
		}

		/**
		 * Extract FAQs from post content
		 *
		 * @param int $post_id Post ID.
		 * @return array
		 */
		public function get_faq_from_content( $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				return array();
			}

			$content = $post->post_content;
			
			// For products, also check the short description
			if ( 'product' === $post->post_type && ! empty( $post->post_excerpt ) ) {
				$content .= "\n" . $post->post_excerpt;
			}

			// Apply filters to get rendered content (blocks, shortcodes, etc.)
			// We remove our own hook temporarily if it was on the_content, but here it's on wp_head so it's fine.
			$content = apply_filters( 'the_content', $content );

			$faqs = array();

			// Look for FAQ pattern: Question heading followed by answer paragraph
			// Pattern 1: H2/H3 with ? at the end
			preg_match_all( '/<h[23][^>]*>([^<]*\?)\s*<\/h[23]>\s*<p>([^<]+)<\/p>/is', $content, $matches );

			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $index => $question ) {
					$answer = isset( $matches[2][ $index ] ) ? $matches[2][ $index ] : '';
					if ( ! empty( $question ) && ! empty( $answer ) ) {
						$faqs[] = array(
							'question' => wp_strip_all_tags( trim( $question ) ),
							'answer'   => wp_strip_all_tags( trim( $answer ) ),
						);
					}
				}
			}

			// Pattern 2: Gutenberg FAQ blocks or common FAQ HTML structures
			// Look for dl/dt/dd pattern
			preg_match_all( '/<dt[^>]*>([^<]+)<\/dt>\s*<dd[^>]*>([^<]+)<\/dd>/is', $content, $dl_matches );

			if ( ! empty( $dl_matches[1] ) ) {
				foreach ( $dl_matches[1] as $index => $question ) {
					$answer = isset( $dl_matches[2][ $index ] ) ? $dl_matches[2][ $index ] : '';
					if ( ! empty( $question ) && ! empty( $answer ) ) {
						$faqs[] = array(
							'question' => wp_strip_all_tags( trim( $question ) ),
							'answer'   => wp_strip_all_tags( trim( $answer ) ),
						);
					}
				}
			}

			// Pattern 3: Accordion/FAQ blocks with specific class
			preg_match_all( '/<div[^>]*class="[^"]*faq[^"]*"[^>]*>.*?<[^>]*class="[^"]*question[^"]*"[^>]*>([^<]+)<.*?<[^>]*class="[^"]*answer[^"]*"[^>]*>([^<]+)</is', $content, $faq_matches );

			if ( ! empty( $faq_matches[1] ) ) {
				foreach ( $faq_matches[1] as $index => $question ) {
					$answer = isset( $faq_matches[2][ $index ] ) ? $faq_matches[2][ $index ] : '';
					if ( ! empty( $question ) && ! empty( $answer ) ) {
						$faqs[] = array(
							'question' => wp_strip_all_tags( trim( $question ) ),
							'answer'   => wp_strip_all_tags( trim( $answer ) ),
						);
					}
				}
			}

			// Pattern 4: Bold Q and A pattern
			preg_match_all( '/<p[^>]*><strong>(?:Q|C\xf2u h\xf3i|Question):?\s*<\/strong>([^<]+)<\/p>\s*<p[^>]*><strong>(?:A|Tr\u1ea3 l\u1eddi|Answer):?\s*<\/strong>([^<]+)<\/p>/iu', $content, $qa_matches );

			if ( ! empty( $qa_matches[1] ) ) {
				foreach ( $qa_matches[1] as $index => $question ) {
					$answer = isset( $qa_matches[2][ $index ] ) ? $qa_matches[2][ $index ] : '';
					if ( ! empty( $question ) && ! empty( $answer ) ) {
						$faqs[] = array(
							'question' => wp_strip_all_tags( trim( $question ) ),
							'answer'   => wp_strip_all_tags( trim( $answer ) ),
						);
					}
				}
			}

			return $faqs;
		}

		/**
		 * Build FAQ Schema
		 *
		 * @param array $faqs Array of FAQs.
		 * @return array
		 */
		public function build_faq_schema( $faqs ) {
			$schema = array(
				'@context' => 'https://schema.org',
				'@type'    => 'FAQPage',
			);

			$main_entity = array();
			foreach ( $faqs as $faq ) {
				if ( empty( $faq['question'] ) || empty( $faq['answer'] ) ) {
					continue;
				}

				$main_entity[] = array(
					'@type'          => 'Question',
					'name'           => $faq['question'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $faq['answer'],
					),
				);
			}

			if ( ! empty( $main_entity ) ) {
				$schema['mainEntity'] = $main_entity;
			}

			return apply_filters( 'wp_schema_pro_auto_faq_schema', $schema, $faqs );
		}

		/**
		 * Auto-extract FAQ filter for existing schema
		 *
		 * @param array $questions Existing questions.
		 * @param int   $post_id Post ID.
		 * @return array
		 */
		public function auto_extract_faq( $questions, $post_id ) {
			// If already has questions, return as-is
			if ( ! empty( $questions ) ) {
				return $questions;
			}

			// Try to extract from content
			$extracted = $this->get_faq_from_content( $post_id );

			if ( ! empty( $extracted ) ) {
				return $extracted;
			}

			return $questions;
		}

		/**
		 * Register FAQ meta box for posts/products
		 *
		 * @return void
		 */
		public static function register_faq_meta() {
			register_post_meta( '', '_schema_pro_faq', array(
				'type'         => 'array',
				'single'       => true,
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'question' => array( 'type' => 'string' ),
								'answer'   => array( 'type' => 'string' ),
							),
						),
					),
				),
			) );
		}
	}
}

// Register meta
add_action( 'init', array( 'BSF_AIOSRS_Pro_FAQ_Auto', 'register_faq_meta' ) );

/**
 * Initialize FAQ Auto
 */
BSF_AIOSRS_Pro_FAQ_Auto::get_instance();
