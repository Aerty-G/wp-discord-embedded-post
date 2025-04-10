<?php
/**
 * Helper For Filtering Nad Retrieve Data
 */

require_once('class.implements.php');

class WDEP_Helper implements WDEP_Const {
  
  public function __construct() {
	  /* Silence is golden */ 
	}
	
	public function isCatNeedToPost( $cat ) {
  	  $cat_option = get_option(self::CATEGORY_SELECTED_SET_OPT, array());
  	  $out = array();
  	  foreach ( $cat_option as $cp ) {
  	    $cat_ids = $cp['cat_ids'];
  	    foreach ( $cat_ids as $ci ) {
  	      if ( $ci === $cat ) {
  	        $out[] = array(
  	          'style_id' => $cp['selected_embedded_style'],
  	          'main_message' => $cp['main_message'],
  	          'channel_id' => $cp['channel_id'],
  	          'bot_token' => $cp['bot_token'],
  	          'webhook_url' => $cp['webhook_url']
  	        );
  	      }
  	    }
  	  }
  	  if (!empty($out)) {
  	    return $out;
  	  } else {
  	    return false;
  	  }
	}
	
	public function ConstructRawDataCP( $data ) {
	  $f = array();
	  $defaultsetarray = get_option(self::DEFAULT_SET_LIST_OPT, array());
    $connection_type = $defaultsetarray['connection_type'] ?? 'webhook';
    $webhook_url = $defaultsetarray['webhook_url'] ?? '';
    $bot_token = $defaultsetarray['bot_token'] ?? '';
    $channel_id = $defaultsetarray['channel_id'] ?? '';
    $f = [
      'connection_type' => $connection_type,
      'webhook_url' => $webhook_url,
      'bot_token' => $bot_token,
      'channel_id' => $channel_id,
      'options' => []
      ];
	  global $post;
	  $post_id = $post->ID;
	  $data = $data['data'];
	  foreach ( $data as $dat ) :
	    foreach ( $dat as $dt ) :
	      if( $dt['style_id'] !== '' ) :
	        $embeded = $this->RetrieveEmbedStyle($dt['style_id']);
	        if (!$embeded) continue;
	        $embeded = $this->FilteringEmbededPlaceholder($embeded);
	        
	        $f['options'][] = [
            'style_id' => $dt['style_id'],
	          'main_message' => $this->FilterVar($dt['main_message']),
	          'channel_id' => $dt['channel_id'],
	          'bot_token' => $dt['bot_token'],
	          'webhook_url' => $dt['webhook_url'],
	          'embeded' => $embeded
	          ];
	      endif;
	    endforeach;
	  endforeach;
	  if ( empty( $f['options'] ) || count($f['options']) === 0 ) return false;
	  return $f;
	}
	
	public function FilteringEmbededPlaceholder( $embed ) {
	  $final = [
	      'embeded' => [
            'author' => [
                'name' => $this->FilterVar($embed['author']['name']),
                'url' => $embed['author']['url']
            ],
            'title' => $this->FilterVar($embed['title']),
            'description' => $this->FilterVar($embed['description']),
            'fields' => [],
            'image' => ['url' => $embed['image']['url'] ],
            'color' => empty($embed['color']) ? null : $embed['color'],
            'timestamp' => !empty($embed['timestamp']) ? $embed['timestamp'] : $this->getTimeStamp(),
            'footer' => ['text' => $this->FilterVar($embed['footer']['text']) ],
        ],
        'components' => []
    ];
    
    foreach ($embed['fields'] as $field) :
      if(!isset($field['name']) || empty($field['name']) && empty($field['value'])) continue;
      $final['embeded']['fields'][] = [
          'name' => $this->FilterVar($field['name']) ,
          'value' => $this->FilterVar($field['value']) ,
          'inline' => $field['inline'] 
      ]; 
    endforeach;
    
    foreach ($embed['components'] as $component) :
      if (isset($component['label'])) { 
        if (empty($component['label']) || empty($component['url'])) continue;
          $final['components'][] = [
              'type' => 2,
              'label' => $this->FilterVar($component['label']),
              'url' => $component['url'],
              'emoji' => [
                  'id' => $component['emoji']['id'] ,
                  'name' => $component['emoji']['name'] ,
                  'animated' => $component['emoji']['animated']
              ]
          ];
      }
    endforeach;
    return $final;
	}
	
	private function getTimeStamp() {
	  global $post;
	  return get_post_time('Y-m-d\TH:i:s.v\Z', true, $post->ID);
	}
	
	public function RetrieveEmbedStyle( $id ) {
	  $styles = get_option( self::EMBEDDED_STRUCT_LIST_OPT, array() );
	  if ( empty( $styles ) ) return false;
	  return isset( $styles['embeded'][$id] ) ? $styles['embeded'][$id] : false;
	}
	
	public function FilterVar( $string, $id = '' ) {
	  if (!$id || empty($id)) {
	    global $post;
	    $id = $post->ID;
	  }
	  $vars = get_option( self::EMBEDDED_VAR_LIST_OPT, array() );
	  // if( empty( $vars ) ) return $string;
	  if (preg_match_all('/\$\{([^}]+)\}\$/', $string, $matches) && $matches[0] > 0) :
  	  foreach ($matches[1] as $t ) :
    	  foreach ( $vars as $var ) :
  	      if( isset($var['template'] ) && $var['template'] === $t ) :
            $vv = $this->get_formatted_value($var);
    	      $string = str_replace( '${'.$var['template'].'}$', ( empty( trim( $vv ) ) ? '${'.$var['template'].'}$' : $vv ), $string );
    	      break;
    	    endif;
    	  endforeach;
    	  $string = $this->FilterDefaultVar( $string, $t );
    	endforeach;
	  endif;
	  return $string;
	}
	
	public function FilterDefaultVar( $string, $template ) {
	  global $post;
	  if ($post->ID === '') return $string;
	  if (strpos(trim($template), 'get_term_list =>') === 0) {
      preg_match_all('/\[([^\]]*)\]/', $template, $matches);
      $arg = $matches[1];
      if (count($arg) >= 4) :
        if (strpos(trim($arg[0]), ',') !== false) return $string;
        $term = get_the_term_list($post->ID, $arg[0], $arg[1], $arg[2], $arg[3]);
        if(isset($arg[4]) && $arg[4] === 1 ) {
          $term = strip_tags($term);
        }
        if (empty($term) || $term === '') $term = 'Error Term Not Found Or Empty';
        $string = str_replace('${'.$template.'}$', $term, $string);
      endif;
      return $string;
    } elseif (strpos(trim($template), 'get_post_meta =>') === 0) {
      preg_match_all('/\[([^\]]*)\]/', $template, $matches);
      $arg = $matches[1];
      if (count($arg) === 3) :
        if (strpos(trim($arg[0]), ',') !== false) return $string;
        if (strpos(trim($arg[2]), ',') !== false) return $string;
        $meta = $this->get_direct_formatted_value($arg);
        if (empty($meta) || $meta === '') $meta = '${'.$template.'}$';
        $string = str_replace('${'.$template.'}$', $meta, $string);
      endif;
      return $string;
    } elseif (strpos(trim($template), 'get_post_info =>') === 0) {
      preg_match_all('/\[([^\]]*)\]/', $template, $matches);
      $arg = $matches[1];
      if (count($arg) === 2) :
        $post_info = $this->get_direct_post_info_value($arg);
        if (empty($post_info) || $post_info === '') $post_info = '${'.$template.'}$';
        $string = str_replace('${'.$template.'}$', $post_info, $string);
      endif;
      return $string;
    } else {
  	  switch ($template) :
  	    case 'author' :
  	      $string = str_replace('${'.$template.'}$', get_the_author_meta('display_name', $post->post_author), $string);
  	      break;
  	    case 'timestamp' :
  	      $string = str_replace('${'.$template.'}$', get_post_time('Y-m-d\TH:i:s.v\Z', true, $post->ID), $string);
  	      break;
  	    case 'permalink' :
  	      $string = str_replace('${'.$template.'}$', get_permalink($post->ID), $string);
  	      break;
  	    case 'thumbnail_url' :
  	      $string = str_replace('${'.$template.'}$', get_the_post_thumbnail_url($post->ID), $string);
  	      break;
  	    case 'post_title' :
  	      $string = str_replace('${'.$template.'}$', $post->post_title, $string);
  	      break;
  	    case 'post_type' :
	        $string = str_replace('${'.$template.'}$', $post->post_type, $string);
	        break;
	      case 'post_status' :
	        $string = str_replace('${'.$template.'}$', $post->post_status, $string);
	        break;
	      case 'post_name' :
	        $string = str_replace('${'.$template.'}$', $post->post_name, $string);
	        break;
	      case 'default_tag' :
	        $defaultsetarray = get_option(self::DEFAULT_SET_LIST_OPT, array());
          $default_tag = $defaultsetarray['default_tag'] ?? '';
	        $string = str_replace('${'.$template.'}$', $default_tag, $string);
	        break;
	      case 'discord_timestamp' :
	        $timestamp = '<t:'.$this->getTimeStamp().':R>';
	        $string = str_replace('${'.$template.'}$', $timestamp, $string);
	        break;
  	  endswitch;
  	  return $string;
    }
	}
	
	public function get_direct_post_info_value($arg) {
	  $post_id = $arg[0];
	  $info = $arg[1];
	  $value = '';
	  if (strpos(trim($post_id), ',') !== false) return $value;
	  if (strpos(trim($info), ',') !== false) return $value;
	  if (!$post_id || empty($post_id)) {
	    global $post;
	    switch ($info) {
	      case 'ID' :
	        $value = $post->ID;
	        break;
	      case 'post_author' :
	        $value = $post->post_author;
	        break;
	      case 'post_date' :
	        $value =$post->post_date;
	        break;
	      case 'post_content' :
	        $value = $post->post_content;
	        break;
	      case 'post_title' :
	        $value = $post->post_title;
	        break;
	      case 'post_excerpt' :
	        $value = $post->post_excerpt;
	        break;
	      case 'post_status' :
	        $value = $post->post_status;
	        break;
	      case 'post_name' :
	        $value = $post->post_name;
	        break;
	      case 'post_type' :
	        $value = $post->post_type;
	        break;
	      case 'post_category' :
	        $value = $post->post_category;
	        break;
	    }
	    return $value;
	  } elseif(is_numeric($post_id)) {
	    $post = get_post($post_id);
	    switch ($info) {
	      case 'ID' :
	        $value = $post->ID;
	        break;
	      case 'post_author' :
	        $value = $post->post_author;
	        break;
	      case 'post_date' :
	        $value =$post->post_date;
	        break;
	      case 'post_content' :
	        $value = $post->post_content;
	        break;
	      case 'post_title' :
	        $value = $post->post_title;
	        break;
	      case 'post_excerpt' :
	        $value = $post->post_excerpt;
	        break;
	      case 'post_status' :
	        $value = $post->post_status;
	        break;
	      case 'post_name' :
	        $value = $post->post_name;
	        break;
	      case 'post_type' :
	        $value = $post->post_type;
	        break;
	      case 'post_category' :
	        $value = $post->post_category;
	        break;
	    }
	    return $value;
	  } else {
	    global $post;
	    $post_id = get_post_meta($post->ID, $post_id, true);
	    if (!is_numeric($post_id) || empty($post_id)) return $value;
	    $post = get_post($post_id);
	    switch ($info) {
	      case 'ID' :
	        $value = $post->ID;
	        break;
	      case 'post_author' :
	        $value = $post->post_author;
	        break;
	      case 'post_date' :
	        $value =$post->post_date;
	        break;
	      case 'post_content' :
	        $value = $post->post_content;
	        break;
	      case 'post_title' :
	        $value = $post->post_title;
	        break;
	      case 'post_excerpt' :
	        $value = $post->post_excerpt;
	        break;
	      case 'post_status' :
	        $value = $post->post_status;
	        break;
	      case 'post_name' :
	        $value = $post->post_name;
	        break;
	      case 'post_type' :
	        $value = $post->post_type;
	        break;
	      case 'post_category' :
	        $value = $post->post_category;
	        break;
	    }
	    return $value;
	  }
	}
	
	public function get_direct_formatted_value($arg) {
	  return $this->get_formatted_value(['mode' => $arg[0], 'keys' => explode(',',$arg[1]), 'separator' => $arg[2]]);
	}
	
	private function get_formatted_value($option) {
      global $post;
      
      if (!isset($post->ID)) {
          return '';
      }

      $value = '';
      $post_id = $post->ID;

      switch ($option['mode']) {
          case 'single':
              if (!empty($option['keys'][0])) {
                  $value = get_post_meta(trim($post_id, $option['keys'][0]), true);
              }
              break;
              
          case 'combine':
              $values = [];
              foreach ($option['keys'] as $key) {
                  $meta_value = get_post_meta($post_id, trim($key), true);
                  if (!empty($meta_value)) {
                      $values[] = $meta_value;
                  }
              }
              $value = implode($option['separator'] ?? ', ', $values);
              break;
              
          case 'connect':
              if (count($option['keys']) >= 2) {
                  $first_value = get_post_meta($post_id, trim($option['keys'][0]), true);
                  if (is_numeric($first_value)) {
                      $value = get_post_meta($first_value, trim($option['keys'][1]), true);
                  }
              }
              break;
      }

      return $value;
  }
	
  public function Post( $url, array $data = [], array $header = ['Content-Type' => 'application/json'] ) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($ch, CURLOPT_HTTPHEADER, json_encode($header));
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_TIMEOUT, 240);
      $pler = curl_exec($ch);
      curl_close($ch);
      return $pler;
  }
  
  public function Get( $url, array $header = ['Content-Type' => '*/*'] ) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, json_encode($header));
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_TIMEOUT, 240);
      $pler = curl_exec($ch);
      curl_close($ch);
      return $pler;
  }
  
  
}