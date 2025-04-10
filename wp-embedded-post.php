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


class WP_Discord_Embedded_Post implements WDEP_Const {
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
	  require_once( 'includes/class.admin.php' );
    require_once( 'includes/class.discord.php' );
    require_once( 'includes/class.helper.php' );
	  $this->option = new stdClass();
	  $this->option->Helper = new WDEP_Helper();
	  $this->option->Admin = new WDEP_Admin();
	  add_action( 'transition_post_status', array( $this, 'PublishPostHook' ), 20, 3 );
	}
	
	
	public function SendToDiscord( array $data ) {
	  $DC = new WDEP_Discord( $data );
	  $response = $DC->Send();
	}
	
	public function PublishPostHook( $new_status, $old_status, $post ) {
	   if ( $new_status === 'publish' && ( $old_status === 'draft' || $old_status === 'pending' || $old_status === 'future' ) ) {
	     $suppres_post = isset( $_POST['wpdep_suppres_post'] ) && $_POST['wpdep_suppres_post'] !== 0;
	     $post_id = $post->ID;
	     $cat = wp_get_post_categories( $post_id );
	     $data = array();
       foreach ( $cat as $c ) {
          $pre = $this->option->Helper->isCatNeedToPost( $c );
          if ( $pre !== false ) {
            $data[] = $pre;
          }
       }
        
       if ( empty($data) ) {
          return;
       }
       
       $final_data = $this->option->Helper->ConstructRawDataCP( [ 'post_id' => $post_id, 'data' => $data ] );
       if (!$final_data) return;
       $this->SendToDiscord($final_data);
	   }
	}
	
}

WP_Discord_Embedded_Post::instance();

function WP_DiscordPost() {
  return WP_Discord_Embedded_Post::instance();
}