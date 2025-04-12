<?php
/**
 * WP Discord Embedded Post
 *
 * @author      Aerty-G
 *
 * Plugin Name: Wp Discord Embedded Post
 * Description: A Discord integration that sends a message on your desired Discord server and channel for every new post published.
 *
 * Version:     1.0.3
 * Author:      Aerty-G
 * Author URI:  https://github.com/Aerty-G
 * Plugin URI: https://github.com/Aerty-G/wp-discord-embedded-post
 * Requires PHP: 7.4
 */
 
 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( 'includes/class.implements.php' );

// More Const/Define Was In "includes/class.implements.php"
define( 'WPDEP_IS_DEBUG', false );
define( 'WPDEP_VERSION', '1.0.3' );

class WP_Discord_Embedded_Post implements WPDEP_Const {
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
	  $this->DefineAssets();
	  $this->option = new stdClass();
	  $this->option->Helper = new WPDEP_Helper();
	  $this->option->Admin = new WPDEP_Admin();
	  $this->option->Updater = new WPDEP_Updater(WPDEP_VERSION);
	  add_action( 'transition_post_status', array( $this, 'PublishPostHook' ), 20, 3 );
	}
	
	public function DefineAssets() {
	  require_once( 'includes/class.implements.php' );
	  require_once( 'includes/class.admin.php' );
    require_once( 'includes/class.discord.php' );
    require_once( 'includes/class.helper.php' );
    require_once( 'includes/class.plugin-update.php' );
	}
	
	public function get_wpdep_helper() {
	  return $this->option->Helper;
	}
	
	public function SendToDiscord( array $data ) {
	  $DC = new WDEP_Discord( $data );
	  $response = $DC->Send();
	}
	
	public function PublishPostHook( $new_status, $old_status, $post ) {
	   if ( $new_status === 'publish' && ( $old_status === 'draft' || $old_status === 'pending' || $old_status === 'future' ) ) {
	     $suppres_post = isset( $_POST['wpdep_suppres_post'] ) && $_POST['wpdep_suppres_post'] !== 0;
	     $this->HandleNewPublishPost( $post );
	   }
	   if ( $new_status === 'publish' && $old_status === 'publish' ) {
	     $suppres_post = isset( $_POST['wpdep_suppres_post'] ) && $_POST['wpdep_suppres_post'] !== 0;
	     $this->HandleUpdatePost( $post );
	   }
	   return;
	}
	
	private function HandleNewPublishPost( $post ) {
      $post_id = $post->ID;
      $this->option->Helper->post_id = $post_id;
      $this->option->Helper->post = $post;
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
	
	private function HandleUpdatePost( $post ) {
	    
	}
}

WP_Discord_Embedded_Post::instance();
function WP_DiscordPost() {
  return WP_Discord_Embedded_Post::instance();
}