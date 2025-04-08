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
	  $post_id = $data['post_id'];
	  $data = $data['data'];
	  foreach ( $data as $dat ) :
	    foreach ( $dat as $dt ) :
	      if( $dt['style_id'] !== '' ) :
	        
	      endif;
	    endforeach;
	  endforeach;
	  if ( empty( $f ) ) return false;
	}
	
	public function RetrieveEmbedStyle( $id ) {
	  $styles = get_option( self::EMBEDDED_STRUCT_LIST_OPT, array() );
	  if ( empty( $styles ) ) return false;
	  return isset( $styles['embeded'][$id] ) ? $styles['embeded'][$id] : false;
	}
	
	public function RetrieveEmbedButtonStyle( $id ) {
	  $styles = get_option( self::EMBEDDED_STRUCT_LIST_OPT, array() );
	  if ( empty( $styles ) ) return false;
	  return isset( $styles['embeded_button'][$id] ) ? $styles['embeded_button'][$id] : false;
	}
	
	public function FilteredAllVar( $data, $id ) {
	  
	}
	
	public function FilterVar( $string, $id ) {
	  $vars = get_option( self::EMBEDDED_VAR_LIST_OPT, array() );
	  if( empty( $vars ) ) return $string;
	  foreach ( $vars as $var ) :
	    switch ( $var['mode'] ) : 
	      case 'single' :
	        $vv = get_post_meta( $id, $var['keys'][0] );
	        break;
	      case 'combine' :
	        $ka = array();
	        foreach ( $var['keys'] as $k ) :
	          $ka[] = get_post_meta( $id, $k );
	        endforeach;
	        $vv = implode( $var['separator'], $ka );
	        break;
	      case 'connect' :
	        $id2 = get_post_meta( $id, $var['keys'][0] );
	        $vv = get_post_meta( $id2, $var['keys'][1] );
	        break;
	    endswitch;
	      $string = str_replace( $var['template'], ( empty( trim( $vv ) ) ? $var['template'] : $vv ), $string );
	  endforeach;
	  return $string;
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