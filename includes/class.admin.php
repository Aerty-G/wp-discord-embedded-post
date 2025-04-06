<?php 

require_once('class.implements.php');

class WDEP_Admin implements WDEP_Const {
  
  public function __construct() {
    	  /* Silence is golden */ 
    	  add_action('admin_menu', array($this, 'Dashboard'));
        add_action('admin_init', array($this, 'SubmissionHandle'));
        add_action('admin_enqueue_scripts', array($this, 'wp_enqueue'));
	}
	
	public function Dashboard() {
	    add_menu_page(
          'WP Discord Embedded Post',         // Title yang ditampilkan di halaman pengaturan
          'WP Discord Post',         // Title yang ditampilkan di menu WordPress
          'manage_options',       // Kapabilitas yang dibutuhkan untuk melihat menu ini
          'wp-discord-embedded-post',    // Slug menu utama
          [$this,'FormMain'],    // Callback function untuk menampilkan halaman pengaturan utama (opsional)
          'dashicons-admin-generic', // Icon untuk menu utama
          60                       // Posisi menu di sidebar admin
      );
      
      add_submenu_page(
          'wp-discord-embedded-post',            // Slug menu utama
          'Default Settings',     // Title yang ditampilkan di halaman pengaturan
          'Default Settings',              // Title yang ditampilkan di menu WordPress
          'manage_options',               // Kapabilitas yang dibutuhkan untuk melihat menu ini
          'wpdep-default-settings',     // Slug submenu
          [$this,'FormDefaultSet'] // Callback function untuk menampilkan halaman pengaturan
      );
      
      add_submenu_page(
          'wp-discord-embedded-post',            // Slug menu utama
          'Variable Manager',     // Title yang ditampilkan di halaman pengaturan
          'Variable Manager',              // Title yang ditampilkan di menu WordPress
          'manage_options',               // Kapabilitas yang dibutuhkan untuk melihat menu ini
          'wpdep-var-manager',     // Slug submenu
          [$this,'FormVarManager'] // Callback function untuk menampilkan halaman pengaturan
      );
      
      add_submenu_page(
          'wp-discord-embedded-post',            // Slug menu utama
          'Embedded Style Manager',     // Title yang ditampilkan di halaman pengaturan
          'Embedded Style',              // Title yang ditampilkan di menu WordPress
          'manage_options',               // Kapabilitas yang dibutuhkan untuk melihat menu ini
          'wpdep-embedded-style-manager',     // Slug submenu
          [$this,'FormEmbeddedStyle'] // Callback function untuk menampilkan halaman pengaturan
      );
      
      add_submenu_page(
          'wp-discord-embedded-post',            // Slug menu utama
          'Post & Category Manager',     // Title yang ditampilkan di halaman pengaturan
          'Cat Manager',              // Title yang ditampilkan di menu WordPress
          'manage_options',               // Kapabilitas yang dibutuhkan untuk melihat menu ini
          'wpdep-post-cat-manager',     // Slug submenu
          [$this,'FormManager'] // Callback function untuk menampilkan halaman pengaturan
      );
	}
	
	public function SubmissionHandle() {
      // Handle Meta Options form submission
      if (isset($_POST['save_wpdep_var_options'])) {
          check_admin_referer('save_wpdep_var_options_action', 'wpdep_var_options_nonce');
          
          $options = array();
          
          if (isset($_POST['options'])) {
              foreach ($_POST['options'] as $option) {
                  if (!empty($option['title'])) {
                      $processed_option = array(
                          'title' => sanitize_text_field($option['title']),
                          'mode' => isset($option['mode']) ? sanitize_text_field($option['mode']) : 'single',
                          'keys' => array(),
                          'template' => isset($option['template']) ? sanitize_text_field($option['template']) : '',
                          'separator' => isset($option['separator']) ? sanitize_text_field($option['separator']) : ', '
                      );
                      
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
      
      // Handle Discord Settings form submission
      if (isset($_POST['save_wpdep_default_discord_settings'])) {
          check_admin_referer('save_wpdep_default_settings_action', 'wpdep_default_settings_nonce');
          
          $settings = array(
              'default_tag' => isset($_POST['default_discord_settings']['default_tag']) 
                  ? $_POST['default_discord_settings']['default_tag']
                  : '',
              'connection_type' => isset($_POST['default_discord_settings']['connection_type']) 
                  ? sanitize_text_field($_POST['default_discord_settings']['connection_type']) 
                  : 'webhook',
              'webhook_url' => isset($_POST['default_discord_settings']['webhook_url']) 
                  ? esc_url_raw($_POST['default_discord_settings']['webhook_url']) 
                  : '',
              'bot_token' => isset($_POST['default_discord_settings']['bot_token']) 
                  ? sanitize_text_field($_POST['default_discord_settings']['bot_token']) 
                  : '',
              'channel_id' => isset($_POST['default_discord_settings']['channel_id']) 
                  ? sanitize_text_field($_POST['default_discord_settings']['channel_id']) 
                  : ''
          );
          
          update_option(self::DEFAULT_SET_LIST_OPT, $settings);
          add_settings_error('wpdep_default_settings_messages', 'wpdep_default_settings_message', __('Default Discord settings saved successfully!', 'wp-discord-embedded-post'), 'success');
      }
      
      // Handle Embed Options form submission
      if (isset($_POST['save_wpdep_embed_options'])) {
          check_admin_referer('save_wpdep_embed_options_action', 'wpdep_embed_options_nonce');
          
          $embed_options = array(
              'embeded' => array(),
              'embeded_button' => array()
          );
          
          // Process embed settings
          if (isset($_POST['embed_options']['embeded'])) {
              foreach ($_POST['embed_options']['embeded'] as $embed) {
                  $processed_embed = array(
                      'author' => array(
                          'name' => isset($embed['author']['name']) ? $embed['author']['name'] : '',
                          'url' => isset($embed['author']['url']) ? esc_url_raw($embed['author']['url']) : ''
                      ),
                      'title' => isset($embed['title']) ? $embed['title'] : '',
                      'description' => isset($embed['description']) ? $embed['description'] : '',
                      'fields' => array(),
                      'image' => array(
                          'url' => isset($embed['image']['url']) ? esc_url_raw($embed['image']['url']) : ''
                      ),
                      'color' => isset($embed['color']) ? sanitize_hex_color($embed['color']) : '',
                      'timestamp' => isset($embed['timestamp']) ? $embed['timestamp'] : '',
                      'footer' => array(
                          'text' => isset($embed['footer']['text']) ? $embed['footer']['text'] : ''
                      )
                  );
                  
                  // Process fields
                  if (isset($embed['fields'])) {
                      foreach ($embed['fields'] as $field) {
                          $processed_embed['fields'][] = array(
                              'name' => isset($field['name']) ? $field['name'] : '',
                              'value' => isset($field['value']) ? $field['value'] : '',
                              'inline' => isset($field['inline']) ? (bool)$field['inline'] : false
                          );
                      }
                  }
                  
                  $embed_options['embeded'][] = $processed_embed;
              }
          }
          
              if (isset($_POST['embed_options']['embeded_button'])) {
                  foreach ($_POST['embed_options']['embeded_button'] as $button_set) {
                      $processed_button_set = array(
                          'type' => 1,
                          'components' => array()
                      );
                      
                      // Process components (max 4)
                      if (isset($button_set['components'])) {
                          $component_count = 0;
                          foreach ($button_set['components'] as $component) {
                              if ($component_count >= 4) break;
                              
                              $processed_button_set['components'][] = array(
                                  'type' => 2,
                                  'style' => 5,
                                  'label' => isset($component['label']) ? $component['label'] : '',
                                  'url' => isset($component['url']) ? esc_url_raw($component['url']) : '',
                                  'emoji' => array(
                                      'id' => isset($component['emoji']['id']) ? $component['emoji']['id'] : '',
                                      'name' => isset($component['emoji']['name']) ? $component['emoji']['name'] : '',
                                      'animated' => isset($component['emoji']['animated']) ? (bool)$component['emoji']['animated'] : false
                                  )
                              );
                              $component_count++;
                          }
                      }
                      
                      $embed_options['embeded_button'][] = $processed_button_set;
                  }
              }
          
          
          update_option(self::EMBEDDED_STRUCT_LIST_OPT, $embed_options);
          add_settings_error('wpdep_embed_options_messages', 'wpdep_embed_options_message', __('Embed settings saved successfully!', 'wp-discord-embedded-post'), 'success');
      }
      
      // Handle Category Options form submission
      if (isset($_POST['save_wpdep_category_options'])) {
              check_admin_referer('save_wpdep_category_options_action', 'wpdep_category_options_nonce');
              
              $category_options = array();
              
              if (isset($_POST['category_options'])) {
                  foreach ($_POST['category_options'] as $option) {
                      $cat_ids = isset($option['cat_ids']) ? array_map('absint', $option['cat_ids']) : array();
                      
                      $processed_option = array(
                          'cat_ids' => $cat_ids,
                          'selected_embedded_style' => isset($option['selected_embedded_style']) 
                              ? sanitize_text_field($option['selected_embedded_style']) 
                              : '',
                          'main_message' => isset($option['main_message']) 
                              ? $option['main_message']
                              : '',
                          'channel_id' => isset($option['channel_id']) 
                              ? sanitize_text_field($option['channel_id']) 
                              : '',
                          'bot_token' => isset($option['bot_token']) 
                              ? sanitize_text_field($option['bot_token']) 
                              : '',
                          'webhook_url' => isset($option['webhook_url']) 
                              ? esc_url_raw($option['webhook_url']) 
                              : ''
                      );
                      
                      // Only save if at least one category is selected
                      if (!empty($processed_option['cat_ids'])) {
                          $category_options[] = $processed_option;
                      }
                  }
              }
              
              // Ensure at least one empty option if all were removed
              if (empty($category_options)) {
                  $category_options[] = array(
                      'cat_ids' => array(),
                      'selected_embedded_style' => '',
                      'channel_id' => '',
                      'bot_token' => '',
                      'webhook_url' => ''
                  );
              }
              
              update_option(self::CATEGORY_SELECTED_SET_OPT, $category_options);
              add_settings_error('wpdep_category_options_messages', 'wpdep_category_options_message', __('Category options saved successfully!', 'wp-discord-embedded-post'), 'success');
          }
	}
	
	public function FormMain() {
	      return $this->FormDefaultSet();
	}
	
	public function FormDefaultSet() {
	      $defaultsetarray = get_option(self::DEFAULT_SET_LIST_OPT, array());
        $connection_type = isset($defaultsetarray['connection_type']) ? $defaultsetarray['connection_type'] : 'webhook';
        $default_tag = isset($defaultsetarray['default_tag']) ? $defaultsetarray['default_tag'] : '';
        $webhook_url = isset($defaultsetarray['webhook_url']) ? $defaultsetarray['webhook_url'] : '';
        $botToken = isset($defaultsetarray['bot_token']) ? $defaultsetarray['bot_token'] : '';
        $channelId = isset($defaultsetarray['channel_id']) ? $defaultsetarray['channel_id'] : '';
        
        settings_errors('wpdep_default_settings_messages');
        ?>
        <div class="wrap">
            <h1>Default Settings</h1>
            
            <div class="wpdep-dashboard-widget">
                <form method="post">
                    <div class="discord-settings-container">
                        <!-- Universal Settings -->
                        <div class="setting-group">
                            <label>Default Tag</label>
                            <input type="text" 
                                   name="default_discord_settings[default_tag]" 
                                   value="<?php echo esc_attr($default_tag); ?>" 
                                   class="widefat" 
                                   placeholder="@here or @everyone">
                            <p class="description">This tag will be used for all notifications (works with both Webhook and Bot)</p>
                        </div>
    
                        <div class="setting-group">
                            <label>Connection Type:</label>
                            <div class="radio-options">
                                <label>
                                    <input type="radio" name="default_discord_settings[connection_type]" 
                                           value="webhook" <?php checked($connection_type, 'webhook'); ?>>
                                    Webhook
                                </label>
                                <label>
                                    <input type="radio" name="default_discord_settings[connection_type]" 
                                           value="bot" <?php checked($connection_type, 'bot'); ?>>
                                    Bot
                                </label>
                            </div>
                        </div>
    
                        <!-- Webhook Settings -->
                        <div id="webhook-settings" class="connection-settings" style="<?php echo ($connection_type !== 'webhook') ? 'display:none;' : ''; ?>">
                            <div class="setting-group">
                                <label for="discord_webhook_url">Webhook URL</label>
                                <input type="text" 
                                       id="discord_webhook_url" 
                                       name="default_discord_settings[webhook_url]" 
                                       value="<?php echo esc_attr($webhook_url); ?>" 
                                       class="widefat"
                                       placeholder="https://discord.com/api/webhooks/...">
                                <p class="description">The Discord webhook URL for sending messages</p>
                            </div>
                        </div>
    
                        <!-- Bot Settings -->
                        <div id="bot-settings" class="connection-settings" style="<?php echo ($connection_type !== 'bot') ? 'display:none;' : ''; ?>">
                            <div class="setting-group">
                                <label for="discord_bot_token">Bot Token</label>
                                <input type="text" 
                                       id="discord_bot_token" 
                                       name="default_discord_settings[bot_token]" 
                                       value="<?php echo esc_attr($botToken); ?>" 
                                       class="widefat"
                                       placeholder="Bot token from Discord Developer Portal">
                                <p class="description">Your Discord bot token (keep this secure)</p>
                            </div>
                            
                            <div class="setting-group">
                                <label for="discord_channel_id">Channel ID</label>
                                <input type="text" 
                                       id="discord_channel_id" 
                                       name="default_discord_settings[channel_id]" 
                                       value="<?php echo esc_attr($channelId); ?>" 
                                       class="widefat"
                                       placeholder="Target channel ID">
                                <p class="description">The channel ID where messages should be sent</p>
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
	}
	
	public function FormManager() {
        $category_options = get_option(self::CATEGORY_SELECTED_SET_OPT, array(
            array(
                'cat_ids' => array(),
                'selected_embedded_style' => '',
                'main_message' => '',
                'channel_id' => '',
                'bot_token' => '',
                'webhook_url' => ''
            )
        ));
        
        // Get all categories for dropdown
        $categories = get_categories(array(
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        // Get available embed styles
        $embed_options = get_option(self::EMBEDDED_STRUCT_LIST_OPT, array());
        $embed_styles = array();
        if (!empty($embed_options['embeded'])) {
            foreach ($embed_options['embeded'] as $index => $embed) {
                $title = !empty($embed['title']) ? $embed['title'] : __('Untitled Embed', 'meta-options-manager');
                $embed_styles[$index] = $title . ' (#' . ($index + 1) . ')';
            }
        }
        
        settings_errors('wpdep_category_options_messages');
        ?>
        <div class="wrap">
            <h1>Post & Category Manager</h1>
            
            <div class="custom-dashboard-widget">
                <form method="post" id="category-options-form">
                    <div id="category-options-container">
                        <?php foreach ($category_options as $index => $option) : ?>
                            <div class="category-option-block" data-index="<?php echo $index; ?>">
                                <div class="option-header">
                                    <h3>Category Setting #<?php echo ($index + 1); ?></h3>
                                    <button type="button" class="remove-category-option button">Remove</button>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Categories</label>
                                    <select name="category_options[<?php echo $index; ?>][cat_ids][]" class="widefat category-select" multiple="multiple">
                                        <?php foreach ($categories as $category) : ?>
                                            <option value="<?php echo $category->term_id; ?>" 
                                                <?php selected(in_array($category->term_id, (array)$option['cat_ids']), true); ?>>
                                                <?php echo esc_html($category->name); ?> (ID: <?php echo $category->term_id; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">Select one or more categories</p>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Embedded Style</label>
                                    <select name="category_options[<?php echo $index; ?>][selected_embedded_style]" class="widefat embed-style-select">
                                        <option value="">— Default Style —</option>
                                        <?php foreach ($embed_styles as $style_index => $style_name) : ?>
                                            <option value="<?php echo $style_index; ?>" 
                                                <?php selected($option['selected_embedded_style'], $style_index); ?>>
                                                <?php echo esc_html($style_name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">Choose the embed style for these categories</p>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Main Messages (optional)</label>
                                    <input type="text" 
                                           name="category_options[<?php echo $index; ?>][main_message]" 
                                           value="<?php echo esc_attr($option['main_message']); ?>" 
                                           class="widefat" 
                                           placeholder="Hello There, Our Category Post Has An Update!">
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
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" id="add-category-option" class="button button-primary">Add Category Option</button>
                        <button type="submit" name="save_wpdep_category_options" class="button button-primary">Save Settings</button>
                    </div>
                    
                    <?php wp_nonce_field('save_wpdep_category_options_action', 'wpdep_category_options_nonce'); ?>
                </form>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Initialize Select2 for category selects
            $('.category-select').select2({
                placeholder: "Select categories...",
                allowClear: true,
                width: '100%'
            });
            
            // Initialize Select2 for embed style selects
            $('.embed-style-select').select2({
                placeholder: "Select an embed style...",
                allowClear: true,
                width: '100%'
            });
        });
        </script>
        <?php
	}
	
	public function FormVarManager() {
	      $saved_options = get_option(self::EMBEDDED_VAR_LIST_OPT, array());
        settings_errors('wpdep_var_options_messages');
        ?>
        <div class="wrap">
            <h1>Manage Variable Options</h1>
            
            <div class="wpdep-dashboard-widget">
                <form id="var-form">
                    <div id="option-blocks-container">
                        <?php if (empty($saved_options)) : ?>
                            <div class="option-block" data-index="0">
                                <div class="option-header">
                                    <label>Title: 
                                        <input type="text" name="options[0][title]" placeholder="Section Title" class="widefat" value="">
                                    </label>
                                        <div class="mode-selector">
                                        <label>
                                              <input type="radio" name="options[0][mode]" 
                                                     value="single" checked>
                                              <span class="radio-custom"></span>
                                              <span class="radio-label">Single</span>
                                          </label>
                                          <label>
                                              <input type="radio" name="options[0][mode]" 
                                                     value="combine" >
                                              <span class="radio-custom"></span>
                                              <span class="radio-label">Combine</span>
                                          </label>
                                          <label>
                                              <input type="radio" name="options[0][mode]" 
                                                     value="connect">
                                              <span class="radio-custom"></span>
                                              <span class="radio-label">Connect</span>
                                          </label>
                                          <button type="button" class="remove-option button">Remove</button>
                                    </div>
                                    
                                </div>
                                
                                <div class="separator-group" style="display:none;">
                                    <label>Separator: 
                                        <input type="text" name="options[0][separator]" value=", " class="widefat">
                                    </label>
                                </div>
                                
                                <div class="keys-container">
                                    <div class="key-input">
                                        <input type="text" name="options[0][keys][]" placeholder="meta_key" class="widefat">
                                        <button type="button" class="add-key button">+</button>
                                    </div>
                                </div>
                                
                                <div class="template-group">
                                    <label>Output Template: 
                                        <input type="text" name="options[0][template]" class="widefat" 
                                               placeholder="E.g.: Post Title: ${title_post}$" value="">
                                    </label>
                                    <p class="description">Use ${meta_key}$ to insert meta values</p>
                                </div>
                                <div class="information">
                                  <label>More Information: 
                                  <p class="description">
                                    Single: Meta Value From First Meta Key Will Became Output Of The Variable.<br>
                                    Combine: All Meta Key In The Meta Key Section Will Be Retrieved And Combined With The Separator You Gift It.<br>
                                    Connet: The Value Of First Meta Key Will Became Post ID of The Second Meta Key, Only If The Value Of The First Meta Key Was Integer.<br>
                                  </p></label>
                                </div>
                            </div>
                        <?php else : ?>
                            <?php foreach ($saved_options as $index => $option) : ?>
                                <div class="option-block" data-index="<?php echo $index; ?>">
                                    <div class="option-header">
                                        <label>Title: 
                                            <input type="text" name="options[<?php echo $index; ?>][title]" 
                                                   value="<?php echo esc_attr($option['title']); ?>" 
                                                   placeholder="Section Title" class="widefat">
                                        </label>
                                        <div class="mode-selector">
                                          <label>
                                              <input type="radio" name="options[<?php echo $index; ?>][mode]" 
                                                     value="single" <?php checked($option['mode'] ?? 'single', 'single'); ?>>
                                              <span class="radio-custom"></span>
                                              <span class="radio-label">Single</span>
                                          </label>
                                          <label>
                                              <input type="radio" name="options[<?php echo $index; ?>][mode]" 
                                                     value="combine" <?php checked($option['mode'] ?? '', 'combine'); ?>>
                                              <span class="radio-custom"></span>
                                              <span class="radio-label">Combine</span>
                                          </label>
                                          <label>
                                              <input type="radio" name="options[<?php echo $index; ?>][mode]" 
                                                     value="connect" <?php checked($option['mode'] ?? '', 'connect'); ?>>
                                              <span class="radio-custom"></span>
                                              <span class="radio-label">Connect</span>
                                          </label>
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
                                                <button type="button" class="remove-key button">-</button>
                                                <?php if ($key_index === 0) : ?>
                                                    <button type="button" class="add-key button">+</button>
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
                                  <div class="information">
                                  <label>More Information: 
                                  <p class="description">
                                    Single: Meta Value From First Meta Key Will Became Output Of The Variable.<br>
                                    Combine: All Meta Key In The Meta Key Section Will Be Retrieved And Combined With The Separator You Gift It.<br>
                                    Connet: The Value Of First Meta Key Will Became Post ID of The Second Meta Key, Only If The Value Of The First Meta Key Was Integer.<br>
                                  </p></label>
                                </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" id="add-option" class="button button-primary">Add New Option</button>
                        <button type="submit" name="save_wpdep_var_options" class="button button-primary">Save Options</button>
                    </div>
                </form>
                <?php wp_nonce_field('save_wpdep_var_options_action', 'wpdep_var_options_nonce'); ?>
            </div>
        </div>
        <?php
	}
	
	public function FormEmbeddedStyle() {
        $embed_options = get_option(self::EMBEDDED_STRUCT_LIST_OPT, array(
            'embeded' => array(
                array(
                    'author' => array('name' => '', 'url' => ''),
                    'title' => '',
                    'description' => "",
                    'fields' => array(),
                    'image' => array('url' => ''),
                    'color' => '',
                    'timestamp' => '',
                    'footer' => array('text' => '')
                )
            ),
            'embeded_button' => array(
                array(
                    'type' => 1,
                    'components' => array()
                )
            )
        ));
        
        settings_errors('wpdep_embed_options_messages');
        ?>
        <div class="wrap">
            <h1>Discord Embed Style Settings</h1>
            
            <div class="wpdep-dashboard-widget">
                <form method="post">
                    <div class="embed-options-container">
                        <!-- Embed Settings -->
                        <div class="embed-section">
                            <h2>Embed Settings</h2>
                            
                            <div class="embed-block" data-index="0">
                                <div class="option-header">
                                    <h3>Embed #1</h3>
                                    <button type="button" class="remove-embed button">Remove</button>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Author Name</label>
                                    <input type="text" name="embed_options[embeded][0][author][name]" 
                                           value="<?php echo esc_attr($embed_options['embeded'][0]['author']['name']); ?>" 
                                           class="widefat">
                                    <p class="description">The name that appears as the author of the embed</p>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Author URL</label>
                                    <input type="text" name="embed_options[embeded][0][author][url]" 
                                           value="<?php echo esc_attr($embed_options['embeded'][0]['author']['url']); ?>" 
                                           class="widefat">
                                    <p class="description">URL that the author name will link to (optional)</p>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Title</label>
                                    <input type="text" name="embed_options[embeded][0][title]" 
                                           value="<?php echo esc_attr($embed_options['embeded'][0]['title']); ?>" 
                                           class="widefat">
                                    <p class="description">The main title of your embed (appears in bold at the top)</p>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Description</label>
                                    <textarea name="embed_options[embeded][0][description]" class="widefat" rows="3"><?php 
                                        echo esc_textarea($embed_options['embeded'][0]['description']); 
                                    ?></textarea>
                                    <p class="description">The main content of your embed (supports Markdown formatting)</p>
                                </div>
                                
                                <!-- Fields -->
                                <div class="fields-container">
                                    <h4>Fields</h4>
                                    <p class="description">Add key-value pairs to display in your embed</p>
                                    <?php if (!empty($embed_options['embeded'][0]['fields'])) : ?>
                                        <?php foreach ($embed_options['embeded'][0]['fields'] as $field_index => $field) : ?>
                                            <div class="field-group" data-index="<?php echo $field_index; ?>">
                                                <div class="setting-group">
                                                    <label>Field Name</label>
                                                    <input type="text" 
                                                           name="embed_options[embeded][0][fields][<?php echo $field_index; ?>][name]" 
                                                           value="<?php echo esc_attr($field['name']); ?>" 
                                                           class="widefat">
                                                    <p class="description">The title of this field</p>
                                                </div>
                                                <div class="setting-group">
                                                    <label>Field Value</label>
                                                    <textarea name="embed_options[embeded][0][fields][<?php echo $field_index; ?>][value]" 
                                                              class="widefat" rows="2"><?php echo esc_textarea($field['value']); ?></textarea>
                                                    <p class="description">The content of this field (supports Markdown)</p>
                                                </div>
                                                <div class="setting-group">
                                                    <label>
                                                        <input type="checkbox" 
                                                               name="embed_options[embeded][0][fields][<?php echo $field_index; ?>][inline]" 
                                                               value="1" <?php checked($field['inline'], true); ?>>
                                                        Inline
                                                    </label>
                                                    <p class="description">Display this field inline (side by side with other inline fields)</p>
                                                </div>
                                                <button type="button" class="remove-field button">Remove Field</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="add-field button">Add Field</button>
                                
                                <div class="setting-group">
                                    <label>Image URL</label>
                                    <input type="text" name="embed_options[embeded][0][image][url]" 
                                           value="<?php echo esc_attr($embed_options['embeded'][0]['image']['url']); ?>" 
                                           class="widefat">
                                    <p class="description">URL of an image to display at the bottom of the embed</p>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Color (hex)</label>
                                    <input type="text" name="embed_options[embeded][0][color]" 
                                           value="<?php echo esc_attr($embed_options['embeded'][0]['color']); ?>" 
                                           class="widefat" placeholder="#FFFFFF">
                                    <p class="description">The color of the embed border (hex format, e.g. #FF0000 for red)</p>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Timestamp</label>
                                    <input type="text" name="embed_options[embeded][0][timestamp]" 
                                           value="<?php echo esc_attr($embed_options['embeded'][0]['timestamp']); ?>" 
                                           class="widefat" placeholder="Leave empty for current time">
                                    <p class="description">ISO8601 timestamp (e.g. 2023-01-01T00:00:00.000Z) or leave empty for current time</p>
                                </div>
                                
                                <div class="setting-group">
                                    <label>Footer Text</label>
                                    <input type="text" name="embed_options[embeded][0][footer][text]" 
                                           value="<?php echo esc_attr($embed_options['embeded'][0]['footer']['text']); ?>" 
                                           class="widefat">
                                    <p class="description">Text to display in the footer of the embed</p>
                                </div>
                            </div>
                            <button type="button" class="add-embed button">Add Another Embed</button>
                        </div>
                        
                        <!-- Button Components -->
                        <div id="button-components-section" class="embed-section">
                            <h2>Button Components</h2>
                            <p class="section-description">These buttons will appear below your embed message (max 4 buttons allowed)</p>
                            
                            <div class="components-container">
                                <?php if (!empty($embed_options['embeded_button'][0]['components'])) : ?>
                                    <?php foreach ($embed_options['embeded_button'][0]['components'] as $comp_index => $component) : ?>
                                        <?php if ($comp_index < 4) : ?>
                                            <div class="component-group" data-index="<?php echo $comp_index; ?>">
                                                <div class="setting-group">
                                                    <label>Button Label</label>
                                                    <input type="text" 
                                                           name="embed_options[embeded_button][0][components][<?php echo $comp_index; ?>][label]" 
                                                           value="<?php echo esc_attr($component['label']); ?>" 
                                                           class="widefat">
                                                    <p class="description">Text that appears on the button</p>
                                                </div>
                                                <div class="setting-group">
                                                    <label>Button URL</label>
                                                    <input type="text" 
                                                           name="embed_options[embeded_button][0][components][<?php echo $comp_index; ?>][url]" 
                                                           value="<?php echo esc_attr($component['url']); ?>" 
                                                           class="widefat">
                                                    <p class="description">URL the button will link to</p>
                                                </div>
                                                <div class="setting-group">
                                                    <label>Emoji ID</label>
                                                    <input type="text" 
                                                           name="embed_options[embeded_button][0][components][<?php echo $comp_index; ?>][emoji][id]" 
                                                           value="<?php echo esc_attr($component['emoji']['id']); ?>" 
                                                           class="widefat">
                                                    <p class="description">Numeric ID of the emoji (available in Discord via \:emoji:)</p>
                                                </div>
                                                <div class="setting-group">
                                                    <label>Emoji Name</label>
                                                    <input type="text" 
                                                           name="embed_options[embeded_button][0][components][<?php echo $comp_index; ?>][emoji][name]" 
                                                           value="<?php echo esc_attr($component['emoji']['name']); ?>" 
                                                           class="widefat">
                                                    <p class="description">Name of the emoji (e.g. "smile")</p>
                                                </div>
                                                <div class="setting-group">
                                                    <label>
                                                        <input type="checkbox" 
                                                               name="embed_options[embeded_button][0][components][<?php echo $comp_index; ?>][emoji][animated]" 
                                                               value="1" <?php checked($component['emoji']['animated'], true); ?>>
                                                        Animated Emoji
                                                    </label>
                                                    <p class="description">Check if using an animated emoji</p>
                                                </div>
                                                <button type="button" class="remove-component button">Remove Button</button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="add-component button">Add Button</button>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="save_wpdep_embed_options" class="button button-primary">Save Embed Settings</button>
                    </div>
                    
                    <?php wp_nonce_field('save_wpdep_embed_options_action', 'wpdep_embed_options_nonce'); ?>
                </form>
            </div>
        </div>
        <?php
	}
	
	public function wp_enqueue( $hook ) {
        wp_enqueue_script(
            'wp-discord-embedded-post',
            plugins_url('assets/js/admin.js', __FILE__),
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_enqueue_style(
            'wp-discord-embedded-post',
            plugins_url('assets/css/admin.css', __FILE__),
            array(),
            '1.0.0'
        );
            wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
            wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'));
	}
	
	
}