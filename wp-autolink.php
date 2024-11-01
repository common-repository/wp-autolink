<?php
/*
Plugin Name: WP-AutoLink
Description: WP-AutoLink Plugin can automatically add links on keywords when publish post. Can also add keyword links all existing contents of your blog. | WP-AutoLink可以在发布文章的时候自动添加上关键词链接，也可以对已经发布的内容增加关键词链接。
Version: 1.0
Author: yjia007
*/


global  $wp_autolink_root,$table_prefix,$t_autolink;
$wp_autolink_root = WP_PLUGIN_URL."/wp-autolink/";
$t_autolink = $table_prefix.'autolink';

load_plugin_textdomain('wp-autolink', WP_PLUGIN_URL.'/wp-autolink/languages/', 'wp-autolink/languages/');


### Load WP-Config File If This File Is Called Directly
if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

### Function: Ratings Administration Menu
add_action('admin_menu', 'wp_autolink_menu');
function wp_autolink_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page('Auto Link','Auto Link', 'administrator', 'wp-autolink/wp-autolink-list.php', '',WP_PLUGIN_URL.'/wp-autolink/images/menu_icon.png');
	}
}


function wp_autolink_install () {

  global $wpdb; $wp_autolink_db_version = '1.0';
  global $t_autolink;
  $old_db_version = get_option('wp_autolink_db_version');
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  
  if(($wpdb->get_var("SHOW TABLES LIKE '$t_autolink'") != $t_autolink)||$wp_autolink_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_autolink . " (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	keyword VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',  
	details VARCHAR(200) NOT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  update_option("wp_autolink_db_version", $wp_autolink_db_version);
}
register_activation_hook( __FILE__,'wp_autolink_install');

function wp_autolink_head() { 
   global  $wp_autolink_root;
   echo '<link rel="stylesheet" type="text/css" href="'.$wp_autolink_root.'css/wp-autolink.css" />';
}
add_action('admin_head', 'wp_autolink_head');



function wp_autolink_content_filter($content) {
  global $wpdb,$t_autolink; 
  $autolinks = $wpdb->get_results('SELECT * FROM '.$t_autolink);
  return wp_autolink_replace($content,$autolinks);
}
add_filter( 'content_save_pre', 'wp_autolink_content_filter' ); 


function wp_autolink_replace($content,$autolinks){
  $ignore_pre = 1;
  global $wp_autolink_replaced;
  $wp_autolink_replaced=false;
  foreach ($autolinks as $autolink ){

    $keyword = $autolink->keyword;
	list($link,$desc,$nofollow,$newwindow,$firstonly,$ignorecase,$WholeWord) = explode("|",$autolink->details);

	if($ignorecase==1){
       if(stripos($content,$keyword)=== false)continue;
	}else{
       if(strpos($content,$keyword)=== false)continue;
	}
    $wp_autolink_replaced=true;
	
	$cleankeyword = stripslashes($keyword);

	if(!$desc){ $desc = $cleankeyword; }
	$desc = addcslashes($desc, '$');
		 			
	$url = "<a href=\"$link\" title=\"$desc\"";

	if ($nofollow) $url .= ' rel="nofollow"';
	if ($newwindow) $url .= ' target="_blank"';

	$url .= ">".addcslashes($cleankeyword, '$')."</a>";
    
	if ($firstonly) $limit = 1; else $limit= -1;
	if ($ignorecase) $case = "i"; else $case="";

	$ex_word = preg_quote($cleankeyword,'\'');


	if($ignore_pre){
	   if( $num_1 = preg_match_all("/<pre.*?>.*?<\/pre>/is", $content, $ignore_pre) )
		 for($i=1;$i<=$num_1;$i++)
		   $content = preg_replace( "/<pre.*?>.*?<\/pre>/is", "%ignore_pre_$i%", $content, 1);
	}

	$content = preg_replace( '|(<img)(.*?)('.$ex_word.')(.*?)(>)|U', '$1$2%&&&&&%$4$5', $content);
	$cleankeyword = preg_quote($cleankeyword,'\'');
    
    if($WholeWord==1){
      $regEx = '\'(?!((<.*?)|(<a.*?)))(\b'. $cleankeyword . '\b)(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;  
	}else{
      $regEx = '\'(?!((<.*?)|(<a.*?)))(' . $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;  
	}
  
				
	$content = preg_replace($regEx,$url,$content,$limit);

	$content = str_replace( '%&&&&&%', stripslashes($ex_word), $content);

	if($ignore_pre){
		for($i=1;$i<=$num_1;$i++){
			$content = str_replace( "%ignore_pre_$i%", $ignore_pre[0][$i-1], $content);
		}
	}
    
  }//end foreach ($links as $keyword => $details){ 
  return $content; 
}

function autoLinkPost($object,$autolinks){
  $content = wp_autolink_replace($object->post_content,$autolinks);
  global $wp_autolink_replaced,$wpdb;
  if($wp_autolink_replaced){
    $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = %s WHERE ID = %d ",$content,$object->ID));
  }
}


?>