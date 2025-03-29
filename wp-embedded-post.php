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

require_once( 'includes/class.implements.php' );

// More Const Was In "includes/class.implements.php"
define( 'WEP_IS_DEBUG', false );


class WP_Discord_Embedded_Post implements WEP_Const {
  protected static $_instance = null;
  public $option;
  
  
  public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function __construct() {
	  /* Silence is golden */ 
	  require_once( 'includes/class.implements.php' );
	  require_once( 'includes/class.database.php' );
	  require_once( 'includes/class.admin.php' );
    require_once( 'includes/class.discord.php' );
    require_once( 'includes/class.helper.php' );
	  $this->option = new stdClass();
	  $this->option->Database = new WDEP_Database();
	  $this->option->Helper = new WDEP_Helper();
	  $this->option->Admin = new WDEP_Admin();
	}
	
	
	public function SendToDiscord( array $data ) {
	  $DC = new WDEP_Discord( $data );
	  $DC->Send();
	}
	
}

WP_Discord_Embedded_Post::instance();

function WP_DiscordPost() {
  return WP_Discord_Embedded_Post::instance();
}