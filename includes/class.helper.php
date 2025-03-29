<?php
/**
 * Helper For Filtering Nad Retrieve Data
 */

require_once('class.implements.php');

class WDEP_Helper implements WDEP_Const {
  
  public function __construct() {
	  /* Silence is golden */ 
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