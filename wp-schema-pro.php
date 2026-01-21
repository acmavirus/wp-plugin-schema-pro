<?php
/**
 * Plugin Name: WP Schema Pro
 * Description: Advanced Schema implementation for WordPress (Organization, Product, LocalBusiness, etc.)
 * Version: 1.0.0
 * Author: Antigravity
 * Text Domain: wp-schema-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Schema_Pro {

	public function __construct() {
		// Output schemas very early in the head
		add_action( 'wp_head', array( $this, 'output_schema' ), 1 );
		
		add_filter( 'the_content', array( $this, 'display_rating_after_content' ) );
		add_action( 'wp_head', array( $this, 'output_rating_css' ) );
		add_action( 'wp_footer', array( $this, 'output_rating_js' ) );

		// AJAX Handlers
		add_action( 'wp_ajax_wpsp_submit_rating', array( $this, 'handle_rating_submission' ) );
		add_action( 'wp_ajax_nopriv_wpsp_submit_rating', array( $this, 'handle_rating_submission' ) );
	}

	public function output_schema() {
		if ( is_admin() ) return;

		$graph = array(
			"@context" => "https://schema.org",
			"@graph"   => array()
		);

		// Core Entities
		$graph['@graph'][] = $this->get_organization_schema();
		$graph['@graph'][] = $this->get_local_business_schema();
		$graph['@graph'][] = $this->get_website_schema();

		// Page Specific
		if ( is_category() ) {
			$graph['@graph'][] = $this->get_category_post_schema();
			$graph['@graph'][] = $this->get_item_list_post_schema();
			$graph['@graph'][] = $this->get_breadcrumb_schema();
		}

		if ( is_single() && ! ( function_exists( 'is_product' ) && is_product() ) ) {
			$graph['@graph'][] = $this->get_breadcrumb_schema();
		}

		if ( function_exists( 'is_product' ) && is_product() ) {
			$product_schema = $this->get_product_schema();
			if ( ! empty( $product_schema ) ) {
				$graph['@graph'][] = $product_schema;
			}
			$graph['@graph'][] = $this->get_product_faq( get_queried_object_id() );
			$graph['@graph'][] = $this->get_breadcrumb_schema();
		}

		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			$graph['@graph'][] = $this->get_product_category_schema();
			$graph['@graph'][] = $this->get_item_list_product_schema();
			$graph['@graph'][] = $this->get_breadcrumb_schema();
		}

		// Filter out empty items
		$graph['@graph'] = array_filter( $graph['@graph'] );

		if ( ! empty( $graph['@graph'] ) ) {
			echo "\n<!-- WP Schema Pro Output -->\n";
			echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
			echo "<!-- /WP Schema Pro Output -->\n";
		}
	}

	private function get_organization_schema() {
		return array(
			"@type" => "Organization",
			"name" => get_bloginfo( 'name' ),
			"url" => home_url(),
			"logo" => "" // Needs setting
		);
	}

	private function get_local_business_schema() {
		return array(
			"@type" => "LocalBusiness",
			"name" => get_bloginfo( 'name' ),
			"url" => home_url(),
			"address" => array(
				"@type" => "PostalAddress",
				"streetAddress" => "",
				"addressLocality" => "",
				"addressRegion" => "",
				"postalCode" => "",
				"addressCountry" => "VN"
			)
		);
	}

	private function get_website_schema() {
		return array(
			"@type" => "WebSite",
			"url" => home_url(),
			"potentialAction" => array(
				"@type" => "SearchAction",
				"target" => home_url( '/?s={search_term_string}' ),
				"query-input" => "required name=search_term_string"
			)
		);
	}

	private function get_breadcrumb_schema() {
		$items = array();
		$items[] = array(
			"@type" => "ListItem",
			"position" => 1,
			"name" => "Trang chủ",
			"item" => home_url()
		);

		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			$items[] = array(
				"@type" => "ListItem",
				"position" => 2,
				"name" => $term->name,
				"item" => get_term_link( $term )
			);
		} elseif ( is_single() ) {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				$items[] = array(
					"@type" => "ListItem",
					"position" => 2,
					"name" => $categories[0]->name,
					"item" => get_category_link( $categories[0]->term_id )
				);
			}
			$items[] = array(
				"@type" => "ListItem",
				"position" => count($items) + 1,
				"name" => get_the_title(),
				"item" => get_permalink()
			);
		}

		return array(
			"@type" => "BreadcrumbList",
			"itemListElement" => $items
		);
	}

	private function get_category_post_schema() {
		$cat = get_queried_object();
		return array(
			"@type" => "CollectionPage",
			"name" => $cat->name,
			"description" => $cat->description,
			"url" => get_category_link( $cat->term_id )
		);
	}

	private function get_item_list_post_schema() {
		$items = array();
		if ( have_posts() ) {
			$counter = 1;
			while ( have_posts() ) {
				the_post();
				$items[] = array(
					"@type" => "ListItem",
					"position" => $counter++,
					"url" => get_permalink(),
					"name" => get_the_title()
				);
			}
			wp_reset_postdata();
		}
		return array(
			"@type" => "ItemList",
			"itemListElement" => $items
		);
	}

	private function get_product_schema() {
		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( get_queried_object_id() );
		}
		
		if ( ! $product ) return array();

		$product_id = $product->get_id();
		$rating_sum   = (float) get_post_meta( $product_id, '_wpsp_rating_sum', true );
		$rating_count = (int) get_post_meta( $product_id, '_wpsp_rating_count', true );
		
		if ( $rating_count === 0 ) {
			// Try KK Star Ratings fallback
			$kk_avg = get_post_meta( $product_id, '_kk_star_ratings', true );
			$kk_count = get_post_meta( $product_id, '_kk_star_ratings_count', true );
			if ( $kk_count > 0 ) {
				$avg = (float) $kk_avg;
				$rating_count = (int) $kk_count;
			} else {
				$avg = 5.0;
				$rating_count = 1;
			}
		} else {
			$avg = $rating_sum / $rating_count;
		}

		$schema = array(
			"@type" => "Product",
			"@id" => get_permalink( $product_id ) . "#product",
			"name" => $product->get_name(),
			"image" => wp_get_attachment_url( $product->get_image_id() ),
			"description" => wp_strip_all_tags( $product->get_short_description() ),
			"sku" => $product->get_sku(),
			"mpn" => $product->get_sku(),
			"brand" => array(
				"@type" => "Brand",
				"name" => "Két sắt"
			),
			"offers" => array(
				"@type" => "Offer",
				"url" => get_permalink( $product_id ),
				"priceCurrency" => "VND",
				"price" => $product->get_price(),
				"priceValidUntil" => date( 'Y-12-31', strtotime( '+1 year' ) ),
				"availability" => $product->is_in_stock() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
				"itemCondition" => "https://schema.org/NewCondition"
			),
			"aggregateRating" => array(
				"@type" => "AggregateRating",
				"ratingValue" => (float) number_format($avg, 1),
				"bestRating" => "5",
				"worstRating" => "1",
				"ratingCount" => (int) $rating_count,
				"reviewCount" => (int) $rating_count
			)
		);

		return $schema;
	}

	private function get_product_faq( $product_id ) {
		// Example placeholders for FAQ
		$questions = array(
			array(
				"question" => "Két sắt này có chống cháy không?",
				"answer" => "Có, sản phẩm được thiết kế với khả năng chống cháy cao."
			),
			array(
				"question" => "Bảo hành sản phẩm trong bao lâu?",
				"answer" => "Sản phẩm được bảo hành chính hãng 5 năm."
			)
		);

		$faq_items = array();
		foreach ( $questions as $q ) {
			$faq_items[] = array(
				"@type" => "Question",
				"name" => $q['question'],
				"acceptedAnswer" => array(
					"@type" => "Answer",
					"text" => $q['answer']
				)
			);
		}

		return array(
			"@type" => "FAQPage",
			"mainEntity" => $faq_items
		);
	}

	private function get_product_category_schema() {
		$cat = get_queried_object();
		return array(
			"@type" => "CollectionPage",
			"name" => $cat->name,
			"url" => get_term_link( $cat )
		);
	}

	private function get_item_list_product_schema() {
		$items = array();
		if ( have_posts() ) {
			$counter = 1;
			while ( have_posts() ) {
				the_post();
				$items[] = array(
					"@type" => "ListItem",
					"position" => $counter++,
					"url" => get_permalink(),
					"name" => get_the_title()
				);
			}
			wp_reset_postdata();
		}
		return array(
			"@context" => "https://schema.org",
			"@type" => "ItemList",
			"itemListElement" => $items
		);
	}

	public function display_rating_after_content( $content ) {
		if ( ! is_product() || ! is_main_query() ) {
			return $content;
		}

		$product_id = get_the_ID();
		$rating_sum   = (float) get_post_meta( $product_id, '_wpsp_rating_sum', true );
		$rating_count = (int) get_post_meta( $product_id, '_wpsp_rating_count', true );
		
		// Defaults if no ratings yet
		if ( $rating_count === 0 ) {
			$avg = 5.0;
			$rating_count = 1; // Default count for look
		} else {
			$avg = $rating_sum / $rating_count;
		}

		$user_ip = $_SERVER['REMOTE_ADDR'];
		$voted_ips = get_post_meta( $product_id, '_wpsp_voted_ips', true );
		if ( ! is_array( $voted_ips ) ) $voted_ips = array();
		
		$has_voted = in_array( $user_ip, $voted_ips );

		$stars_html = '<div class="wpsp-stars' . ( $has_voted ? ' wpsp-voted' : '' ) . '" data-product-id="' . $product_id . '">';
		for ( $i = 1; $i <= 5; $i++ ) {
			$is_filled = ( $i <= round($avg) );
			$stars_html .= '<span class="wpsp-star' . ( $is_filled ? ' filled' : '' ) . '" data-value="' . $i . '">★</span>';
		}
		$stars_html .= '</div>';

		$rating_html = sprintf(
			'<div class="wpsp-visual-rating">%s <span class="wpsp-rating-text"><span class="wpsp-avg">%s</span>/5 - (<span class="wpsp-count">%d</span> Đánh giá)</span>%s</div>',
			$stars_html,
			number_format( $avg, 1 ),
			$rating_count,
			$has_voted ? '<div class="wpsp-voted-msg">Bạn đã đánh giá sản phẩm này.</div>' : ''
		);

		return $content . $rating_html;
	}

	public function output_rating_css() {
		if ( ! is_product() ) {
			return;
		}
		?>
		<style>
			.wpsp-visual-rating {
				margin-top: 20px;
				padding: 15px 0;
				border-top: 1px solid #eee;
				display: flex;
				flex-direction: row;
				align-items: center;
				font-family: inherit;
				flex-wrap: wrap;
			}
			.wpsp-stars {
				display: flex;
				cursor: pointer;
				margin-right: 10px;
			}
			.wpsp-stars.wpsp-voted {
				cursor: default;
				pointer-events: none;
			}
			.wpsp-star {
				font-size: 24px;
				color: #ccc;
				margin-right: 2px;
				transition: color 0.2s;
			}
			.wpsp-stars:not(.wpsp-voted) .wpsp-star:hover,
			.wpsp-stars:not(.wpsp-voted) .wpsp-star:hover ~ .wpsp-star {
				color: #ccc !important;
			}
			.wpsp-stars:not(.wpsp-voted):hover .wpsp-star {
				color: #ffdc00;
			}
			.wpsp-star.filled {
				color: #ffdc00;
				text-shadow: 0 0 1px #e6c300;
			}
			.wpsp-rating-text {
				font-size: 18px;
				color: #333;
			}
			.wpsp-voted-msg {
				font-size: 14px;
				color: #28a745;
				margin-left: 15px;
				width: 100%;
				margin-top: 5px;
			}
			.wpsp-loading {
				opacity: 0.5;
				pointer-events: none;
			}
		</style>
		<?php
	}

	public function output_rating_js() {
		if ( ! is_product() ) return;
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const starsContainer = document.querySelector('.wpsp-stars:not(.wpsp-voted)');
			if (!starsContainer) return;

			const stars = starsContainer.querySelectorAll('.wpsp-star');
			const productId = starsContainer.getAttribute('data-product-id');

			stars.forEach(star => {
				star.addEventListener('click', function() {
					const value = this.getAttribute('data-value');
					
					starsContainer.classList.add('wpsp-loading');

					fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams({
							action: 'wpsp_submit_rating',
							product_id: productId,
							rating: value
						})
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							location.reload(); // Simple reload to update view
						} else {
							alert(data.data || 'Đã có lỗi xảy ra.');
							starsContainer.classList.remove('wpsp-loading');
						}
					});
				});
			});
		});
		</script>
		<?php
	}

	public function handle_rating_submission() {
		$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
		$rating     = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

		if ( ! $product_id || ! $rating || $rating < 1 || $rating > 5 ) {
			wp_send_json_error('Dữ liệu không hợp lệ.');
		}

		$user_ip = $_SERVER['REMOTE_ADDR'];
		$voted_ips = get_post_meta( $product_id, '_wpsp_voted_ips', true );
		if ( ! is_array( $voted_ips ) ) $voted_ips = array();

		if ( in_array( $user_ip, $voted_ips ) ) {
			wp_send_json_error('Bạn đã đánh giá sản phẩm này rồi.');
		}

		// Update stats
		$rating_sum   = (float) get_post_meta( $product_id, '_wpsp_rating_sum', true );
		$rating_count = (int) get_post_meta( $product_id, '_wpsp_rating_count', true );

		$rating_sum += $rating;
		$rating_count++;

		update_post_meta( $product_id, '_wpsp_rating_sum', $rating_sum );
		update_post_meta( $product_id, '_wpsp_rating_count', $rating_count );

		// Update IP log
		$voted_ips[] = $user_ip;
		update_post_meta( $product_id, '_wpsp_voted_ips', $voted_ips );

		wp_send_json_success('Cảm ơn bạn đã đánh giá!');
	}
}

new WP_Schema_Pro();
