<?php
/*
Plugin Name: All Post Meta
Plugin URI:
Description: Shows all post information: post object, attached images, (hidden) meta data
Version: 0.0.2
Author: Caleb Stauffer
Author URI: http://develop.calebstauffer.com
*/

new allpostmeta;

class allpostmeta {

	function __construct() {
		add_filter('qm/collectors',array(__CLASS__,'register_qm_collector'),20,2);
		add_filter('qm/outputter/html',array(__CLASS__,'register_qm_output'),76,2);
		add_action('plugins_loaded',array('allpostmeta','init'));
	}

	public static function init() {
		if (!class_exists('QueryMonitor') && is_admin())
			add_action('add_meta_boxes',array(__CLASS__,'add_meta_boxes'));
	}

		public static function register_qm_collector( array $collectors, QueryMonitor $qm ) {
			$collectors['allpostmeta'] = new CSS_QM_Collector_allpostmeta;
			return $collectors;
		}

		public static function register_qm_output( array $output, QM_Collectors $collectors ) {
			require_once plugin_dir_path(__FILE__) . 'qm-output.php';
			if ( $collector = QM_Collectors::get( 'allpostmeta' ) ) {
				$output['allpostmeta'] = new CSS_QM_Output_Html_allpostmeta( $collector );
			}
			return $output;
		}

	public static function add_meta_boxes() {
		add_meta_box('allpostmeta','All Post Meta',array(__CLASS__,'meta_box'),'','advanced');
	}

	public static function meta_box($post) {
		echo '<table class="code" cellspacing="0" cellpadding="0" style="width: 100%;">';
		echo '<tbody>';
		$i = 0;

		foreach ($post as $k => $v) {
			$i++;
			$value = self::value($v);
			if ('post_content' == $k) $v = '<textarea rows="2" readonly="readonly" style="width: 98%; max-width: 98%; padding: 3px 5px; font-size: 11px; resize: vertical;">' . $v . '</textarea>';
			echo '<tr style="' . (0 == ($i % 2) ? ' background-color: #F6F6F6;' : '') . '">';
				echo '<td style="width: 45%; padding: 5px;">' . $k . '</td>';
				echo '<td style="width: 45%; padding: 5px;"><div style="overflow: auto; max-width: 100%; max-height: 300px;">' . (is_array($value) || is_object($value) ? '<pre>' . print_r($value,true) . '</pre>' : $value) . '</div></td>';
			echo '</tr>';
		}

		echo '<tr><td colspan="2">&nbsp;</td></tr>';

		foreach (get_post_custom($post->ID) as $k => $array) {
			$i++;
			$array = self::value($array);
			echo '<tr style="' . (0 == ($i % 2) ? ' background-color: #F6F6F6;' : '') . '">';
				echo '<td style="width: 45%; padding: 5px;">' . $k . '</td>';
				echo '<td style="width: 45%; padding: 5px;"><div style="overflow: auto; max-width: 100%; max-height: 300px;"><pre>' . print_r($array,true) . '</pre></div></td>';
			echo '</tr>';
		}

		if ($taxes = get_object_taxonomies(get_post_type($post),'objects')) {

			echo '<tr><td colspan="2">&nbsp;</td></tr>';

			foreach ($taxes as $slug => $tax) {
				$i++;
				echo '<tr style="' . (0 == ($i % 2) ? ' background-color: #F6F6F6;' : '') . '">';
					echo '<td style="width: 45%; padding: 5px;">' . $slug . '</td>';
					echo '<td style="width: 45%; padding: 5px;">';
						$output = array();
						$terms = get_the_terms($post->ID,$slug);
						if (is_array($terms) && count($terms)) {
							foreach ($terms as $term) $output[] = $term->name;
							echo implode('", "',$output) . '"';
						}
					 echo '</td>';
				echo '</tr>';
			}

		}

		echo '</tbody>';
		echo '</table>';
	}

	public static function value($value,$entities = true) {
		$value = maybe_unserialize($value);
		if (is_array($value) || is_object($value))
			foreach ($value as $k => $v)
				$value[$k] = self::value($v);
		else if (true === $entities) $value = htmlentities($value);
		return $value;
	}

}

if (class_exists('QM_Collector')) {
	class CSS_QM_Collector_allpostmeta extends QM_Collector {

		public $id = 'allpostmeta';

		public function name() {
			return __( 'All Post Meta', 'query-monitor' );
		}

		public function __construct() {

			global $wpdb;

			parent::__construct();

		}

		public function process() {
			$this->data['allpostmeta'] = array('a','b','c','d');

		}

	}
}

?>
