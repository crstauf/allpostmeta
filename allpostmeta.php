<?php
/*
Plugin Name: All Post Meta
Plugin URI: 
Description: Shows all post meta, even hidden ones...
Version: 0.0.1
Author: Caleb Stauffer
Author URI: http://develop.calebstauffer.com
*/

new allpostmeta;

class allpostmeta {

	function __construct() {
		if (is_admin()) add_action('add_meta_boxes',array(__CLASS__,'add_meta_boxes'));
	}

	public static function add_meta_boxes() {
		add_meta_box('allpostmeta','All Post Meta',array(__CLASS__,'meta_box'),'','advanced');
	}

	public static function meta_box($post) {
		echo '<table class="code" cellspacing="0" cellpadding="0" style="width: 100%;">';
		echo '<tbody>';
		$i = 0;
		foreach (get_post_custom($post->ID) as $k => $array) {
			$i++;
			if (1 == count($array)) $v = $array[0];
			else $v = print_r($array,true);
			echo '<tr style="' . (0 == ($i % 2) ? ' background-color: #F6F6F6;' : '') . '">';
				echo '<td style="width: 45%; padding: 5px;">' . $k . '</td>';
				echo '<td style="width: 45%; padding: 5px;">' . htmlentities($v) . '</td>';
			echo '</tr>';
		}

		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		
		foreach ($post as $k => $v) {
			$i++;
			if ('post_content' == $k) $v = '<textarea rows="2" readonly="readonly" style="width: 98%; max-width: 98%; padding: 3px 5px; font-size: 11px; resize: vertical;">' . $v . '</textarea>';
			echo '<tr style="' . (0 == ($i % 2) ? ' background-color: #F6F6F6;' : '') . '">';
				echo '<td style="width: 45%; padding: 5px;">' . $k . '</td>';
				echo '<td style="width: 45%; padding: 5px;">' . $v . '</td>';
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
							echo implode(', ',$output);
						}
					 echo '</td>';
				echo '</tr>';
			}

		}

		echo '</tbody>';
		echo '</table>';
	}
	
}

?>