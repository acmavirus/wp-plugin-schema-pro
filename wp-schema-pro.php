<?php

/**
 * Plugin Name: WP Schema Pro
 * Description: Advanced Schema implementation for WordPress (Organization, Product, LocalBusiness, etc.)
 * Version: 1.0.0
 * Author: Antigravity
 * Text Domain: wp-schema-pro
 */

// Copyright by AcmaTvirus

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Autoload dependencies
 */
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Initialize Plugin
 */
if (class_exists('Acma\\WpSchemaPro\\Plugin')) {
	\Acma\WpSchemaPro\Plugin::instance()->run();
}

/**
 * Legacy compatibility (Will be refactored soon)
 */

class WP_Schema_Pro
{

	public function __construct()
	{
		// Output schemas very early in the head
		add_action('wp_head', array($this, 'output_schema'), 1);

		add_filter('the_content', array($this, 'display_rating_after_content'));
		add_action('wp_head', array($this, 'output_rating_css'));
		add_action('wp_footer', array($this, 'output_rating_js'));

		// AJAX Handlers
		add_action('wp_ajax_wpsp_submit_rating', array($this, 'handle_rating_submission'));
		add_action('wp_ajax_nopriv_wpsp_submit_rating', array($this, 'handle_rating_submission'));

		// Admin Settings
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
	}

	public function output_schema()
	{
		if (is_admin()) return;

		$graph = array(
			"@context" => "https://schema.org",
			"@graph"   => array()
		);

		// Core Entities
		$graph['@graph'][] = $this->get_organization_schema();
		$graph['@graph'][] = $this->get_local_business_schema();
		$graph['@graph'][] = $this->get_website_schema();

		// Page Specific
		if (is_category()) {
			$graph['@graph'][] = $this->get_category_post_schema();
			$graph['@graph'][] = $this->get_item_list_post_schema();
			$graph['@graph'][] = $this->get_breadcrumb_schema();
		}

		if (is_single() && ! (function_exists('is_product') && is_product())) {
			$graph['@graph'][] = $this->get_breadcrumb_schema();
		}

		if (function_exists('is_product') && is_product()) {
			$product_schema = $this->get_product_schema();
			if (! empty($product_schema)) {
				$graph['@graph'][] = $product_schema;
			}
			$faq_schema = $this->get_product_faq(get_queried_object_id());
			if (! empty($faq_schema)) {
				$graph['@graph'][] = $faq_schema;
			}
			$graph['@graph'][] = $this->get_breadcrumb_schema();
		}

		if (function_exists('is_product_category') && is_product_category()) {
			$graph['@graph'][] = $this->get_product_category_schema();
			$graph['@graph'][] = $this->get_item_list_product_schema();
			$graph['@graph'][] = $this->get_breadcrumb_schema();
		}

		// Filter out empty items
		$graph['@graph'] = array_filter($graph['@graph']);

		if (! empty($graph['@graph'])) {
			echo "\n<!-- WP Schema Pro Output -->\n";
			echo '<script type="application/ld+json">' . wp_json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
			echo "<!-- /WP Schema Pro Output -->\n";
		}
	}

	private function get_organization_schema()
	{
		$options = get_option('wpsp_settings');
		return array(
			"@type" => "Organization",
			"name" => ! empty($options['org_name']) ? $options['org_name'] : get_bloginfo('name'),
			"url" => ! empty($options['org_url']) ? $options['org_url'] : home_url(),
			"logo" => ! empty($options['org_logo']) ? $options['org_logo'] : ""
		);
	}

	private function get_local_business_schema()
	{
		$options = get_option('wpsp_settings');
		return array(
			"@type" => "LocalBusiness",
			"name" => ! empty($options['org_name']) ? $options['org_name'] : get_bloginfo('name'),
			"url" => ! empty($options['org_url']) ? $options['org_url'] : home_url(),
			"address" => array(
				"@type" => "PostalAddress",
				"streetAddress" => ! empty($options['addr_street']) ? $options['addr_street'] : "",
				"addressLocality" => ! empty($options['addr_locality']) ? $options['addr_locality'] : "",
				"addressRegion" => ! empty($options['addr_region']) ? $options['addr_region'] : "",
				"postalCode" => ! empty($options['addr_postal']) ? $options['addr_postal'] : "",
				"addressCountry" => "VN"
			)
		);
	}

	private function get_website_schema()
	{
		return array(
			"@type" => "WebSite",
			"url" => home_url(),
			"potentialAction" => array(
				"@type" => "SearchAction",
				"target" => home_url('/?s={search_term_string}'),
				"query-input" => "required name=search_term_string"
			)
		);
	}

	private function get_breadcrumb_schema()
	{
		$items = array();
		$items[] = array(
			"@type" => "ListItem",
			"position" => 1,
			"name" => "Trang chủ",
			"item" => home_url()
		);

		if (is_category() || is_tag() || is_tax()) {
			$term = get_queried_object();
			$items[] = array(
				"@type" => "ListItem",
				"position" => 2,
				"name" => $term->name,
				"item" => get_term_link($term)
			);
		} elseif (is_single()) {
			$categories = get_the_category();
			if (! empty($categories)) {
				$items[] = array(
					"@type" => "ListItem",
					"position" => 2,
					"name" => $categories[0]->name,
					"item" => get_category_link($categories[0]->term_id)
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

	private function get_category_post_schema()
	{
		$cat = get_queried_object();
		return array(
			"@type" => "CollectionPage",
			"name" => $cat->name,
			"description" => $cat->description,
			"url" => get_category_link($cat->term_id)
		);
	}

	private function get_item_list_post_schema()
	{
		$items = array();
		if (have_posts()) {
			$counter = 1;
			while (have_posts()) {
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

	private function get_product_schema()
	{
		global $product;
		if (! is_a($product, 'WC_Product')) {
			$product = wc_get_product(get_queried_object_id());
		}

		if (! $product) return array();

		$product_id = $product->get_id();
		$rating_sum   = (float) get_post_meta($product_id, '_wpsp_rating_sum', true);
		$rating_count = (int) get_post_meta($product_id, '_wpsp_rating_count', true);

		if ($rating_count === 0) {
			// Try KK Star Ratings fallback
			$kk_avg = get_post_meta($product_id, '_kk_star_ratings', true);
			$kk_count = get_post_meta($product_id, '_kk_star_ratings_count', true);
			if ($kk_count > 0) {
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
			"@id" => get_permalink($product_id) . "#product",
			"name" => $product->get_name(),
			"image" => wp_get_attachment_url($product->get_image_id()),
			"description" => wp_strip_all_tags($product->get_short_description()),
			"sku" => $product->get_sku(),
			"mpn" => $product->get_sku(),
			"brand" => array(
				"@type" => "Brand",
				"name" => "Két sắt"
			),
			"offers" => array(
				"@type" => "Offer",
				"url" => get_permalink($product_id),
				"priceCurrency" => "VND",
				"price" => $product->get_price(),
				"priceValidUntil" => date('Y-12-31', strtotime('+1 year')),
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

	private function get_product_faq($product_id)
	{
		$faq_items = array();

		// 1. Manual FAQs from Settings Page
		$options = get_option('wpsp_faq_settings');
		$manual_questions = isset($options['faqs']) ? $options['faqs'] : array();

		foreach ($manual_questions as $q) {
			if (empty($q['q']) || empty($q['a'])) continue;
			$faq_items[] = array(
				"@type" => "Question",
				"name" => $q['q'],
				"acceptedAnswer" => array(
					"@type" => "Answer",
					"text" => wp_kses_post($q['a'])
				)
			);
		}

		// 2. Automatic FAQs from Product Attributes (WooCommerce)
		if (function_exists('wc_get_product')) {
			$product = wc_get_product($product_id);
			if (is_a($product, 'WC_Product')) {
				$attributes = $product->get_attributes();
				$product_name = $product->get_name();

				foreach ($attributes as $attr_name => $attr) {
					// Only process visible attributes or all? Let's do all defined for better coverage
					$label = wc_attribute_label($attr_name);
					$value = $product->get_attribute($attr_name);

					if (empty($value)) continue;

					// Basic logic for question generation
					$question = sprintf("Sản phẩm %s có %s như thế nào?", $product_name, $label);
					$answer   = sprintf("Thông tin %s của sản phẩm %s là: %s. Đây là thông số kỹ thuật được cung cấp chính xác từ nhà sản xuất.", $label, $product_name, $value);

					$faq_items[] = array(
						"@type" => "Question",
						"name" => $question,
						"acceptedAnswer" => array(
							"@type" => "Answer",
							"text" => $answer
						)
					);
				}
			}
		}

		if (empty($faq_items)) return array();

		return array(
			"@type" => "FAQPage",
			"mainEntity" => $faq_items
		);
	}

	private function get_product_category_schema()
	{
		$cat = get_queried_object();
		return array(
			"@type" => "CollectionPage",
			"name" => $cat->name,
			"url" => get_term_link($cat)
		);
	}

	private function get_item_list_product_schema()
	{
		$items = array();
		if (have_posts()) {
			$counter = 1;
			while (have_posts()) {
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

	public function display_rating_after_content($content)
	{
		if (! is_product() || ! is_main_query()) {
			return $content;
		}

		$product_id = get_the_ID();
		$rating_sum   = (float) get_post_meta($product_id, '_wpsp_rating_sum', true);
		$rating_count = (int) get_post_meta($product_id, '_wpsp_rating_count', true);

		// Defaults if no ratings yet
		if ($rating_count === 0) {
			$avg = 5.0;
			$rating_count = 1; // Default count for look
		} else {
			$avg = $rating_sum / $rating_count;
		}

		$user_ip = $_SERVER['REMOTE_ADDR'];
		$voted_ips = get_post_meta($product_id, '_wpsp_voted_ips', true);
		if (! is_array($voted_ips)) $voted_ips = array();

		$has_voted = in_array($user_ip, $voted_ips);

		$stars_html = '<div class="wpsp-stars' . ($has_voted ? ' wpsp-voted' : '') . '" data-product-id="' . $product_id . '">';
		for ($i = 1; $i <= 5; $i++) {
			$is_filled = ($i <= round($avg));
			$stars_html .= '<span class="wpsp-star' . ($is_filled ? ' filled' : '') . '" data-value="' . $i . '">★</span>';
		}
		$stars_html .= '</div>';

		$rating_html = sprintf(
			'<div class="wpsp-visual-rating">%s <span class="wpsp-rating-text"><span class="wpsp-avg">%s</span>/5 - (<span class="wpsp-count">%d</span> Đánh giá)</span>%s</div>',
			$stars_html,
			number_format($avg, 1),
			$rating_count,
			$has_voted ? '<div class="wpsp-voted-msg">Bạn đã đánh giá sản phẩm này.</div>' : ''
		);

		return $content . $rating_html;
	}

	public function output_rating_css()
	{
		if (! is_product()) {
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
			.wpsp-stars:not(.wpsp-voted) .wpsp-star:hover~.wpsp-star {
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

	public function output_rating_js()
	{
		if (! is_product()) return;
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

	public function handle_rating_submission()
	{
		$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
		$rating     = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

		if (! $product_id || ! $rating || $rating < 1 || $rating > 5) {
			wp_send_json_error('Dữ liệu không hợp lệ.');
		}

		$user_ip = $_SERVER['REMOTE_ADDR'];
		$voted_ips = get_post_meta($product_id, '_wpsp_voted_ips', true);
		if (! is_array($voted_ips)) $voted_ips = array();

		if (in_array($user_ip, $voted_ips)) {
			wp_send_json_error('Bạn đã đánh giá sản phẩm này rồi.');
		}

		// Update stats
		$rating_sum   = (float) get_post_meta($product_id, '_wpsp_rating_sum', true);
		$rating_count = (int) get_post_meta($product_id, '_wpsp_rating_count', true);

		$rating_sum += $rating;
		$rating_count++;

		update_post_meta($product_id, '_wpsp_rating_sum', $rating_sum);
		update_post_meta($product_id, '_wpsp_rating_count', $rating_count);

		// Update IP log
		$voted_ips[] = $user_ip;
		update_post_meta($product_id, '_wpsp_voted_ips', $voted_ips);

		wp_send_json_success('Cảm ơn bạn đã đánh giá!');
	}

	public function add_admin_menu()
	{
		add_options_page(
			'WP Schema Pro Settings',
			'WP Schema Pro',
			'manage_options',
			'wp-schema-pro',
			array($this, 'settings_page_html')
		);
	}

	public function register_settings()
	{
		register_setting('wpsp_settings_group', 'wpsp_settings');
		register_setting('wpsp_faq_group', 'wpsp_faq_settings');

		// General Section
		add_settings_section(
			'wpsp_org_section',
			'Organization & LocalBusiness Settings',
			null,
			'wp-schema-pro'
		);

		$fields = array(
			'org_name'      => 'Organization Name',
			'org_url'       => 'Website URL',
			'org_logo'      => 'Logo URL',
			'addr_street'   => 'Street Address',
			'addr_locality' => 'Locality (City)',
			'addr_region'   => 'Region',
			'addr_postal'   => 'Postal Code',
		);

		foreach ($fields as $id => $title) {
			add_settings_field(
				$id,
				$title,
				array($this, 'render_field'),
				'wp-schema-pro',
				'wpsp_org_section',
				array('id' => $id)
			);
		}

		// FAQ Section
		add_settings_section(
			'wpsp_faq_section',
			'Product FAQ Settings',
			null,
			'wp-schema-pro-faq'
		);

		add_settings_field(
			'faqs',
			'FAQ List',
			array($this, 'render_faq_repeater'),
			'wp-schema-pro-faq',
			'wpsp_faq_section'
		);
	}

	public function enqueue_admin_scripts($hook)
	{
		if ('settings_page_wp-schema-pro' !== $hook) {
			return;
		}
		wp_enqueue_media();
	}

	public function render_field($args)
	{
		$options = get_option('wpsp_settings');
		$id      = $args['id'];
		$value   = isset($options[$id]) ? esc_attr($options[$id]) : '';

		if ('org_logo' === $id) {
			echo '<div class="wpsp-logo-picker-wrap">';
			echo '<img class="wpsp-logo-preview" src="' . ($value ? $value : '') . '" style="max-width:100px; display:' . ($value ? 'block' : 'none') . '; margin-bottom:10px;">';
			echo '<div class="wpsp-logo-input-group">';
			echo '<input type="text" id="wpsp_org_logo" name="wpsp_settings[org_logo]" value="' . $value . '" class="regular-text">';
			echo '<button type="button" class="button wpsp-upload-logo">Chọn ảnh</button>';
			echo '</div>';
			echo '</div>';
			return;
		}

		echo '<input type="text" name="wpsp_settings[' . $id . ']" value="' . $value . '" class="regular-text">';
	}

	public function render_faq_repeater()
	{
		$options = get_option('wpsp_faq_settings');
		$faqs    = isset($options['faqs']) ? $options['faqs'] : array();
	?>
		<div id="wpsp-faq-repeater">
			<div class="faq-items">
				<?php if (! empty($faqs)) : foreach ($faqs as $index => $faq) : ?>
						<div class="faq-item" style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; background: #fafafa; position: relative;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
								<span style="font-weight: bold; color: #1d2327;">Câu hỏi #<?php echo $index + 1; ?></span>
								<button type="button" class="button remove-faq" style="color: #d63638; border-color: #d63638;">Xóa</button>
							</div>
							<p style="margin-bottom: 15px;">
								<label style="display: block; font-weight: 600; margin-bottom: 5px;">Câu hỏi:</label>
								<input type="text" name="wpsp_faq_settings[faqs][<?php echo $index; ?>][q]" value="<?php echo esc_attr($faq['q']); ?>" style="width: 100%; height: 35px;">
							</p>
							<p style="margin-bottom: 0;">
								<label style="display: block; font-weight: 600; margin-bottom: 5px;">Trả lời:</label>
								<textarea name="wpsp_faq_settings[faqs][<?php echo $index; ?>][a]" style="width: 100%; height: 80px;"><?php echo esc_textarea($faq['a']); ?></textarea>
							</p>
						</div>
					<?php endforeach;
				else : ?>
					<div class="faq-item" style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; background: #fafafa; position: relative;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
							<span style="font-weight: bold; color: #1d2327;">Câu hỏi #1</span>
							<button type="button" class="button remove-faq" style="color: #d63638; border-color: #d63638;">Xóa</button>
						</div>
						<p style="margin-bottom: 15px;">
							<label style="display: block; font-weight: 600; margin-bottom: 5px;">Câu hỏi:</label>
							<input type="text" name="wpsp_faq_settings[faqs][0][q]" value="" style="width: 100%; height: 35px;">
						</p>
						<p style="margin-bottom: 0;">
							<label style="display: block; font-weight: 600; margin-bottom: 5px;">Trả lời:</label>
							<textarea name="wpsp_faq_settings[faqs][0][a]" style="width: 100%; height: 80px;"></textarea>
						</p>
					</div>
				<?php endif; ?>
			</div>
			<button type="button" class="button add-faq button-primary" style="margin-top: 10px;">Thêm câu hỏi mới</button>
		</div>
		<script>
			jQuery(document).ready(function($) {
				$('.add-faq').click(function() {
					var index = $('.faq-item').length;
					var html = `
					<div class="faq-item" style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; background: #fafafa; position: relative;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
							<span style="font-weight: bold; color: #1d2327;">Câu hỏi #${index + 1}</span>
							<button type="button" class="button remove-faq" style="color: #d63638; border-color: #d63638;">Xóa</button>
						</div>
						<p style="margin-bottom: 15px;">
							<label style="display: block; font-weight: 600; margin-bottom: 5px;">Câu hỏi:</label>
							<input type="text" name="wpsp_faq_settings[faqs][${index}][q]" value="" style="width: 100%; height: 35px;">
						</p>
						<p style="margin-bottom: 0;">
							<label style="display: block; font-weight: 600; margin-bottom: 5px;">Trả lời:</label>
							<textarea name="wpsp_faq_settings[faqs][${index}][a]" style="width: 100%; height: 80px;"></textarea>
						</p>
					</div>
				`;
					$('.faq-items').append(html);
				});
				$(document).on('click', '.remove-faq', function() {
					$(this).closest('.faq-item').remove();
					$('.faq-item').each(function(i) {
						$(this).find('span').first().text('Câu hỏi #' + (i + 1));
					});
				});
			});
		</script>
	<?php
	}

	public function settings_page_html()
	{
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
	?>
		<div class="wrap wpsp-settings-wrap">
			<div class="wpsp-header">
				<h1>WP Schema Pro Settings</h1>
				<p class="description">Configure your site's global schema information for better search engine visibility.</p>
			</div>

			<h2 class="nav-tab-wrapper">
				<a href="?page=wp-schema-pro&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">Cài đặt chung</a>
				<a href="?page=wp-schema-pro&tab=faq" class="nav-tab <?php echo $active_tab == 'faq' ? 'nav-tab-active' : ''; ?>">Cấu hình FAQ Sản phẩm</a>
			</h2>

			<div class="wpsp-card" style="margin-top: 20px;">
				<form action="options.php" method="post">
					<?php if ($active_tab == 'general') : ?>
						<?php
						settings_fields('wpsp_settings_group');
						do_settings_sections('wp-schema-pro');
						?>
					<?php else : ?>
						<?php
						settings_fields('wpsp_faq_group');
						do_settings_sections('wp-schema-pro-faq');
						?>
					<?php endif; ?>
					<?php submit_button(); ?>
				</form>
			</div>

			<style>
				.wpsp-settings-wrap {
					max-width: 900px;
					margin-top: 20px;
				}

				.wpsp-header {
					margin-bottom: 20px;
				}

				.wpsp-header h1 {
					font-weight: 700;
					color: #1d2327;
					margin-bottom: 5px;
				}

				.wpsp-card {
					background: #fff;
					border: 1px solid #ccd0d4;
					box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
					padding: 30px;
					border-radius: 8px;
				}

				.nav-tab-wrapper {
					margin-bottom: 0;
					border-bottom: 1px solid #ccd0d4;
				}

				.form-table th {
					width: 240px;
					padding: 20px 10px 20px 0;
					font-weight: 600;
				}

				.regular-text {
					width: 100%;
					max-width: 450px;
					border-radius: 4px;
					border: 1px solid #8c8f94;
					padding: 8px 12px;
				}

				.wpsp-logo-picker-wrap {
					display: flex;
					flex-direction: column;
					gap: 10px;
					align-items: flex-start;
				}

				.wpsp-logo-preview {
					border: 1px solid #ddd;
					padding: 10px;
					background: #f9f9f9;
					border-radius: 4px;
					box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
					max-height: 120px;
					object-fit: contain;
				}

				.wpsp-logo-input-group {
					display: flex;
					gap: 10px;
					width: 100%;
					max-width: 550px;
				}

				.wpsp-logo-input-group input {
					flex-grow: 1;
				}

				.wpsp-upload-logo {
					height: auto !important;
					padding: 5px 15px !important;
				}

				.submit {
					margin-top: 20px;
					padding-top: 20px;
					border-top: 1px solid #f0f0f1;
				}
			</style>
		</div>
		<script>
			jQuery(document).ready(function($) {
				$('.wpsp-upload-logo').click(function(e) {
					e.preventDefault();
					var button = $(this);
					var imgTag = button.closest('.wpsp-logo-picker-wrap').find('.wpsp-logo-preview');
					var inputTag = button.closest('.wpsp-logo-picker-wrap').find('input');

					var custom_uploader = wp.media({
						title: 'Chọn Logo',
						button: {
							text: 'Dùng ảnh này'
						},
						multiple: false
					}).on('select', function() {
						var attachment = custom_uploader.state().get('selection').first().toJSON();
						imgTag.attr('src', attachment.url).show();
						inputTag.val(attachment.url);
					}).open();
				});
			});
		</script>
<?php
	}
}

new WP_Schema_Pro();
