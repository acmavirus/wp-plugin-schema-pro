<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Faq_Page' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Faq_Page {

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
			$schema['@type']    = 'FAQPage';

			if ( isset( $data['questions'] ) ) {
				$questions = apply_filters( 'wp_schema_pro_faq_questions', $data['questions'], $post['ID'] );
				if ( ! empty( $questions ) ) {
					foreach ( $questions as $key => $value ) {
						$schema['mainEntity'][ $key ]['@type']          = 'Question';
						$schema['mainEntity'][ $key ]['name']           = wp_strip_all_tags( $value['question'] );
						$schema['mainEntity'][ $key ]['acceptedAnswer']['@type'] = 'Answer';
						$schema['mainEntity'][ $key ]['acceptedAnswer']['text']  = $value['answer'];
					}
				}
			}

			return apply_filters( 'wp_schema_pro_schema_faq_page', $schema, $data, $post );
		}

	}
}
