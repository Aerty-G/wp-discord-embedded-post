<?php 

class WPDEP_Updater {
    private $plugin_slug;
    private $version;
    private $json_url = 'https://raw.githubusercontent.com/Aerty-G/wp-discord-embedded-post/refs/heads/main/update.json';
    private $cache_key = 'wpdep_update_info';

    public function __construct() {
        $this->plugin_slug = plugin_basename(__FILE__);
        $this->version = get_file_data(__FILE__, array('Version' => 'Version'))['Version'];
        
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
    }

    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_info = $this->get_remote_info();
        
        if ($remote_info && version_compare($this->version, $remote_info->version, '<')) {
            $res = new stdClass();
            $res->slug = $this->plugin_slug;
            $res->plugin = $this->plugin_slug;
            $res->new_version = $remote_info->version;
            $res->tested = $remote_info->tested;
            $res->package = $remote_info->download_url;
            $res->url = 'https://github.com/Aerty-G/wp-discord-embedded-post';
            
            $transient->response[$res->plugin] = $res;
        }
        
        return $transient;
    }

    public function plugin_info($false, $action, $response) {
        if ($response->slug !== $this->plugin_slug) {
            return $false;
        }
        
        $remote_info = $this->get_remote_info();
        
        if (!$remote_info) {
            return $false;
        }
        
        $response->last_updated = $remote_info->last_updated;
        $response->slug = $this->plugin_slug;
        $response->plugin = $this->plugin_slug;
        $response->name = 'Wp Discord Embedded Post';
        $response->version = $remote_info->version;
        $response->author = 'Aerty-G';
        $response->homepage = 'https://github.com/Aerty-G/wp-discord-embedded-post';
        $response->download_link = $remote_info->download_url;
        $response->requires = $remote_info->requires;
        $response->tested = $remote_info->tested;
        $response->sections = array(
            'description' => 'A Discord integration that sends a message on your desired Discord server and channel for every new post published',
            'changelog' => $this->format_changelog($remote_info->changelog)
        );
        
        if (isset($remote_info->banners)) {
            $response->banners = $remote_info->banners;
        }
        
        return $response;
    }

    private function get_remote_info() {
        $cache = get_transient($this->cache_key);
        
        if ($cache !== false) {
            return $cache;
        }
        
        $response = wp_remote_get($this->json_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json'
            )
        ));
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
            return false;
        }
        
        $info = json_decode(wp_remote_retrieve_body($response));
        
        if (!isset($info->version)) {
            return false;
        }
        
        // Cache for 12 hours
        set_transient($this->cache_key, $info, 12 * HOUR_IN_SECONDS);
        
        return $info;
    }

    private function format_changelog($changelog) {
        $output = '<h3>Changelog</h3><ul>';
        
        foreach ($changelog as $version => $changes) {
            $output .= '<li><strong>Version ' . esc_html($version) . '</strong><ul>';
            
            foreach ($changes as $change) {
                $output .= '<li>' . esc_html($change) . '</li>';
            }
            
            $output .= '</ul></li>';
        }
        
        $output .= '</ul>';
        return $output;
    }
}

