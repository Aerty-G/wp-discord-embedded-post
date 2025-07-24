<?php
/**
 * WP Discord Embedded Post
 *
 * @author      Aerty-G
 *
 * Plugin Name: WP Discord Embedded Post
 * Description: A Discord integration that sends a message on your desired Discord server and channel for every new post published.
 *
 * Version:     2.1.1
 * Author:      Aerty-G
 * Author URI:  https://github.com/Aerty-G
 * Plugin URI: https://github.com/Aerty-G/wp-discord-embedded-post
 * Requires PHP: 7.4
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-embedded-post
 */
 
 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}




define( 'WPDEP_IS_DEBUG', false );
define( 'WPDEP_VERSION', '2.1.1' );
define( 'WPDEP_PATH_DIR' , __DIR__ );
define( 'WPDEP_PATH_URL' , plugin_dir_url(__FILE__) );
define( 'WPDEP_PATH_CACHE_DIR' , WP_CONTENT_DIR.'/cache/archangel/discord-embedded');
if ( !file_exists( WPDEP_PATH_CACHE_DIR ) ) {
    mkdir( WPDEP_PATH_CACHE_DIR , 0755 , true );
}

// More Const/Define Was In "includes/class.implements.php"
require_once( WPDEP_PATH_DIR.'/includes/class.implements.php' );


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
      add_filter('plugin_action_links_wp-embedded-post/wp-embedded-post.php', [$this, 'PluginButtons']);
      $this->hooks_init();
      add_action('plugins_loaded', [$this, 'late_init']);
	}
	
	public function DefineAssets() {
    require_once( WPDEP_PATH_DIR.'/includes/class.implements.php' );
    require_once( WPDEP_PATH_DIR.'/includes/class.admin.php' );
    require_once( WPDEP_PATH_DIR.'/includes/class.discord.php' );
    require_once( WPDEP_PATH_DIR.'/includes/class.helper.php' );
    require_once( WPDEP_PATH_DIR.'/includes/class.plugin-update.php' );
    require_once( WPDEP_PATH_DIR.'/includes/class.comment.php' );
	}
	
	public function late_init() {
	  $this->option->Comment = new WPDEP_Comment();
	}
	
	public function PluginButtons($links) {
    $dashboard_url = admin_url('admin.php?page=wp-discord-embedded-post');
    $dashboard_link = '<a href="' . esc_url($dashboard_url) . '">Dashboard</a>';
    $github_link = '<a href="https://github.com/Aerty-G/wp-discord-embedded-post" target="_blank">GitHub</a>';
    array_unshift($links, $dashboard_link, $github_link);
    return $links;
	}
	
	public function hooks_init () {
	  $defaultsetarray = get_option(self::DEFAULT_SET_LIST_OPT, array());
	  if (isset($defaultsetarray['hooks']) && $defaultsetarray['hooks'] === 'hooks_1') {
	    add_action( 'transition_post_status', array( $this, 'PublishPostHook' ), 10, 3 );
	  } if (isset($defaultsetarray['hooks']) && $defaultsetarray['hooks'] === 'hooks_2') {
	    add_action( 'auto-draft_to_publish', array($this, 'Hooks2Handle'), 10, 1);
	    add_action( 'future_to_publish', array($this, 'Hooks2Handle'), 10, 1);
	    add_action( 'draft_to_publish', array($this, 'Hooks2Handle'), 10, 1);
	    add_action( 'pending_to_publish', array($this, 'Hooks2Handle'), 10, 1);
	  } if (isset($defaultsetarray['hooks']) && $defaultsetarray['hooks'] === 'hooks_3') {
	    add_action( 'post_submitbox_misc_actions', [$this, 'render_checkbox_notify'] );
	    add_action( 'save_post', [$this, 'Hooks3Handle'], 10, 3 );
	    add_action( 'future_to_publish', array($this, 'Hooks2Handle'), 10, 1);
	  }
	}
	
	public function get_wpdep_helper() {
	  return $this->option->Helper;
	}
	
	public function render_checkbox_notify() {
	  global $post;

    if ( $post->post_type !== 'post' ) return;

    $has_been_processed = get_post_meta( $post->ID, '_wpdep_notify_discord', true );
    $default_checked = ( $post->post_status === 'auto-draft' && ! $has_been_processed );

    ?>
    <div class="misc-pub-section">
        <label>
            <input type="checkbox" name="_wpdep_notify_discord" value="1" <?php checked( $default_checked ); ?> />
            Send to Discord
        </label>
    </div>
    <?php
	}
	
	public function SendToDiscord( array $data ) {
	  $DC = new WPDEP_Discord( $data );
	  $response = $DC->Send();
	}
	
	public function PublishPostHook( $new_status, $old_status, $post ) {
	   if(($new_status === $old_status) || $new_status !== 'publish') return;
	   if ($new_status === 'publish' && ($old_status === 'draft' || $old_status === 'pending' || $old_status === 'future')) {
	     //$suppres_post = isset( $_POST['wpdep_suppres_post'] ) && $_POST['wpdep_suppres_post'] !== 0;
	     $this->HandleNewPublishPost( $post );
	   }
	   return;
	}
	
	public function Hooks2Handle($ID) {
	  $post = get_post($ID);
	  $this->HandleNewPublishPost( $post );
	}
	
	public function HooksForFuture() {
	  
	}
	
	public function Hooks3Handle($post_id, $post, $update) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( $post->post_status !== 'publish' ) return;
    $post_test = get_post($post_id);
    if ( $post_test->post_status !== 'publish' ) return;
    $is_marked_as_new = isset( $_POST['_wpdep_notify_discord'] ) && $_POST['_wpdep_notify_discord'] === '1';
    if ( $is_marked_as_new ) {
        update_post_meta( $post_id, '_wpdep_notify_discord', 1 );
        $this->Hooks2Handle($post_id);
    }
    
	}
	
	private function HandleNewPublishPost( $post ) {
      $post_id = $post->ID;
      $this->option->Helper->post_id = $post_id;
      $this->option->Helper->post = $post;
      $this->option->Helper->is_filter_comment = false;
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

