<?php
/*
Plugin Name: WP Goo Shortener
Plugin URI: http://www.pureweber.com
Description: Google 短地址生成器，解析器。可以选择在文章末尾添加goo.gl短链接，并且可以在设置选项中自定义样式。如果需要将文章中的链接转换成goo.gl短链接，可以使用如下形式：[wpgoo]http://www.google.com[/wpgoo]。文章保存后该地址将会被转换成goo.gl短链接。
Version: 0.1
Author: 7lemon
Author URI: http://www.7lemon.net
*/

include("lib/GooAPI.class.php");
include("include/goo-manager.php");

if(class_exists("GooManager")){
	$GM = new GooManager();
}

if(!function_exists('add_goo_shortener_options_page')){
	function add_goo_shortener_options_page(){
		global $GM;
		
		if(!isset($GM)){
			return;
		}
		
		if (function_exists('add_options_page')){
			add_options_page('wp-goo-shortener', 'GooShortener设置', 9, basename(__FILE__), array(&$GM, 'adminPage'));
		}
	}
}

function get_goo_short_url($post_id){
	global $GM;
	
	return $GM->get_post_goo_url($post_id);
}

function get_goo_short_link($post_id){
	global $GM;
	
	$short = $GM->get_post_goo_url($post_id);
	$format = $GM->get_format();
	
	$pattern= array('/%url/is', '/%text/is');
	$replacement = array($short, $short);
	$link = preg_replace($pattern, $replacement, $format);
	
	return $link;
}

function the_goo_short_url(){
	$post_id = get_the_ID();
	echo get_goo_short_url($post_id);
}

function the_goo_short_link(){
	$post_id = get_the_ID();
	echo get_goo_short_link($post_id);
}

function addGooLinkToContent($content){
	global $GM;
	if ($GM->auto_show()){
		$content .= the_goo_short_link();
	}
	return $content;
}

if (isset($GM)){
	add_action('wp-goo-shortener/wp-goo-shortener.php' , array (&$GM , 'init' )) ;
	add_action('admin_menu','add_goo_shortener_options_page');
	add_action('save_post', array(&$GM, 'addMeta'));
	add_action('publish_post', array(&$GM, 'convertToGooUrl'));
	add_filter('the_content', 'addGooLinkToContent');
}

?>