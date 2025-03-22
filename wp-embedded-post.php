<?php
/**
 * WP Embedded Post
 *
 * @author      Aerty-G
 *
 * Plugin Name: Wp Embedded Post
 * Plugin URI:  https://developerhero.net/plugins/wp-discord-post-plus/
 * Description: A Discord integration that sends a message on your desired Discord server and channel for every new post published.
 *
 * Version:     0.0.1
 * Author:      Aerty-G
 * Author URI:  https://aerty.my.id
 * Text Domain: wp-embedded-post
 */
 
 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once('includes/class.implements.php');
require_once('includes/class.discord.php');
require_once('includes/class.helper.php');


class WP_Embedded_Post implements WEP_Const {
  
}