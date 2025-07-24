<?php 

require_once('class.implements.php');

class WPDEP_Admin implements WPDEP_Const {
    private $admin_pages = [];
    private $admin_assets_loaded = false;
    private $meta_options = [];
  
    public function __construct() {
      	  /* Silence is golden */ 
      	  $this->init_admin_pages();
      	  add_action('admin_enqueue_scripts', [$this, 'wpdep_wp_enqueue']);
      	  add_action('admin_menu', array($this, 'add_admin_menus'));
          add_action('admin_init', array($this, 'handle_form_submissions'));
          add_action('add_meta_boxes', [$this, 'meta_boxes']);
          add_action('save_post', [$this, 'save_metabox']);
  	}
	
	
	  private function init_admin_pages() {
        $this->admin_pages = [
            'main' => [
                'page_title' => 'WP Discord Embedded Post',
                'menu_title' => 'WP Discord Post',
                'capability' => 'manage_options',
                'menu_slug' => 'wp-discord-embedded-post',
                'callback' => [$this, 'FormMain'],
                'icon' => 'dashicons-admin-generic',
                'position' => 60
            ],
            'submenus' => [
                [
                    'parent_slug' => 'wp-discord-embedded-post',
                    'page_title' => 'Default Settings',
                    'menu_title' => 'Default Settings',
                    'capability' => 'manage_options',
                    'menu_slug' => 'wpdep-default-setings',
                    'callback' => [$this, 'FormDefaultSet']
                ],
                [
                    'parent_slug' => 'wp-discord-embedded-post',
                    'page_title' => 'Variable Manager',
                    'menu_title' => 'Variable Manager',
                    'capability' => 'manage_options',
                    'menu_slug' => 'wpdep-var-manager',
                    'callback' => [$this, 'FormVarManager']
                ],
                [
                    'parent_slug' => 'wp-discord-embedded-post',
                    'page_title' => 'Embedded Style Manager',
                    'menu_title' => 'Embedded Style',
                    'capability' => 'manage_options',
                    'menu_slug' => 'wpdep-embed-style-manager',
                    'callback' => [$this, 'FormEmbeddedStyle']
                ],
                [
                    'parent_slug' => 'wp-discord-embedded-post',
                    'page_title' => 'Embedded Comment Manager',
                    'menu_title' => 'Embedded Comment',
                    'capability' => 'manage_options',
                    'menu_slug' => 'wpdep-embed-comment-manager',
                    'callback' => [$this, 'FormEmbeddedStyleComment']
                ],
                [
                    'parent_slug' => 'wp-discord-embedded-post',
                    'page_title' => 'Post & Category Manager',
                    'menu_title' => 'Cat Manager',
                    'capability' => 'manage_options',
                    'menu_slug' => 'wpdep-post-cat-manager',
                    'callback' => [$this, 'FormManager'],
                    'requires_select2' => true
                ]
            ]
        ];
    }
	
  	public function add_admin_menus() {
        add_menu_page(
            $this->admin_pages['main']['page_title'],
            $this->admin_pages['main']['menu_title'],
            $this->admin_pages['main']['capability'],
            $this->admin_pages['main']['menu_slug'],
            $this->admin_pages['main']['callback'],
            $this->admin_pages['main']['icon'],
            $this->admin_pages['main']['position']
        );

        foreach ($this->admin_pages['submenus'] as $submenu) {
            add_submenu_page(
                $submenu['parent_slug'],
                $submenu['page_title'],
                $submenu['menu_title'],
                $submenu['capability'],
                $submenu['menu_slug'],
                $submenu['callback']
            );
        }
    }
    
  	public function handle_form_submissions() {
        if (isset($_POST['save_wpdep_var_options'])) {
            $this->save_variable_options();
        }
        
        if (isset($_POST['save_wpdep_default_discord_settings'])) {
            $this->save_default_discord_settings();
        }
        
        if (isset($_POST['save_wpdep_embed_options'])) {
            $this->save_embed_style_options();
        }
        
        if (isset($_POST['save_wpdep_category_options'])) {
            $this->save_category_options();
        }
        
        if (isset($_POST['save_wpdep_embed_comment_options'])) {
            $this->save_embed_comment_style_options();
        }
    }
    
  	
  	private function save_variable_options() {
        check_admin_referer('save_wpdep_var_options_action', 'wpdep_var_options_nonce');
        
        $options = [];
        
        if (isset($_POST['options'])) {
            foreach ($_POST['options'] as $option) {
                if (!empty($option['title'])) {
                    $processed_option = [
                        'title' => sanitize_text_field($option['title']),
                        'mode' => isset($option['mode']) ? sanitize_text_field($option['mode']) : 'single',
                        'keys' => [],
                        'template' => isset($option['template']) ? sanitize_text_field($option['template']) : '',
                        'separator' => isset($option['separator']) ? sanitize_text_field($option['separator']) : ', '
                    ];
                    
                    if (!empty($option['keys'])) {
                        foreach ($option['keys'] as $key) {
                            if (!empty($key)) {
                                $processed_option['keys'][] = sanitize_key($key);
                            }
                        }
                    }
                    
                    if (!empty($processed_option['keys'])) {
                        $options[] = $processed_option;
                    }
                }
            }
        }
        
        update_option(self::EMBEDDED_VAR_LIST_OPT, $options);
        add_settings_error('wpdep_var_options_messages', 'wpdep_var_options_message', __('Variable options saved successfully!', 'wp-discord-embedded-post'), 'success');
    }
  	
  	private function save_default_discord_settings() {
        check_admin_referer('save_wpdep_default_settings_action', 'wpdep_default_settings_nonce');
        
        $settings = [
            'default_tag' => isset($_POST['default_discord_settings']['default_tag']) 
                ? $_POST['default_discord_settings']['default_tag'] 
                : '',
            'default_message' => isset($_POST['default_discord_settings']['default_message']) 
                ? $_POST['default_discord_settings']['default_message'] 
                : '',
            'connection_type' => isset($_POST['default_discord_settings']['connection_type']) 
                ? sanitize_text_field($_POST['default_discord_settings']['connection_type']) 
                : 'webhook',
            'webhook_url' => isset($_POST['default_discord_settings']['webhook_url']) 
                ? esc_url_raw($_POST['default_discord_settings']['webhook_url']) 
                : '',
            'bot_token' => isset($_POST['default_discord_settings']['bot_token']) 
                ? $_POST['default_discord_settings']['bot_token'] 
                : '',
            'channel_id' => isset($_POST['default_discord_settings']['channel_id']) 
                ? sanitize_text_field($_POST['default_discord_settings']['channel_id']) 
                : '',
            'comment_channel_id' => isset($_POST['default_discord_settings']['comment_channel_id']) 
                ? sanitize_text_field($_POST['default_discord_settings']['comment_channel_id']) 
                : '',
            'comment_webhook_url' => isset($_POST['default_discord_settings']['comment_webhook_url']) 
                ? sanitize_text_field($_POST['default_discord_settings']['comment_webhook_url']) 
                : '',
            'hooks' => isset($_POST['default_discord_settings']['hooks']) 
                ? sanitize_text_field($_POST['default_discord_settings']['hooks']) 
                : 'hooks_1',
            'comment_service' => isset($_POST['default_discord_settings']['comment_service']) 
                ? sanitize_text_field($_POST['default_discord_settings']['comment_service']) 
                : 'wp'
        ];
        
        update_option(self::DEFAULT_SET_LIST_OPT, $settings);
        add_settings_error('wpdep_default_settings_messages', 'wpdep_default_settings_message', __('Default Discord settings saved successfully!', 'wp-discord-embedded-post'), 'success');
    }
    
    private function save_embed_style_options() {
        check_admin_referer('save_wpdep_embed_options_action', 'wpdep_embed_options_nonce');
        
        $embed_options = ['embeded' => []];
        
        if (isset($_POST['embed_options']['embeded'])) {
            foreach ($_POST['embed_options']['embeded'] as $embed) {
                $processed_embed = [
                    'author' => [
                        'name' => $embed['author']['name'] ?? '',
                        'url' => ($embed['author']['url'] ?? '')
                    ],
                    'title' => $embed['title'] ?? '',
                    'description' => $embed['description'] ?? '',
                    'fields' => [],
                    'image' => ['url' => ($embed['image']['url'] ?? '')],
                    'color' => ($embed['color'] ?? ''),
                    'timestamp' => sanitize_text_field($embed['timestamp'] ?? ''),
                    'footer' => ['text' => $embed['footer']['text'] ?? ''],
                    'components' => []
                ];
                
                if (isset($embed['fields'])) {
                    foreach ($embed['fields'] as $field) {
                        $processed_embed['fields'][] = [
                            'name' => $field['name'] ?? '',
                            'value' => $field['value'] ?? '',
                            'inline' => isset($field['inline']) ? (bool)$field['inline'] : false
                        ];
                    }
                }
                
                if (isset($embed['components'])) {
                    foreach ($embed['components'] as $component) {
                        if (isset($component['label'])) { 
                            $processed_embed['components'][] = [
                                'type' => 2,
                                'label' => $component['label'],
                                'url' => ($component['url'] ?? ''),
                                'emoji' => [
                                    'id' => sanitize_text_field($component['emoji']['id'] ?? ''),
                                    'name' => sanitize_text_field($component['emoji']['name'] ?? ''),
                                    'animated' => isset($component['emoji']['animated']) ? (bool)$component['emoji']['animated'] : false
                                ]
                            ];
                        }
                    }
                }
                
                $embed_options['embeded'][] = $processed_embed;
            }
        }
        
        update_option(self::EMBEDDED_STRUCT_LIST_OPT, $embed_options);
        add_settings_error('wpdep_embed_options_messages', 'wpdep_embed_options_message', __('Embed settings saved successfully!', 'wp-discord-embedded-post'), 'success');
    }
    
    private function save_embed_comment_style_options() {
        check_admin_referer('save_wpdep_embed_comment_options_action', 'wpdep_embed_comment_options_nonce');
        
        $embed_options = ['embeded' => []];
        
        if (isset($_POST['embed_options']['embeded'])) {
            foreach ($_POST['embed_options']['embeded'] as $embed) {
                $processed_embed = [
                    'author' => [
                        'name' => $embed['author']['name'] ?? '',
                        'url' => ($embed['author']['url'] ?? '')
                    ],
                    'title' => $embed['title'] ?? '',
                    'description' => $embed['description'] ?? '',
                    'fields' => [],
                    'image' => ['url' => ($embed['image']['url'] ?? '')],
                    'color' => ($embed['color'] ?? ''),
                    'timestamp' => sanitize_text_field($embed['timestamp'] ?? ''),
                    'footer' => ['text' => $embed['footer']['text'] ?? ''],
                    'components' => []
                ];
                
                if (isset($embed['fields'])) {
                    foreach ($embed['fields'] as $field) {
                        $processed_embed['fields'][] = [
                            'name' => $field['name'] ?? '',
                            'value' => $field['value'] ?? '',
                            'inline' => isset($field['inline']) ? (bool)$field['inline'] : false
                        ];
                    }
                }
                
                if (isset($embed['components'])) {
                    foreach ($embed['components'] as $component) {
                        if (isset($component['label'])) { 
                            $processed_embed['components'][] = [
                                'type' => 2,
                                'label' => $component['label'],
                                'url' => ($component['url'] ?? ''),
                                'emoji' => [
                                    'id' => sanitize_text_field($component['emoji']['id'] ?? ''),
                                    'name' => sanitize_text_field($component['emoji']['name'] ?? ''),
                                    'animated' => isset($component['emoji']['animated']) ? (bool)$component['emoji']['animated'] : false
                                ]
                            ];
                        }
                    }
                }
                
                $embed_options['embeded'][] = $processed_embed;
            }
        }
        
        update_option(self::EMBEDDED_COMMENT_STRUCT_OPT, $embed_options);
        //file_put_contents(__DIR__.'/struktur.txt', var_export($embed_options, true));
        add_settings_error('wpdep_embed_comment_options_messages', 'wpdep_embed_comment_options_messages', __('Embed settings saved successfully!', 'wp-discord-embedded-post'), 'success');
    }
    
    private function save_category_options() {
        check_admin_referer('save_wpdep_category_options_action', 'wpdep_category_options_nonce');
        
        $category_options = [];
        
        if (isset($_POST['category_options'])) {
            foreach ($_POST['category_options'] as $option) {
                $cat_ids = isset($option['cat_ids']) ? array_map('absint', $option['cat_ids']) : [];
                
                $processed_option = [
                    'cat_ids' => $cat_ids,
                    'selected_embedded_style' => isset($option['selected_embedded_style']) 
                        ? sanitize_text_field($option['selected_embedded_style']) 
                        : '',
                    'channel_id' => isset($option['channel_id']) 
                        ? sanitize_text_field($option['channel_id']) 
                        : '',
                    'main_message' => isset($option['main_message']) 
                        ? $option['main_message']
                        : '',
                    'bot_token' => isset($option['bot_token']) 
                        ? sanitize_text_field($option['bot_token']) 
                        : '',
                    'webhook_url' => isset($option['webhook_url']) 
                        ? esc_url_raw($option['webhook_url']) 
                        : ''
                ];
                
                $category_options[] = $processed_option;
            }
        }
        
        
        update_option(self::CATEGORY_SELECTED_SET_OPT, $category_options);
        add_settings_error('wpdep_category_options_messages', 'wpdep_category_options_message', __('Category options saved successfully!', 'wp-discord-embedded-post'), 'success');
    }
  	public function FormMain() {
  	      echo file_get_contents( WPDEP_PATH_DIR.'/includes/documentation.html' );
  	}
  	
  	public function FormDefaultSet() {
	      $defaultsetarray = get_option(self::DEFAULT_SET_LIST_OPT, array());
        $connection_type = $defaultsetarray['connection_type'] ?? 'webhook';
        $settings = wp_parse_args($defaultsetarray, [
            'default_tag' => '',
            'webhook_url' => '',
            'bot_token' => '',
            'channel_id' => '',
            'default_message' => '',
            'comment_webhook_url' => '',
            'comment_channel_id' => '',
            'hooks' => 'hooks_1',
            'comment_service' => 'wp'
        ]);
          settings_errors('wpdep_default_settings_messages');
         ob_start();
        ?>
        <div class="wrap">
            <h1>Discord Defaults Settings</h1>
            <div class="wpdep-dashboard-widget">
                <form method="post">
                    <div class="discord-settings-container">
                        <div class="setting-group">
                            <label>Default Tag</label>
                            <input type="text" 
                                   name="default_discord_settings[default_tag]" 
                                   value="<?php echo esc_attr($settings['default_tag']); ?>" 
                                   class="widefat" 
                                   placeholder="@here or @everyone">
                            <p class="description">This tag will be used for all notifications</p>
                        </div>
                        <div class="setting-group">
                            <label>Default Message</label>
                            <textarea 
                                   name="default_discord_settings[default_message]" 
                                   class="widefat" 
                                   placeholder="@here or @everyone or Message You Want"><?php echo esc_textarea($settings['default_message']??''); ?></textarea>
                            <p class="description">This Message will be used for all notifications</p>
                        </div>
                        <div class="setting-group">
                            <label>Hooks Type:</label>
                            <div class="radio-options">
                                    <label>
                                        <input type="radio" name="default_discord_settings[hooks]" 
                                               value="hooks_1" <?php checked($settings['hooks'], 'hooks_1'); ?>>
                                        Hooks 1
                                    </label>
                                    <label>
                                        <input type="radio" name="default_discord_settings[hooks]" 
                                               value="hooks_2" <?php checked($settings['hooks'], 'hooks_2'); ?>>
                                        Hooks 2
                                    </label>
                                    <label>
                                        <input type="radio" name="default_discord_settings[hooks]" 
                                               value="hooks_3" <?php checked($settings['hooks'], 'hooks_3'); ?>>
                                        Hooks 3 (Recommended)
                                    </label>
                            </div>
                        </div>
                        <div class="setting-group">
                            <label>Comment Service:</label>
                            <div class="radio-options">
                                    <label>
                                        <input type="radio" name="default_discord_settings[comment_service]" 
                                               value="wp" <?php checked($settings['comment_service'], 'wp'); ?>>
                                        WordPress 
                                    </label>
                                    <label>
                                        <input type="radio" name="default_discord_settings[comment_service]" 
                                               value="wpdiscuz" <?php checked($settings['comment_service'], 'wpdiscuz'); ?>>
                                        WpDiscuz
                                    </label>
                                    <label>
                                        <input type="radio" name="default_discord_settings[comment_service]" 
                                               value="disqus" <?php checked($settings['comment_service'], 'disqus'); ?>>
                                        Disqus
                                    </label>
                            </div>
                        </div>
                        <div class="setting-group">
                            <label>Connection Type:</label>
                            <div class="radio-options">
                                <?php foreach (['webhook', 'bot'] as $type) : ?>
                                    <label>
                                        <input type="radio" name="default_discord_settings[connection_type]" 
                                               value="<?php echo $type; ?>" <?php checked($connection_type, $type); ?>>
                                        <?php echo ucfirst($type); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
        
                        <div id="webhook-settings" class="connection-settings" style="<?php echo ($connection_type !== 'webhook') ? 'display:none;' : ''; ?>">
                            <div class="setting-group">
                                <label for="discord_webhook_url">Webhook URL</label>
                                <input type="text" 
                                       id="discord_webhook_url" 
                                       name="default_discord_settings[webhook_url]" 
                                       value="<?php echo esc_attr($settings['webhook_url']); ?>" 
                                       class="widefat"
                                       placeholder="https://discord.com/api/webhooks/...">
                                <p class="description">The Discord webhook URL for sending messages</p>
                            </div>
                            <div class="setting-group">
                                <label for="discord_webhook_url">Comment Webhook URL</label>
                                <input type="text" 
                                       id="discord_webhook_url" 
                                       name="default_discord_settings[comment_webhook_url]" 
                                       value="<?php echo esc_attr($settings['comment_webhook_url']); ?>" 
                                       class="widefat"
                                       placeholder="https://discord.com/api/webhooks/...">
                                <p class="description">The Discord webhook URL for sending comment messages</p>
                            </div>
                        </div>
        
                        <div id="bot-settings" class="connection-settings" style="<?php echo ($connection_type !== 'bot') ? 'display:none;' : ''; ?>">
                            <div class="setting-group">
                                <label for="discord_bot_token">Bot Token</label>
                                <input type="text" 
                                       id="discord_bot_token" 
                                       name="default_discord_settings[bot_token]" 
                                       value="<?php echo esc_attr($settings['bot_token']); ?>" 
                                       class="widefat"
                                       placeholder="Bot token from Discord Developer Portal">
                                <p class="description">Your Discord bot token (keep this secure)</p>
                            </div>
                            
                            <div class="setting-group">
                                <label for="discord_channel_id">Channel ID</label>
                                <input type="text" 
                                       id="discord_channel_id" 
                                       name="default_discord_settings[channel_id]" 
                                       value="<?php echo esc_attr($settings['channel_id']); ?>" 
                                       class="widefat"
                                       placeholder="Target channel ID">
                                <p class="description">The channel ID where messages should be sent</p>
                            </div>
                            <div class="setting-group">
                                <label for="discord_channel_id">Comment Channel ID</label>
                                <input type="text" 
                                       id="discord_channel_id" 
                                       name="default_discord_settings[comment_channel_id]" 
                                       value="<?php echo esc_attr($settings['comment_channel_id']); ?>" 
                                       class="widefat"
                                       placeholder="Target channel ID">
                                <p class="description">The channel ID where comment message should be sent</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="save_wpdep_default_discord_settings" class="button button-primary">Save Settings</button>
                    </div>
                    
                    <?php wp_nonce_field('save_wpdep_default_settings_action', 'wpdep_default_settings_nonce'); ?>
                </form>
            </div>
        </div>
        <?php
        echo ob_get_clean();
  	}
  	
  	public function FormManager() {
  	    $default_category_option = [
            'cat_ids' => [],
            'main_message' => '',
            'selected_embedded_style' => '',
            'channel_id' => '',
            'bot_token' => '',
            'webhook_url' => ''
        ];
        $category_options = get_option(self::CATEGORY_SELECTED_SET_OPT, [$default_category_option]);
        
        $categories = get_categories(array(
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        $embed_options = get_option(self::EMBEDDED_STRUCT_LIST_OPT, array());
        $embed_styles = [];
        
        if (!empty($embed_options['embeded'])) {
            foreach ($embed_options['embeded'] as $index => $embed) {
                $title = !empty($embed['title']) ? $embed['title'] : __('Untitled Embed', 'meta-options-manager');
                $embed_styles[$index] = $title . ' (#' . ($index + 1) . ')';
            }
        }
        
        $js_categories = [];
        foreach ($categories as $category) {
            $js_categories[] = [
                'id' => $category->term_id,
                'name' => $category->name
            ];
        }
        
        $js_embed_styles = $embed_styles;  
        
        settings_errors('wpdep_category_options_messages');
        ob_start();
        ?>
        <script>
            var wpdepData = {
                categories: <?php echo json_encode($js_categories); ?>,
                embedStyles: <?php echo json_encode($js_embed_styles); ?>
            };
        </script>
        <div class="wrap">
            <h1>Post & Category Manager</h1>
            <div class="wpdep-dashboard-widget">
                <form method="post" id="category-options-form">
                    <div id="category-options-container">
                        <?php foreach ($category_options as $index => $option) : ?>
                            <div class="category-option-block" data-index="<?php echo $index; ?>">
                                <?php echo $this->render_category_option_block($index, $option, $categories, $embed_styles); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" id="add-category-option" class="button button-primary">Add Category Option</button>
                        <button type="submit" name="save_wpdep_category_options" class="button button-primary">Save Settings</button>
                    </div>
                    
                    <?php wp_nonce_field('save_wpdep_category_options_action', 'wpdep_category_options_nonce');  ?>
                </form>
            </div>
        </div>
        
        <script>
          jQuery(function($) {
              $('.category-select').select2({
                  placeholder: "Select categories...",
                  allowClear: true,
                  width: '100%'
              });
              
              $('.embed-style-select').select2({
                  placeholder: "Select an embed style...",
                  allowClear: true,
                  width: '100%'
              });
          });
        </script>
        <?php
        echo ob_get_clean();
  	}
  	
  	private function render_category_option_block($index, $option, $categories, $embed_styles) {
        ob_start();

       $selected_cat_ids = isset($option['cat_ids']) ? (array) $option['cat_ids'] : [];
        ?>
        <div class="option-header">
            <h3>Category Setting #<?php echo ($index + 1); ?></h3>
            <button type="button" class="remove-category-option button">Remove</button>
        </div>
        
        <div class="setting-group">
            <label>Categories</label>
            <select name="category_options[<?php echo $index; ?>][cat_ids][]" 
                    class="widefat category-select" 
                    multiple="multiple"
                    data-selected="<?php echo esc_attr(json_encode($selected_cat_ids)); ?>">
            </select>
            <p class="description">Select one or more categories</p>
        </div>
        
        <div class="setting-group">
            <label>Embedded Style</label>
            <select name="category_options[<?php echo $index; ?>][selected_embedded_style]" 
                    class="widefat embed-style-select"
                    data-selected="<?php echo esc_attr($option['selected_embedded_style']); ?>">
                <option value="">— Default Style —</option>
            </select>
            <p class="description">Choose the embed style for these categories</p>
        </div>
        
        <div class="setting-group">
            <label>Main Messages (optional)</label>
            <textarea  
                   name="category_options[<?php echo $index; ?>][main_message]" 

                   class="widefat"
                   placeholder="Hello There, Our Category Post Has An Update!"><?php echo ($option['main_message']); ?></textarea>
                   <p class="description">This Main Message Will Be Send As Default Message Like You Send Messages In Discord Normally Before The Embedded Message.</p>
        </div>
        
        <div class="setting-group">
            <label>Channel ID (optional)</label>
            <input type="text" 
                   name="category_options[<?php echo $index; ?>][channel_id]" 
                   value="<?php echo esc_attr($option['channel_id']); ?>" 
                   class="widefat"
                   placeholder="Target channel ID">
                   <p class="description">Override default channel for these categories</p>
        </div>
        
        <div class="setting-group">
            <label>Bot Token (optional)</label>
            <input type="text" 
                   name="category_options[<?php echo $index; ?>][bot_token]" 
                   value="<?php echo esc_attr($option['bot_token']); ?>" 
                   class="widefat"
                   placeholder="Bot token from Discord Developer Portal">
                   <p class="description">Override default bot token for these categories</p>
        </div>
        
        <div class="setting-group">
            <label>Webhook URL (optional)</label>
            <input type="text" 
                   name="category_options[<?php echo $index; ?>][webhook_url]" 
                   value="<?php echo esc_attr($option['webhook_url']); ?>" 
                   class="widefat"
                   placeholder="https://discord.com/api/webhooks/...">
                   <p class="description">Override default webhook for these categories</p>
        </div>
        <?php
        return ob_get_clean();
    }
  	
  	public function FormVarManager() {
	      $saved_options = get_option(self::EMBEDDED_VAR_LIST_OPT, array());
	      $default_option = [
            'title' => '',
            'mode' => 'single',
            'keys' => [''],
            'template' => '',
            'separator' => ', '
        ];
        settings_errors('wpdep_var_options_messages');
        ob_start();
        ?>
        <div class="wrap">
            <h1>Manage Variable Options</h1>
            <div class="wpdep-dashboard-widget">
                <form id="vars-form">
                    <div id="option-blocks-container">
                        <?php if (empty($saved_options)) : ?>
                            <?php echo $this->render_option_block(0, $default_option); ?>
                        <?php else : ?>
                            <?php foreach ($saved_options as $index => $option) : ?>
                                <?php echo $this->render_option_block($index, $option); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="form-actions">
                        <button type="button" id="add-option" class="button button-primary">Add New Option</button>
                        <button type="submit" name="save_wpdep_var_options" class="button button-primary">Save Options</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
        echo ob_get_clean();
  	}
  	private function render_option_block($index, $option) {
        ob_start();
        ?>
        <div class="option-block" data-index="<?php echo $index; ?>">
            <div class="option-header">
                <label>Title: 
                    <input type="text" name="options[<?php echo $index; ?>][title]" 
                           value="<?php echo esc_attr($option['title']); ?>" 
                           placeholder="Section Title" class="widefat">
                </label>
                <div class="mode-selector">
                    <?php foreach (['single', 'combine', 'connect'] as $mode) : ?>
                        <label>
                            <input type="radio" name="options[<?php echo $index; ?>][mode]" 
                                   value="<?php echo $mode; ?>" <?php checked($option['mode'] ?? 'single', $mode); ?>>
                            <span class="radio-custom"></span>
                            <span class="radio-label"><?php echo ucfirst($mode); ?></span>
                        </label>
                    <?php endforeach; ?>
                    <button type="button" class="remove-option button">Remove</button>
                </div>
            </div>
            
            <div class="separator-group" style="<?php echo (($option['mode'] ?? '') === 'combine') ? '' : 'display:none;'; ?>">
                <label>Separator: 
                    <input type="text" name="options[<?php echo $index; ?>][separator]" 
                           value="<?php echo esc_attr($option['separator'] ?? ', '); ?>" class="widefat">
                </label>
            </div>
            
            <div class="keys-container">
                <?php foreach ($option['keys'] as $key_index => $key) : ?>
                    <div class="key-input">
                        <input type="text" name="options[<?php echo $index; ?>][keys][]" 
                               value="<?php echo esc_attr($key); ?>" 
                               placeholder="meta_key" class="widefat">
                        <?php if ($key_index === 0) : ?>
                            <button type="button" class="add-key button">+</button>
                        <?php else : ?>
                            <button type="button" class="remove-key button">-</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="template-group">
                <label>Output Template: 
                    <input type="text" name="options[<?php echo $index; ?>][template]" 
                           class="widefat" placeholder="E.g.: Post Title: ${title_post}$" 
                           value="<?php echo esc_attr($option['template'] ?? ''); ?>">
                </label>
                <p class="description">Use ${meta_key}$ to insert meta values</p>
            </div>
            
            <?php if (empty($saved_options)) : ?>
                <div class="information">
                    <label>More Information: 
                    <p class="description">
                        Single: Meta Value From First Meta Key Will Became Output Of The Variable.<br>
                        Combine: All Meta Key In The Meta Key Section Will Be Retrieved And Combined With The Separator You Gift It.<br>
                        Connect: The Value Of First Meta Key Will Became Post ID of The Second Meta Key, Only If The Value Of The First Meta Key Was Integer.<br>
                    </p></label>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
  	
  	public function FormEmbeddedStyle() {
  	    $defaultsetarray = get_option(self::DEFAULT_SET_LIST_OPT, []);
        $connection_type = $defaultsetarray['connection_type'] ?? 'webhook';
        $embed_options = get_option(self::EMBEDDED_STRUCT_LIST_OPT, [
            'embeded' => [
                $this->get_default_embed_array()
            ]
        ]);
        
        settings_errors('wpdep_embed_options_messages');
        ob_start();
        ?>
        <div class="wrap">
            <h1>Discord Embed Style Settings</h1>
            <div class="wpdep-dashboard-widget">
              <form method="post">
                  <div class="embed-options-container">
                      <div class="embed-section">
                          <h2>Embed Settings</h2>
                          
                          <?php foreach ($embed_options['embeded'] as $index => $embed) : ?>
                              <div class="embed-block" data-index="<?php echo $index; ?>">
                                  <?php $this->render_embed_block($index, $embed, $connection_type); ?>
                              </div>
                          <?php endforeach; ?>
                          
                          <button type="button" class="add-embed button">Add New Embed</button>
                      </div>
                  </div>
                  
                  <div class="form-actions">
                      <button type="submit" name="save_wpdep_embed_options" class="button button-primary">Save Style Settings</button>
                  </div>
                  
                  <?php wp_nonce_field('save_wpdep_embed_options_action', 'wpdep_embed_options_nonce'); ?>
              </form>
            </div>
        </div>
        <?php
        echo ob_get_clean();
  	}
  	
  	private function get_default_embed_array() {
        return [
            'author' => ['name' => '', 'url' => ''],
            'title' => '',
            'description' => '',
            'fields' => [],
            'image' => ['url' => ''],
            'color' => '',
            'timestamp' => '',
            'footer' => ['text' => ''],
            'components' => []
        ];
    }
    
    private function render_embed_block($index, $embed, $connection_type, $is_comment = false) {
        ?>
        <div class="option-header">
            <h3>Embed #<?php if (!$is_comment) { echo ($index + 1); } elseif ($index === 0) { ?>Main<?php } else { ?>Reply<?php } ?></h3>
            <?php if (!$is_comment) { ?>
            <button type="button" class="remove-embed button">Remove</button>
            <?php } ?>
        </div>
        
        <div class="setting-group">
                <label>Author Name</label>
                <input type="text" name="embed_options[embeded][<?php echo $index; ?>][author][name]" 
                       value="<?php echo esc_attr($embed['author']['name']); ?>" 
                       class="widefat">
                <p class="description">The name that appears as the author of the embed</p>
            </div>
            
            <div class="setting-group">
                <label>Author URL</label>
                <input type="text" name="embed_options[embeded][<?php echo $index; ?>][author][url]" 
                       value="<?php echo esc_attr($embed['author']['url']); ?>" 
                       class="widefat">
                <p class="description">URL that the author name will link to (optional)</p>
            </div>
            
            <div class="setting-group">
                <label>Title</label>
                <input type="text" name="embed_options[embeded][<?php echo $index; ?>][title]" 
                       value="<?php echo esc_attr($embed['title']); ?>" 
                       class="widefat">
                <p class="description">The main title of your embed (appears in bold at the top)</p>
            </div>
            
            <div class="setting-group">
                <label>Description</label>
                <textarea name="embed_options[embeded][<?php echo $index; ?>][description]" class="widefat" rows="3"><?php 
                    echo esc_textarea($embed['description']); 
                ?></textarea>
                <p class="description">The main content of your embed (supports Markdown formatting)</p>
            </div>
            
            <div class="fields-container">
                <h4>Fields</h4>
                <p class="description">Add key-value pairs to display in your embed</p>
                <?php
                if (!empty($embed['fields'])) :
                foreach ($embed['fields'] as $field_index => $field) : ?>
                    <div class="field-group" data-index="<?php echo $field_index; ?>">
                        <?php echo $this->render_embed_field($index, $field_index, $field); ?>
                    </div>
                <?php endforeach; 
                else : ?>
                    <div class="field-group" data-index="0">
                        <?php echo $this->render_embed_field_default($index); ?>
                    </div>
                <?php endif;
                ?>
            </div>
            <div class="setting-group">
                <label>Image URL</label>
                <input type="text" name="embed_options[embeded][<?php echo $index; ?>][image][url]" 
                       value="<?php echo ($embed['image']['url']); ?>" 
                       class="widefat">
                <p class="description">URL of an image to display at the bottom of the embed</p>
            </div>
            
            <div class="setting-group">
                <label>Color (hex)</label>
                <input type="text" name="embed_options[embeded][<?php echo $index; ?>][color]" 
                       value="<?php echo esc_attr($embed['color']); ?>" 
                       class="widefat" placeholder="#FFFFFF">
                <p class="description">The color of the embed border (hex format)</p>
            </div>
            
            <div class="setting-group">
                <label>Timestamp</label>
                <input type="text" name="embed_options[embeded][<?php echo $index; ?>][timestamp]" 
                       value="<?php echo esc_attr($embed['timestamp']); ?>" 
                       class="widefat" placeholder="Leave empty for current time">
                <p class="description">ISO8601 timestamp or leave empty for current time</p>
            </div>
            
            <div class="setting-group">
                <label>Footer Text</label>
                <input type="text" name="embed_options[embeded][<?php echo $index; ?>][footer][text]" 
                       value="<?php echo esc_attr($embed['footer']['text']); ?>" 
                       class="widefat">
                <p class="description">Text to display in the footer of the embed</p>
            </div>
        
        <div class="components-section">
            <h4>Button Components</h4>
            <p class="section-description">
                These buttons will appear below this embed (max 4 buttons allowed).
                <?php if ($connection_type !== 'bot') : ?>
                    <strong class="notice" style="color:#d63638; display:block; margin-top:5px;">
                        Note: Buttons only work when connection type is set to "Bot" in Discord Settings.
                    </strong>
                <?php endif; ?>
            </p>
            
            <div class="components-container">
                <?php foreach ($embed['components'] as $comp_index => $component) : ?>
                    <?php if ($comp_index < 4) : ?>
                        <div class="component-group" data-index="<?php echo $comp_index; ?>">
                            
                            <?php echo $this->render_component_block($index,  $comp_index, $component );?>
                            
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
           <button type="button" data-index="<?php echo $index; ?>" class="add-component button">+ Add Button</button>
        </div>
        <?php
    }
    
    private function render_embed_field_default($embed_index) {
        ob_start();
        ?>
            <div class="field-group" data-index="0">
                <div class="setting-group">
                    <label>Field Name</label>
                    <input type="text" name="embed_options[embeded][<?php echo $embed_index; ?>][fields][0][name]" class="widefat">
                    <p class="description">The title of this field</p>
                </div>
                <div class="setting-group">
                    <label>Field Value</label>
                    <textarea name="embed_options[embeded][<?php echo $embed_index; ?>][fields][0][value]" class="widefat" rows="2"></textarea>
                    <p class="description">The content of this field (supports Markdown)</p>
                </div>
                <div class="setting-group">
                    <label>
                        <input type="checkbox" name="embed_options[embeded][<?php echo $embed_index; ?>][fields][0][inline]" value="1">
                        Inline
                    </label>
                    <p class="description">Display this field inline (side by side with other inline fields)</p>
                </div>
                <button type="button" class="add-field button">+ Add Field</button>
            </div>
        <?php
        return ob_get_clean();
    }
    
    private function render_embed_field($embed_index, $field_index, $field) {
        ob_start();
        ?>
        <div class="setting-group">
            <label>Field Name</label>
            <input type="text" 
                   name="embed_options[embeded][<?php echo $embed_index; ?>][fields][<?php echo $field_index; ?>][name]" 
                   value="<?php echo esc_attr(isset($field['name']) ? $field['name'] : ''); ?>" 
                   class="widefat">
            <p class="description">The title of this field</p>
        </div>
        <div class="setting-group">
            <label>Field Value</label>
            <textarea name="embed_options[embeded][<?php echo $embed_index; ?>][fields][<?php echo $field_index; ?>][value]" 
                      class="widefat" rows="2"><?php echo esc_textarea(isset($field['value']) ? $field['value'] : ''); ?></textarea>
            <p class="description">The content of this field (supports Markdown)</p>
        </div>
        <div class="setting-group">
            <label>
                <input type="checkbox" 
                       name="embed_options[embeded][<?php echo $embed_index; ?>][fields][<?php echo $field_index; ?>][inline]" 
                       value="1" <?php checked(isset($field['inline']) ? $field['inline'] : '' , true); ?>>
                Inline
            </label>
            <p class="description">Display this field inline</p>
        </div>
        <button type="button" class="remove-field button">Remove Field</button>
        <?php
        return ob_get_clean();
    }
    
    private function render_component_block($index, $comp_index, $component) {
        ob_start();
        ?>
        <input type="hidden" name="embed_options[embeded][<?php echo $index; ?>][components][<?php echo $comp_index; ?>][type]" value="2">
        <div class="setting-group">
            <label>Button Label</label>
            <input type="text" 
                   name="embed_options[embeded][<?php echo $index; ?>][components][<?php echo $comp_index; ?>][label]" 
                   value="<?php echo esc_attr($component['label']); ?>" 
                   class="widefat">
            <p class="description">Text that appears on the button</p>
        </div>
        <div class="setting-group">
            <label>Button URL</label>
            <input type="text" 
                   name="embed_options[embeded][<?php echo $index; ?>][components][<?php echo $comp_index; ?>][url]" 
                   value="<?php echo esc_attr($component['url']); ?>" 
                   class="widefat">
            <p class="description">URL the button will link to</p>
        </div>
        <div class="setting-group">
            <label>Emoji ID</label>
            <input type="text" 
                   name="embed_options[embeded][<?php echo $index; ?>][components][<?php echo $comp_index; ?>][emoji][id]" 
                   value="<?php echo esc_attr($component['emoji']['id']); ?>" 
                   class="widefat">
            <p class="description">Numeric ID of the emoji</p>
        </div>
        <div class="setting-group">
            <label>Emoji Name</label>
            <input type="text" 
                   name="embed_options[embeded][<?php echo $index; ?>][components][<?php echo $comp_index; ?>][emoji][name]" 
                   value="<?php echo esc_attr($component['emoji']['name']); ?>" 
                   class="widefat">
            <p class="description">Name of the emoji (e.g. "smile")</p>
        </div>
        <div class="setting-group">
            <label>
                <input type="checkbox" 
                       name="embed_options[embeded][<?php echo $index; ?>][components][<?php echo $comp_index; ?>][emoji][animated]" 
                       value="1" <?php checked($component['emoji']['animated'], true); ?>>
                Animated Emoji
            </label>
            <p class="description">Check if using an animated emoji</p>
        </div>
        <button type="button" class="remove-component button">Remove Button</button>
        <?php
        return ob_get_clean();
    }
  	
  	public function FormEmbeddedStyleComment() {
  	    $defaultsetarray = get_option(self::DEFAULT_SET_LIST_OPT, []);
        $connection_type = $defaultsetarray['connection_type'] ?? 'webhook';
        $embed_options = get_option(self::EMBEDDED_COMMENT_STRUCT_OPT, [
            'embeded' => [
                 [
                      'author' => ['name' => '', 'url' => ''],
                      'title' => '',
                      'description' => '',
                      'fields' => [],
                      'image' => ['url' => ''],
                      'color' => '',
                      'timestamp' => '',
                      'footer' => ['text' => ''],
                      'components' => []
                  ],
                   [
                        'author' => ['name' => '', 'url' => ''],
                        'title' => '',
                        'description' => '',
                        'fields' => [],
                        'image' => ['url' => ''],
                        'color' => '',
                        'timestamp' => '',
                        'footer' => ['text' => ''],
                        'components' => []
                    ]
            ]
        ]);
        if (count($embed_options['embeded']) === 1) {
          $embed_options = [
            'embeded' => [
                 [
                      'author' => ['name' => '', 'url' => ''],
                      'title' => '',
                      'description' => '',
                      'fields' => [],
                      'image' => ['url' => ''],
                      'color' => '',
                      'timestamp' => '',
                      'footer' => ['text' => ''],
                      'components' => []
                  ],
                   [
                        'author' => ['name' => '', 'url' => ''],
                        'title' => '',
                        'description' => '',
                        'fields' => [],
                        'image' => ['url' => ''],
                        'color' => '',
                        'timestamp' => '',
                        'footer' => ['text' => ''],
                        'components' => []
                    ]
            ]
        ];
        }
        
        settings_errors('wpdep_embed_comment_options_messages');
        ob_start();
        ?>
        <div class="wrap">
            <h1>Discord Embed Comment Style Settings</h1>
            <div class="wpdep-dashboard-widget">
              <form method="post">
                  <div class="embed-options-container">
                      <div class="embed-section">
                          <h2>Embed Settings</h2>
                          
                          <?php foreach ($embed_options['embeded'] as $index => $embed) : ?>
                              <div class="embed-block" data-index="<?php echo $index; ?>">
                                  <?php $this->render_embed_block($index, $embed, $connection_type, true); ?>
                              </div>
                          <?php endforeach; ?>
                      </div>
                  </div>
                  
                  <div class="form-actions">
                      <button type="submit" name="save_wpdep_embed_comment_options" class="button button-primary">Save Style Settings</button>
                  </div>
                  
                  <?php wp_nonce_field('save_wpdep_embed_comment_options_action', 'wpdep_embed_comment_options_nonce'); ?>
              </form>
            </div>
        </div>
        <?php
        echo ob_get_clean();
  	}
  	
  	public function wpdep_wp_enqueue($hook) {
        $plugin_pages = [
            $this->admin_pages['main']['menu_slug'], 
        ];
        
        foreach ( $this->admin_pages['submenus'] as $submenu_opt ) {
            $plugin_pages[] = $submenu_opt['menu_slug'];
        }
    
        $is_plugin_page = false;
        foreach ($plugin_pages as $page) {
            if ($hook === $page || strpos($hook, $page) !== false) {
                $is_plugin_page = true;
                break;
            }
        }
    
        if (!$is_plugin_page) {
            return;
        }
    
        if (!$this->admin_assets_loaded) {
            wp_enqueue_style(
                'wp-discord-embedded-post',
                WPDEP_PATH_URL . 'assets/css/admin.css',
                [],
                filemtime(WPDEP_PATH_DIR . '/assets/css/admin.css')
            );
    
            wp_enqueue_script(
                'wp-discord-embedded-post',
                WPDEP_PATH_URL . 'assets/js/admin.js',
                ['jquery'],
                filemtime(WPDEP_PATH_DIR . '/assets/js/admin.js'),
                true
            );
    
            $load_select2 = false;
            foreach ($this->admin_pages['submenus'] as $submenu) {
                if (!empty($submenu['requires_select2'])) {
                    $load_select2 = true;
                    break;
                }
            }
    
            if ($load_select2) {
                wp_enqueue_style(
                    'select2',
                    WPDEP_PATH_URL . 'assets/css/select2.min.css'
                );
                wp_enqueue_script(
                    'select2',
                    WPDEP_PATH_URL . 'assets/js/select2.min.js',
                    ['jquery'],
                    '4.1.0-rc.0',
                    true
                );
            }
    
            $this->admin_assets_loaded = true;
        }
    }
    public function meta_boxes() {
        add_meta_box(
            'wpdep_featured',        
            'Wpdep Settings',                        
            [$this, 'render_wpdep_featured'],           
            'post',                                
            'side',                              
            'core'                                 
        );
    }
    
    public function render_after_editor() {
        global $post;
        
        if ($post->post_type !== 'post') return;
        
        echo '<div id="wpdep_featured_position" style="margin-top:20px;">';
        $this->render_wpdep_featured($post);
        echo '</div>';
    }
    
    public function render_wpdep_featured($post) {
        $featured_image = get_post_meta($post->ID, '_wpdep_featured_image', true);
        $description = get_post_meta($post->ID, '_wpdep_custom_description', true); 
        
        wp_nonce_field('wpdep_featured_nonce_action', 'wpdep_featured_nonce');
        ?>
        <div style="padding: 10px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,0.04);">
            <p>
                <label for="wpdep_featured_image" style="display:block; margin-bottom:5px; font-weight:600;">
                    <?php _e('Insert Featuring Image URL:', 'textdomain'); ?>
                </label>
                <input type="url" 
                       id="wpdep_featured_image" 
                       name="wpdep_featured_image" 
                       value="<?php echo esc_url($featured_image); ?>" 
                       style="width: 100%; padding: 8px;"
                       placeholder="https://example.com">
            </p>
            <p>
                <label for="wpdep_custom_description" style="display:block; margin-bottom:5px; font-weight:600;">
                    <?php _e('Custom Description:', 'textdomain'); ?>
                </label>
                <textarea id="wpdep_custom_description" 
                          class="widefat" 
                          name="wpdep_custom_description"
                          style="width:100%; min-height:100px; padding:8px;"><?php 
                          echo esc_textarea($description); 
                ?></textarea>
            </p>
        </div>
        <?php
    }

    public function save_metabox($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['wpdep_featured_nonce']) || 
            !wp_verify_nonce($_POST['wpdep_featured_nonce'], 'wpdep_featured_nonce_action')) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) return;
        
        if (isset($_POST['wpdep_featured_image'])) {
            $sanitized_url = esc_url_raw($_POST['wpdep_featured_image']);
            update_post_meta($post_id, '_wpdep_featured_image', $sanitized_url);
        }
        if (isset($_POST['wpdep_custom_description'])) {
            $description = ($_POST['wpdep_custom_description']);
            update_post_meta($post_id, '_wpdep_custom_description', $description);
        }
    }
	
}