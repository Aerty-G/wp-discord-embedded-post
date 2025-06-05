<?php
/* Comment Hooks*/

require_once( __DIR__. '/class.implements.php' );
require_once( __DIR__ . '/class.helper.php');

class WPDEP_Comment implements WPDEP_Const { 
    private $config = null;
    private $option;
    public $comment_on_proceed = [];
    
    public function __construct() {
      	  /* Silence is golden */ 
      	  $this->option = new stdClass();
      	  $this->option->helper = new WPDEP_Helper();
      	  $this->option->message = '';
      	  
          $defaultset = get_option(self::DEFAULT_SET_LIST_OPT, array());
          if ( isset($defaultset['comment_service']) && $defaultset['comment_service'] === 'wpdiscuz' ) {
              add_action('wpdiscuz_after_comment_post', [$this, 'wpdiscuz_handle_core'], 9999, 2);
          } elseif( isset($defaultset['comment_service']) && $defaultset['comment_service'] === 'wp' ) {
              add_action('comment_post', [$this, 'second_core'], 9999, 3);
          }
//           add_action('wp_insert_comment', [$this, 'core'], 9999, 2);
  	}
  	
  	public function core($new_status, $old_status, $comment) {
  	    // status = '' new, status = approved/1 approved, status = spam, status = trash
  	    if (in_array($comment->comment_ID, $this->comment_on_proceed)) return ;
  	    $this->comment_on_proceed[] = $comment->comment_ID;
  	    if ($this->getConnetionType() === 'webhook' && empty($this->getWebhookUrl())) return;
  	    if ($this->getConnetionType() === 'bot' && (empty($this->getBotToken()) || empty($this->getChannelId()))) return;
  	    if ($new_status === 'approved' || $new_status == 1) {
  	      $this->option->comment = $comment;
  	      $this->option->helper->is_filter_comment = true;
  	      $this->option->helper->comment = $this->option->comment;
  	      $this->construct_data();
  	      $this->send();
  	    }
  	}
  	
  	//WPDiscuz 7 $user_id Null If Guest
  	public function wpdiscuz_handle_core($comment, $user_id){
  	    if (in_array($comment->comment_ID, $this->comment_on_proceed)) return ;
  	    $this->comment_on_proceed[] = $comment->comment_ID;
  	    $comment_approved = $comment->comment_approved;
  	    
  	    if ($this->getConnetionType() === 'webhook' && empty($this->getWebhookUrl())) return;
  	    if ($this->getConnetionType() === 'bot' && (empty($this->getBotToken()) || empty($this->getChannelId()))) return;
  	    if ($comment_approved === '1' || $comment_approved === 1) {
  	      $this->option->comment = $comment;
  	      $this->option->helper->is_filter_comment = true;
  	      $this->option->helper->comment = $this->option->comment;
  	      $this->construct_data();
  	      $this->send();
  	    }
  	}
  	
  	public function second_core($comment_ID, $comment_approved, $commentdata) {
  	    $comment = get_comment($comment_ID);
  	    if (in_array($comment->comment_ID, $this->comment_on_proceed)) return ;
  	    $this->comment_on_proceed[] = $comment->comment_ID;
  	    if ($this->getConnetionType() === 'webhook' && empty($this->getWebhookUrl())) return;
  	    if ($this->getConnetionType() === 'bot' && (empty($this->getBotToken()) || empty($this->getChannelId()))) return;
  	    if ($comment_approved === '1' || $comment_approved === 1) {
  	      $this->option->comment = $comment;
  	      $this->option->helper->is_filter_comment = true;
  	      $this->option->helper->comment = $this->option->comment;
  	      $this->construct_data();
  	      $this->send();
  	    }
  	}
  	
  	private function send() {
  	  if ($this->getConnetionType() === 'bot') {
  	    $this->sendViaBot();
  	  } elseif ($this->getConnetionType() === 'webhook') {
  	    $this->sendViaWebhooks();
  	  }
  	}
  	
  	private function getBotToken() {
  	  $this->config = $this->config ?? get_option(self::DEFAULT_SET_LIST_OPT, array());
  	  return !empty($this->config['bot_token']) ? $this->config['bot_token'] : '';
  	}
  	
  	private function getChannelId() {
  	  $this->config = $this->config ?? get_option(self::DEFAULT_SET_LIST_OPT, array());
  	  return !empty($this->config['comment_channel_id']) ? $this->config['comment_channel_id'] : '';
  	}
  	
  	private function getWebhookUrl() {
  	  $this->config = $this->config ?? get_option(self::DEFAULT_SET_LIST_OPT, array());
  	  return !empty($this->config['comment_webhook_url']) ? $this->config['comment_webhook_url'] : '';
  	}
  	
  	private function getConnetionType() {
  	  $this->config = $this->config ?? get_option(self::DEFAULT_SET_LIST_OPT, array());
  	  return $this->config['connection_type'] ?? 'webhook';
  	}
  	
  	private function getEmbedTemplate() {
  	  $embed_options = get_option(self::EMBEDDED_COMMENT_STRUCT_OPT, []);
  	  if (!empty($this->option->comment->comment_parent)) {
  	    return $embed_options['embeded'][1];
  	  } else {
  	    return $embed_options['embeded'][0];
  	  }
  	}
  	
  	private function construct_data() {
  	    //error_log(var_export($this->option->helper->FilteringEmbededPlaceholder($this->getEmbedTemplate())));
        $this->construct_embed($this->option->helper->FilteringEmbededPlaceholder($this->getEmbedTemplate()));
  	}
  	
    private function quick_image_extract($comment_content) {
        $image_urls = [];
        preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $comment_content, $img_matches);
        preg_match_all('/(?:^|\s|>)(https?:\/\/[^\s<>"]+\.(?:jpe?g|png|gif|webp|bmp)(?:\?[^\s<>"]*)?)(?:\s|<|$)/i', $comment_content, $bare_matches);
        preg_match_all('/\[wpd_gallery\s+id=["\']?(\d+)["\']?[^\]]*\]/', $comment_content, $gallery_matches);
        if (!empty($img_matches[1])) $image_urls = array_merge($image_urls, $img_matches[1]);
        if (!empty($bare_matches[1])) $image_urls = array_merge($image_urls, $bare_matches[1]);
        foreach ($gallery_matches[1] as $attachment_id) {
            if ($url = wp_get_attachment_url($attachment_id)) {
                $image_urls[] = $url;
            }
        }
        return array_unique($image_urls);
    }
  	
  	private function construct_embed($embed = null){
  	  $emebed = $embed ?? $this->getEmbedTemplate();
  	  $this->option->component = $emebed['components'];
  	  unset($emebed['components']);
  	  $this->option->embed = $emebed['embeded'];
  	  $this->option->image_feature = $this->quick_image_extract($this->option->comment->comment_content);
  	  $this->option->images_embed = [];
  	  if (!empty($this->option->image_feature)) {
  	    foreach ($this->option->image_feature as $img) {
  	      $this->option->images_embed[] = ['image' => ['url' => $img], 'color' => $this->option->embed['color'], 'timestamp' => $this->option->embed['timestamp'], 'footer' => $this->option->embed['footer'] ];
  	    }
  	  }
  	  $array_image = get_comment_meta( $this->option->comment->comment_ID, 'wmu_attachments', true );
  	  //error_log(print_r($array_image, true));
  	  if (!empty($array_image)) {
  	    if (is_array($array_image)) {
  	      if (isset($array_image['images'])) {
    	      foreach ($array_image['images'] as $aid) {
    	        $this->option->images_embed[] = ['image' => ['url' => wp_get_attachment_url( $aid )], 'color' => $this->option->embed['color'], 'timestamp' => $this->option->embed['timestamp'], 'footer' => $this->option->embed['footer'] ];
    	      }
  	      } else {
  	        foreach ($array_image as $aid) {
    	        $this->option->images_embed[] = ['image' => ['url' => wp_get_attachment_url( $aid )], 'color' => $this->option->embed['color'], 'timestamp' => $this->option->embed['timestamp'], 'footer' => $this->option->embed['footer'] ];
    	      }
  	      }
  	    }
  	    if (is_numeric($array_image)) {
  	      $this->option->images_embed[] = ['image' => ['url' => wp_get_attachment_url( $array_image )], 'color' => $this->option->embed['color'], 'timestamp' => $this->option->embed['timestamp'], 'footer' => $this->option->embed['footer'] ];
  	    }
  	  }
  	  if (count($this->option->images_embed) === 1) {
  	    $this->option->embed['image']['url'] = $this->option->images_embed[0]['image']['url'];
  	    $this->option->images_embed = [];
  	  } if (count($this->option->images_embed) > 1) {
  	    $this->option->embed['image']['url'] = $this->option->images_embed[0]['image']['url'];
  	    unset($this->option->images_embed[0]);
  	  }
  	  
  	  return;
  	}
  	
		public function sendViaWebhooks() {
  	  $url = $this->getWebhookUrl();
  	  $embed = [];
  	  $embed[] = $this->option->embed;
  	  $embed = array_merge($embed, $this->option->images_embed);
  	  $POSTX = ['content' => $this->option->message,
                'embeds' => $embed,
               ];
      $headers = ['Content-Type: application/json'];
      $this->curl($url, $POSTX, $headers);
  	}
  	
  	public function sendViaBot() {
  	  $channelId = $this->getChannelId();
  	  $url = "https://discord.com/api/v9/channels/{$channelId}/messages";
  	  $embed = [];
  	  $embed[] = $this->option->embed;
  	  $embed = array_merge($embed, $this->option->images_embed);
  	  $POSTX = [
          'content' => $this->option->message,
          'embeds' => $embed,
          
      ];
      if(!empty($this->option->component)) {
      $POSTX['components'] = [
              [
                  'type' => 1,
                  'components' => $this->option->component
              ]
              ];
      }
      $headers = [
        'Content-Type: application/json',
        'Authorization: Bot ' . $this->getBotToken(),
      ];
      //file_put_contents(__DIR__.'/postx.txt', var_export($POSTX, true));
      
      $this->curl($url, $POSTX, $headers);
  	}
  	
  	private function curl($url, $POSTX, $headers) {
  	  $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($POSTX, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
      $response = curl_exec($ch);
      //file_put_contents(__DIR__.'/res.txt', var_export($response, true));
      $this->option->response[] = json_decode($response, true);
      curl_close($ch);
  	}
  
}