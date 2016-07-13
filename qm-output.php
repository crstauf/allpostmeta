<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class CSS_QM_Output_Html_allpostmeta extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 61 );
	}

	public function output() {
		global $wp_the_query;

		if (!isset($wp_the_query) || !is_object($wp_the_query->post) || !count($wp_the_query->post)) global $post;
		else $post = $wp_the_query->post;

		$meta = array();

		if (is_admin() && isset($post) && is_object($post))
			foreach ($post as $key => $value)
				$meta['Object'][$key] = $value;

		if ($taxes = get_object_taxonomies(get_post_type($post),'objects')) {
			foreach ($taxes as $slug => $tax) {
				$terms = get_the_terms($post->ID,$slug);
				if (is_array($terms)) {
					$meta['Taxonomies'][$slug] = '';
					foreach ($terms as $term)
						if ('' !== $slug)
							$meta['Taxonomies'][$slug] .= '<a href="' . esc_url(get_edit_term_link($term->term_id,$slug)) . '">' . $term->name . '</a>, ';
					if ('' !== $meta['Taxonomies'][$slug])
						$meta['Taxonomies'][$slug] = substr($meta['Taxonomies'][$slug],0,strlen($meta['Taxonomies'][$slug]) - 2);
				}
			}
		}

		$custom = get_post_custom($post->ID);
		ksort($custom);
		foreach ($custom as $key => $value)
			if (is_array($value) && 1 === count($value))
				$meta['Custom Fields'][$key] = $value[0];
			else
				$meta['Custom Fields'][$key] = $value;

			$imgs = new WP_Query(array(
				'post_type' => 'attachment',
				'posts_per_page' => -1,
				'post_status' => 'inherit',
				'post_parent' => $post->ID,
			));

			if ($imgs->have_posts()) {
				$orig = $post;
				$meta['Attached Images'][' '] = '';
				while ($imgs->have_posts()) {
					$imgs->the_post();
					$thumb = wp_get_attachment_image_src(get_the_ID(),'thumbnail');
					$meta['Attached Images'][' '] .= '<a href="' . get_edit_post_link(get_the_ID()) . '" target="_blank"><img src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" alt="' . get_the_ID() . '" style="width: 50px; height: auto;" /></a>';
				}
				$post = $orig;
				wp_reset_postdata();
			}

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-full">';

		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th colspan="3">All Post Meta</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ($meta as $name => $group) {
			$first = true;

			echo '<tr><td rowspan="' . count($meta[$name]) . '">' . $name . '</td>';

			foreach ($group as $key => $value) {
				$v = allpostmeta::value($value,('Custom Fields' !== $name ? false : true));
				if (true !== $first) echo '<tr>';
					if ('' !== trim($key)) echo '<td>' . $key . '</td>';
					echo '<td' . ('' === trim($key) ? ' colspan="3"' : '') . '><div style="overflow: auto; max-width: 100%; max-height: 300px;">' . (is_array($v) || is_object($v) ? '<pre>' . print_r($v,true) . '</pre>' : $v) . '</div></td>';
				echo '</tr>';
				$first = false;
			}
		}

		echo '</tbody>';
		echo '</table>';

		echo '</div>';

	}

}

?>
