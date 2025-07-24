<?php 

class WPDEP_Updater {
    private $plugin_slug;
    private $plugin_version;
    private $update_url;

    public function __construct($version = null) {
        $this->plugin_slug = 'wp-embedded-post';
        $this->plugin_version = $version ?? WPDEP_VERSION;
        $this->update_url = 'https://raw.githubusercontent.com/Aerty-G/wp-discord-embedded-post/refs/heads/main/update.json';

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
    }

    public function check_for_update($transient) {
        if (empty($transient->checked)) return $transient;

        $remote = $this->get_remote_info();
        if (!$remote) return $transient;

        if (version_compare($this->plugin_version, $remote->version, '<')) {
            $transient->response[$this->plugin_slug . '/' . $this->plugin_slug . '.php'] = (object)[
                'slug' => $this->plugin_slug,
                'new_version' => $remote->version,
                'url' => $remote->homepage,
                'package' => $remote->download_url,
            ];
        }

        return $transient;
    }

    public function plugin_info($false, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) return false;

        $remote = $this->get_remote_info();
        if (!$remote) return false;

        return (object)[
            'name' => $remote->name,
            'slug' => $remote->slug,
            'version' => $remote->version,
            'author' => $remote->author,
            'author_homepage' => $remote->author_homepage,
            'homepage' => $remote->homepage,
            'requires' => $remote->requires,
            'tested' => $remote->tested,
            'sections' => (array)$remote->sections,
            'download_link' => $remote->download_url,
        ];
    }

    private function get_remote_info() {
        $response = wp_remote_get($this->update_url);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response));
    }
}