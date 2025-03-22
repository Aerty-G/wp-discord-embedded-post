<?php
/**
 * WP Discord Embedded Post
 *
 * @author      Aerty-G
 *
 * Plugin Name: Wp Discord Embedded Post
 * Description: A Discord integration that sends a message on your desired Discord server and channel for every new post published.
 *
 * Version:     0.0.1
 * Author:      Aerty-G
 * Author URI:  https://aerty.my.id
 * Text Domain: wp-discord-embedded-post
 */
 
 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once('includes/class.implements.php');
require_once('includes/class.discord.php');
require_once('includes/class.helper.php');

// More Const Was In "includes/class.implements.php"
define('WEP_IS_DEBUG', false);


class WP_Discord_Embedded_Post implements WEP_Const {
  protected static $_instance = null;
  
  public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function __construct() {
	  
	}
}

WP_Discord_Embedded_Post::instance();