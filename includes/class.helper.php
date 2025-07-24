<?php
/**
 * Helper For Filtering And Retrieve Data
 */

require_once('class.implements.php');

class WPDEP_Helper implements WPDEP_Const {
  public $post_id = null;
  public $post = null;
  public $is_filter_comment = false;
  public $comment = null;
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
	  $final = array();
	  if ($this->post_id === null && $post === null) return $final;
	  $defaultsetarray = get_option(self::DEFAULT_SET_LIST_OPT, array());
    $connection_type = $defaultsetarray['connection_type'] ?? 'webhook';
    $webhook_url = $defaultsetarray['webhook_url'] ?? '';
    $bot_token = $defaultsetarray['bot_token'] ?? '';
    $channel_id = $defaultsetarray['channel_id'] ?? '';
    $final = [
      'connection_type' => $connection_type,
      'webhook_url' => $webhook_url,
      'bot_token' => $bot_token,
      'channel_id' => $channel_id,
      'options' => []
      ];
	  $post = $this->post;
	  $post_id = $post->ID;
	  $data = $data['data'];
	  foreach ( $data as $dat ) :
	    foreach ( $dat as $dt ) :
	      if( $dt['style_id'] !== '' ) :
	        $embeded = $this->RetrieveEmbedStyle($dt['style_id']);
	        if (!$embeded) continue;
	        $embeded = $this->FilteringEmbededPlaceholder($embeded);
	        
	        $final['options'][] = [
            'style_id' => $dt['style_id'],
	          'main_message' => $this->FilterVar($dt['main_message'], false),
	          'channel_id' => $dt['channel_id'],
	          'bot_token' => $dt['bot_token'],
	          'webhook_url' => $dt['webhook_url'],
	          'embeded' => $embeded
	          ];
	      endif;
	    endforeach;
	  endforeach;
	  if ( empty( $final['options'] ) || count($final['options']) === 0 ) return false;
	  return $final;
	}
	
  function clean_url($url) {
      return str_replace('http://', 'https://', $url);
  }
	
	public function FilteringEmbededPlaceholder( $embed ) {
	  if (!$this->is_filter_comment) {
	    if ($this->post_id === null && $post === null) return $embed;
	  }
	  
	  $featured_image = get_post_meta($this->post_id, '_wpdep_featured_image', true);
	  
	  $final = [
	      'embeded' => [
            'author' => [
                'name' => $this->FilterVar($embed['author']['name']),
                'url' => $this->FilterVar($embed['author']['url'])
            ],
            'title' => $this->FilterVar($embed['title']),
            'description' => $this->FilterVar($embed['description']),
            'fields' => [],
            'image' => ['url' => trim($featured_image) ?: $this->clean_url($this->FilterVar($embed['image']['url'])) ],
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
            $filteredLabel = $this->FilterVar($component['label']);
            if (empty($filteredLabel)) {
                $filteredLabel = $component['label'];
            }
            
            $comp = [
                'type' => 2,
                'style' => 5,
                'label' => $filteredLabel,
                'url' => $this->FilterVar($component['url']),
            ];
            
            if (isset($component['emoji']) && !empty($component['emoji']['id'])) {
                $comp['emoji'] = [
                    'id' => $component['emoji']['id'],
                    'name' => $component['emoji']['name'],
                    'animated' => $component['emoji']['animated']
                ];
            }
            $final['components'][] = $comp;
        }
    endforeach;
    return $final;
	}
	
	private function getTimeStamp() {
	  if (!$this->is_filter_comment) {
	    if ($this->post_id === null && $post === null) return false;
	    $post = $this->post;
	    return get_post_time('Y-m-d\TH:i:s.v\Z', true, $post->ID);
	  }
	  $dt = new DateTime( $this->comment->comment_date, wp_timezone() );
	  $dt->setTimezone( new DateTimeZone('UTC') );
	  return $dt->format('Y-m-d\TH:i:s.v\Z');
	  
	}
	
	private function RetrieveEmbedStyle( $id ) {
	  $styles = get_option( self::EMBEDDED_STRUCT_LIST_OPT, array() );
	  if ( empty( $styles ) ) return false;
	  return isset( $styles['embeded'][$id] ) ? $styles['embeded'][$id] : false;
	}
	
	public function FilterVar( $string, $markdown = true) {
	  if (!$this->is_filter_comment) {
	    if ($this->post_id === null && $post === null) return $string;
	  }
	  $original = $string;
	  try {
  	  if ( !$this->is_filter_comment ) {
  	    $post = $this->post;
  	    $id = $post->ID;
  	  }
  	  $vars = get_option( self::EMBEDDED_VAR_LIST_OPT, array() );
  	  // if( empty( $vars ) ) return $string;
  	  if (preg_match_all('/\$\{([^}]+)\}\$/', $string, $matches) && $matches[0] > 0) :
    	  foreach ($matches[1] as $t ) :
    	    $string = $this->FilterDefaultVar( $string, $t );
      	  foreach ( $vars as $var ) :
    	      if( isset($var['template'] ) && $var['template'] === $t ) :
              $vv = $this->get_formatted_value($var);
      	      $string = str_replace( '${'.$var['template'].'}$', ( empty( trim( $vv ) ) ? '${'.$var['template'].'}$' : $vv ), $string );
      	    endif;
      	  endforeach;
      	endforeach;
  	  endif;
  	  if ($markdown) {
  	    $string = $this->discord_content_filter($string);
  	  }
  	  return $string;
	  } catch (Exception $e) {
       error_log('FilterVar error: ' . $e->getMessage());
       return $original; 
   }
	}
	
	private function FilterDefaultVar( $string, $template ) {
	  
	  if (!$this->is_filter_comment) {
	    if ($this->post_id === null && $post === null) return $string;
	  }
	  if ($this->is_filter_comment) {
      return $this->CommentInfo( $string, $template);
    }
	  $post = $this->post;
	  if ($post->ID === '') return $string;
	  if (strpos(trim($template), 'get_term_list =>') === 0) {
      preg_match_all('/\[([^\]]*)\]/', $template, $matches);
      $arg = $matches[1];
      if (count($arg) >= 5) :
        if (strpos(trim($arg[0]), ',') !== false) return $string;
        if (strpos(trim($arg[1]), ',') !== false) return $string;
        $term = $this->get_direct_term_list($arg);
        if(isset($arg[4]) && $arg[4] === 1 ) {
          $term = strip_tags($term);
        }
        if (empty($term) || $term === '') $term = 'Error Term Not Found Or Empty';
        $string = str_replace('${'.$template.'}$', $term, $string);
      endif;
      return $string;
    } if (strpos(trim($template), 'get_post_meta =>') === 0) {
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
    } if (strpos(trim($template), 'get_post_info =>') === 0) {
      preg_match_all('/\[([^\]]*)\]/', $template, $matches);
      $arg = $matches[1];
      if (count($arg) === 2) :
        $post_info = $this->get_direct_post_info_value($arg);
        if (empty($post_info) || $post_info === '') $post_info = '${'.$template.'}$';
        $string = str_replace('${'.$template.'}$', $post_info, $string);
      endif;
      return $string;
    } if (strpos(trim($template), 'post_content:') === 0) {
      $value = $this->get_post_content_dyn( $template, $post );
      if (empty($post_info) || $post_info === '') $post_info = '${'.$template.'}$';
      $string = str_replace('${'.$template.'}$', $post_info, $string);
      return $string;
    } if (strpos(trim($template), 'default_message =>') === 0) {
      preg_match_all('/\[([^\]]*)\]/', $template, $matches);
      if (empty($matches)) return $string;
      $defaultsetarray = get_option(self::DEFAULT_SET_LIST_OPT, array());
      $default_message = $defaultsetarray['default_message'] ?? '';
      foreach ($matches[1] as $index => $msg) {
        if (strpos(trim($msg), 'extract_') === 0) {
          $msg = str_replace('extract_', '', $msg);
          $msg = $this->Default_Var_Extra_Info('${'.$msg.'}$',$msg);
        }
        $default_message = str_replace('%var_'.$index.'%', $msg, $default_message);
      }
      $string = str_replace('${'.$template.'}$', $default_message, $string);
      return $string;
    } if (strpos(trim($template), 'custom_description =>') === 0) {
      preg_match_all('/\[([^\]]*)\]/', $template, $matches);
      $meta_description = get_post_meta($post->ID, '_wpdep_custom_description', true); 
      if (!empty($meta_description)) {
        $string = str_replace('${'.$template.'}$', $this->FilterVar($meta_description), $string);
      } else {
        $string = str_replace('${'.$template.'}$', $matches[1][0], $string);
      }
      return $string;
    } if (strpos(trim($template), 'custom_featured_image_or_custom_value =>') === 0) {
      preg_match_all('/\[([^\]]*)\]/', $template, $matches);
      $featured_image = get_post_meta($post->ID, '_wpdep_featured_image', true);
      if (!empty($featured_image) && filter_var($featured_image, FILTER_VALIDATE_URL)) {
        $string = str_replace('${'.$template.'}$', trim($featured_image), $string);
      } else {
        if (!empty($matches[0]) && filter_var($matches[1][0], FILTER_VALIDATE_URL)) {
          $string = str_replace('${'.$template.'}$', trim($matches[0][1]), $string);
        } if (!empty($matches[0]) && is_string($matches[1][0])){
          $meta = get_post_meta($post->ID, $matches[1][0], true);
          if (!empty($meta) && filter_var($meta, FILTER_VALIDATE_URL)) {
            $string = str_replace('${'.$template.'}$', trim($meta), $string);
          } if (!empty($meta) && is_numeric($meta)){
            $thumb_id = get_post_thumbnail_id( $meta );
            $url = wp_get_attachment_image_url( $thumb_id, 'full' );
            $string = str_replace('${'.$template.'}$', !empty($url) ? $url : get_the_post_thumbnail_url($meta, 'full'), $string);
          }
        } if (!empty($matches[0]) && is_numeric($matches[1][0])){
          $thumb_id = get_post_thumbnail_id( $matches[1][0] );
          $url = wp_get_attachment_image_url( $thumb_id, 'full' );
          $string = str_replace('${'.$template.'}$', !empty($url) ? $url : get_the_post_thumbnail_url($matches[1][0], 'full'), $string);
        } if (!empty($matches[0]) && empty($matches[1][0])) {
          $thumb_id = get_post_thumbnail_id( $post->ID );
          $url = wp_get_attachment_image_url( $thumb_id, 'full' );
          $string = str_replace('${'.$template.'}$', !empty($url) ? $url : get_the_post_thumbnail_url($post->ID, 'full'), $string);
        }
      } 
      return $string;
    }
    
    return $ths->Default_Var_Extra_Info($string, $template);
    
  	  
	}
	
	private function Default_Var_Extra_Info($string, $template) {
  	  if (!$this->is_filter_comment) {
  	    if ($this->post_id === null && $post === null) return $string;
  	  }
  	  if ($this->is_filter_comment) {
        return $this->CommentInfo( $string, $template);
      }
  	  $post = $this->post;
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
          $thumb_id = get_post_thumbnail_id( $post->ID );
          $url = wp_get_attachment_image_url( $thumb_id, 'full' );
  	      $string = str_replace('${'.$template.'}$', !empty($url) ? $url : get_the_post_thumbnail_url($post->ID, 'full'), $string);
  	      break;
  	    case 'post_title' :
  	      $string = str_replace('${'.$template.'}$', $post->post_title, $string);
  	      break;
  	    case 'post_type' :
	        $string = str_replace('${'.$template.'}$', $post->post_type, $string);
	        break;
	      case 'post_date' :
	        $string = str_replace('${'.$template.'}$', $post->post_date, $string);
	      case 'post_status' :
	        $string = str_replace('${'.$template.'}$', $post->post_status, $string);
	        break;
	      case 'post_name' :
	        $string = str_replace('${'.$template.'}$', $post->post_name, $string);
	        break;
	      case 'post_content' :
	        $string = str_replace('${'.$template.'}$', $post->post_content, $string);
	        break;
	      case 'post_category' :
  	      $string = str_replace('${'.$template.'}$', $post->post_category, $string);
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
	      case 'thumbnail_url_or_featured_image' :
	        $thumb_id = get_post_thumbnail_id( $post->ID );
          $url = wp_get_attachment_image_url( $thumb_id, 'full' );
          $featured_image = get_post_meta($post->ID, '_wpdep_featured_image', true);
          if (!empty($featured_image) && filter_var($featured_image, FILTER_VALIDATE_URL)) {
            $string = str_replace('${'.$template.'}$', trim($featured_image), $string);
          } else {
  	        $string = str_replace('${'.$template.'}$', !empty($url) ? $url : get_the_post_thumbnail_url($post->ID, 'full'), $string);
          }
	        break;
  	  endswitch;
  	  return $string;
    
	}
	
	private function CommentInfo($string, $template) {
	    if ($this->comment === null) return $string;
	    $comment = $this->comment;
	    if (strpos(trim($template), 'get_comment_meta =>') === 0) {
	      
	    }
	    switch ($template) :
	      case 'comment_content' :
	        $string = str_replace('${'.$template.'}$', $comment->comment_content, $string);
	        break;
	      case 'comment_author' :
	        $string = str_replace('${'.$template.'}$', $comment->comment_author, $string);
	        break;
	      case 'comment_date' :
	        $string = str_replace('${'.$template.'}$', $comment->comment_date, $string);
	        break;
	      case 'comment_id' :
	        $string = str_replace('${'.$template.'}$', $comment->comment_ID, $string);
	        break;
	      case 'comment_post_title' :
	        $id_p = $comment->comment_post_ID;
	        $parent_post = get_post($id_p);
	        $string = str_replace('${'.$template.'}$', $parent_post->post_title, $string);
	        break;
	      case 'comment_timestamp' : 
	        $string = str_replace('${'.$template.'}$', strtotime($comment->comment_date), $string);
	        break;
	      case 'comment_discord_timestamp' :
	        $timestamp = '<t:'.$this->getTimeStamp().':R>';
	        $string = str_replace('${'.$template.'}$', $timestamp, $string);
	        break;
	      case 'comment_permalink' : 
	        $permalink = get_comment_link( $comment->comment_ID );
	        $string = str_replace('${'.$template.'}$', $permalink, $string);
	        break;
	      case 'comment_parent_content' :
	        $comment_parent = get_comment($comment->comment_parent);
	        $string = str_replace('${'.$template.'}$', $comment_parent->comment_content, $string);
	        break;
	      case 'comment_parent_author' :
	        $comment_parent = get_comment($comment->comment_parent);
	        $string = str_replace('${'.$template.'}$', $comment_parent->comment_author, $string);
	        break;
	      case 'comment_parent_date' :
	        $comment_parent = get_comment($comment->comment_parent);
	        $string = str_replace('${'.$template.'}$', $comment_parent->comment_date, $string);
	        break;
	      case 'comment_parent_id' :
	        $string = str_replace('${'.$template.'}$', $comment->comment_parent, $string);
	        break;
	      case 'comment_parent_permalink' :
	        $permalink = get_comment_link( $comment->comment_ID );
	        $string = str_replace('${'.$template.'}$', $permalink, $string);
	        break;
	      case 'comment_parent_post_title' :
	        $comment_parent = get_comment($comment->comment_parent);
	        $id_p = $comment_parent->comment_post_ID;
	        $parent_post = get_post($id_p);
	        $string = str_replace('${'.$template.'}$', $parent_post->post_title, $string);
	        break;
	    endswitch;
	    return $string;
	}
	
	public function discord_content_filter($content) {
	    // Space
      $content = str_replace('&nbsp;', ' ', $content);
      $content = str_replace("\xc2\xa0", ' ', $content);
      // HTML to Markdown
      $content = preg_replace('/<p\b[^>]*>(.*?)<\/p>/is', "$1\n\n", $content);
      $content = preg_replace('/<br\s?\/?>/i', "\n", $content);
      $content = preg_replace('/<(strong|b)>(.*?)<\/\1>/is', '**$2**', $content);
      $content = preg_replace('/<(em|i)>(.*?)<\/\1>/is', '*$2*', $content);
      $content = preg_replace('/<u>(.*?)<\/u>/is', '__$1__', $content);
      
      //  blockquotes
      $content = preg_replace('/<blockquote>(.*?)<\/blockquote>/is', "> $1", $content);
      
      //  lists
      $content = preg_replace('/<ul\b[^>]*>(.*?)<\/ul>/is', "$1", $content);
      $content = preg_replace('/<ol\b[^>]*>(.*?)<\/ol>/is', "$1", $content);
      $content = preg_replace('/<li\b[^>]*>(.*?)<\/li>/is', "- $1\n", $content);
      
      $content = strip_tags($content);
      $content = preg_replace('/\n\s+\n/', "\n\n", $content);
      $content = preg_replace('/[\s]+$/', '', $content);
      $content = trim($content, " \t\n\r\0\x0B\xC2\xA0");
      
      // Htm Entity
      $content = $this->decode_html_entities($content);
      return $content;
  }
	
	public function decode_html_entities($content) {
      $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $content = preg_replace_callback('/&#\d+;/', function($m) {
          return mb_convert_encoding($m[0], 'UTF-8', 'HTML-ENTITIES');
      }, $content);
      $content = preg_replace_callback('/&#(\d+);/', function($matches) {
          return chr($matches[1]);
      }, $content);
      $content = preg_replace_callback('/&#x([a-fA-F0-9]+);/', function($matches) {
          return hex2bin($matches[1]);
      }, $content);
      return $content;
  }
	
	private function get_post_content_dyn( $template, $post ) {
	  $template_array = explode( ':', $template );
	  if (count($template_array) < 3) return 'Syntax Error';
	  if (!is_numeric((int)$template_array[1]) && !is_numeric((int)$template_array[2])) return 'Syntax Invalid';
	  switch ((int)$template_array[1]) :
	    case 0 :
	      $value = wp_trim_words(strip_tags($post->post_content), (int)$template_array[2], '');
	      break;
	    case 1 :
	      $value = mb_substr(strip_tags($post->post_content), (int)$template_array[2]);
	      break;
	  endswitch;
	  return $value;
	}
	
	private function get_direct_term_list($arg) {
	  $post = $this->post;
	  if (is_numeric($arg[0])) {
	    return get_the_term_list($arg[0], $arg[1], $arg[2], $arg[3], $arg[4]);
	  } elseif (empty($arg[0])) {
	    return get_the_term_list($post->ID, $arg[1], $arg[2], $arg[3], $arg[4]);
	  } else {
	    $post_id = get_post_meta($post->ID, $arg[0], true);
	    if (empty($post_id)) return '';
	    return get_the_term_list($post_id, $arg[1], $arg[2], $arg[3], $arg[4]);
	  }
	}
	
	private function get_direct_post_info_value($arg) {
	  $post_id = $arg[0];
	  $info = $arg[1];
	  $value = '';
	  if (strpos(trim($post_id), ',') !== false) return $value;
	  if (strpos(trim($info), ',') !== false) return $value;
	  if (!$post_id || empty($post_id)) {
	    $post = $this->post;
	    $value = $this->get_info_post( $info, $post );
	    return $value;
	  } elseif(is_numeric($post_id)) {
	    $post = get_post($post_id);
	    if (!$post) return $value;
	    $value = $this->get_info_post( $info, $post );
	    return $value;
	  } else {
	    $post = $this->post;
	    
	    $post_id = get_post_meta($post->ID, $post_id, true);
	    if (!is_numeric($post_id) || empty($post_id)) return $value;
	    $post = get_post($post_id);
	    if (!$post) return $value;
	    $value = $this->get_info_post( $info, $post );
	    return $value;
	  }
	}
	
	private function get_info_post( $info, $post ) {
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
	      case 'thumbnail_url' :
	        $value = get_the_post_thumbnail_url($post->ID);
	        break;
	      case 'permalink' :
	        $value = get_permalink($post->ID);
	        break;
	    }
	    if (strpos(trim($info), 'post_content:') === 0) {
	      $value = get_post_content_dyn( $info, $post );
	    }
	    return $value;
	}
	
	private function get_direct_formatted_value($arg) {
	  return $this->get_formatted_value(['mode' => $arg[0], 'keys' => explode(',',$arg[1]), 'separator' => $arg[2]]);
	}
	
	private function get_formatted_value($option) {
      $post = $this->post;
      
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