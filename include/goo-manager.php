<?php

if (!class_exists('GooManager')){
	class GooManager{
	
		private $goo = NULL;
		private $optionsName = 'GooShortenerOptions';
	
		function init(){
			if(class_exists('GooAPI')){
				$this->goo = new GooAPI();
			} else {
				die('GooAPI not found');
			}
			
			$this->getOptions();
		}
		
		function shorten($url){
			if(!$goo){
				$this->init();
			}
			return $this->goo->shorten($url);
		}
		
		function expand($url){
			if(!$goo){
				$this->init();
			}
			return $this->goo->expand($url);
		}
		
		function getOptions(){
			$gooDefaultOptions = array(
				'autoShow' => true,
				'format' => stripslashes('<span class="goo-short"><a href="%url">%text</a></span>'),
			);
			$options = get_option($this->optionsName);
			if (!empty($options)){
				foreach($options as $key => $option){
					$gooDefaultOptions[$key] = $option;
				}
			}
			
			update_option($this->optionsName, $gooDefaultOptions);
			return $gooDefaultOptions;
		}
		
		function get_format(){
			$options = $this->getOptions();
			return $options['format'];
		}
		
		function auto_show(){
			$options = $this->getOptions();
			return $options['autoShow'];
		}
		
		function adminPage(){
			$options = $this->getOptions();
			if (isset($_POST['update-options'])){
				if (isset($_POST['autoShow'])){
					$options['autoShow'] = true;
				} else {
					$options['autoShow'] = false;
				}
				
				if (isset($_POST['format'])){
					$options['format'] = stripslashes($_POST['format']);
				}
				
				update_option($this->optionsName, $options);
			}
			?>
			<div class="wrap">
				<form method="POST" action="<?php echo $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI']; ?>">
					<h2>WP Goo Shortener 设置</h2>
					<div class="config_box">
                        <p><label><input type="checkbox" id="autoShow" name="autoShow" value="true"  <?php if($options['autoShow']) echo 'checked="checked"';  ?> />&nbsp;&nbsp;选择是否在文章末尾显示Goo短链接</label></p>
						<p>文章末尾的 goo.gl 短网址的格式：<br/><label><textarea type="text" id="format" name="format"  cols="60" rows="4"/><?php echo $options['format'];?></textarea></label></p>
						<p class="submit">
							<input type="submit" name="update-options" value="保存设置" />
						</p>
					</div>
				</form>
			</div>
			<?php
		}
		
		function get_post_goo_url($object_id = 0, $object = null, $new = false){
			if (!isset($object) || $object == null){
				$object = get_post($object_id);
			}
			
			if(!$new && $goo_url = get_post_meta($object->ID, 'goo_short_url', true)){
				return $goo_url;
			}
			
			$url = get_permalink($object->ID);
			$short = $this->shorten($url);
			update_post_meta($object->ID, 'goo_short_url', $short);
			if ($short == 'FALSE')
				return $url;
				
			return $short;
		}
		
		function addMeta( $object_id = 0, $object = null){
			if (!isset($object) || $object == null){
				if ( wp_is_post_revision( $object_id ) ){
					$object_id = wp_is_post_revision( $object_id );
				}
				$object = get_post($object_id);
			}
			
			$short = $this->get_post_goo_url($object_id, $object, true);
			update_post_meta($object->ID, 'goo_short_url', $short);
		}
		
		function convertToGooUrl($object_id = 0, $object = null){
			global $wpdb;
		
			if (!isset($object) || $object == null){
				if ( wp_is_post_revision( $object_id ) ){
					$object_id = wp_is_post_revision( $object_id );
				}
				$object = get_post($object_id);
			}
			
			$content = $object->post_content;
			
			preg_match_all('/\[wpgoo\]([^[]*)\[\/wpgoo\]/is', $content, $results, PREG_SET_ORDER);
			$short_links = array();
			$long_links = array();
			foreach($results as $row){
				$goo_url = $this->shorten($row[1]);
				if ($goo_url != 'FALSE' ){
					$short_links[] = '<a href="'.$goo_url.'">'.$goo_url.'</a>';
				} else {
					$short_links[] = $row[1];
				}
				$long_links[] = '[wpgoo]'.$row[1].'[/wpgoo]';
			}
			
			$content = str_ireplace($long_links, $short_links, $content);
			
			$wpdb->update($wpdb->posts, array('post_content' => $content), array('ID' => $object->ID), array('%s'), array('%d'));
		}
	}
}

?>