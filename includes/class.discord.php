<?php
/**
 * Dircord Integration 
 */

require_once('class.implements.php');

class WPDEP_Discord implements WPDEP_Const {
  private $option;
  public function __construct($data) {
    	  /* Silence is golden */ 
      $this->option = new stdClass();
      $this->option->raw = $data;
      $this->filter();
      $this->option->response = [];
	}
	
	public function Send() {
	  if (empty($this->option->filtered) || count($this->option->filtered) === 0) return false;
	  foreach ($this->option->filtered as $option) :
	    switch ($this->option->raw['connection_type']) :
	      case 'bot':
	        $this->option->current = $option;
	        $this->sendViaBot();
	        break;
	      case 'webhook':
	        $this->option->current = $option;
	        $this->sendViaWebhooks();
	        break;
	    endswitch;
	  endforeach;
	  return true;
	}
	
	private function filter() {
	    $final = [];
	    foreach ($this->option->raw['options'] as $raw) {
	      if ($this->option->raw['connection_type'] === 'bot') {
	        if(empty($this->option->raw['bot_token']) && empty($this->option->raw['channel_id']) && empty($raw['bot_token']) && empty($raw['channel_id'])) {
	          continue;
	        }
	      }
	      if ($this->option->raw['connection_type'] === 'webhook') {
	        if(empty($this->option->raw['webhook_url']) && empty($raw['webhook_url'])) {
	          continue;
	        }
	      }
	      $final[] = $raw;
	    }
	    $this->option->filtered = $final;
	}
	
	private function getBotToken() {
	  return !empty($this->option->current['bot_token']) ? $this->option->current['bot_token'] : $this->option->raw['bot_token'];
	}
	
	private function getChannelId() {
	  return !empty($this->option->current['channel_id']) ? $this->option->current['channel_id'] : $this->option->raw['channel_id'];
	}
	
	private function getWebhookUrl() {
	  return !empty($this->option->current['webhook_url']) ? $this->option->current['webhook_url'] : $this->option->raw['webhook_url'];
	}
	
	private function getConnetionType() {
	  return $this->option->raw['connection_type'];
	}
	
	public function sendViaWebhooks() {
	  $url = $this->getWebhookUrl();
	  $POSTX = ['content' => $this->option->current['main_message'],
              'embeds' => [$this->option->current['embeded']['embeded']],
             ];
    $headers = ['Content-Type: application/json'];
    $this->curl($url, $POSTX, $headers);
	}
	
	public function sendViaBot() {
	  $channelId = $this->getChannelId();
	  $url = "https://discord.com/api/v9/channels/{$channelId}/messages";
	  $POSTX = [
        'content' => $this->option->current['main_message'],
        'embeds' => [ $this->option->current['embeded']['embeded'] ],
        'components' => [
            [
                'type' => 1,
                'components' => $this->option->current['embeded']['components']
            ]
        ]
    ];
    $headers = [
      'Content-Type: application/json',
      'Authorization: Bot ' . $this->getBotToken(),
    ];
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
    $this->option->response[] = json_decode($response, true);
    curl_close($ch);
	}
	
}