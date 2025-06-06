jQuery(document).ready(function($) {
    // ======================
    // Var Options Management
    // ======================
    const manageOptionBlocks = {
        init() {
            $('#add-option').on('click', this.addOptionBlock);
            $(document).on('click', '.remove-option', this.removeOptionBlock);
            $(document).on('click', '.add-key', this.addKeyField);
            $(document).on('click', '.remove-key', this.removeKeyField);
            $(document).on('change', 'input[name$="[mode]"]', this.toggleSeparatorField);
        },
        
        toggleSeparatorField() {
            const separatorGroup = $(this).closest('.option-block').find('.separator-group');
            separatorGroup.toggle($(this).val() === 'combine');
        },
        
        addOptionBlock() {
            const index = Date.now();
            const newBlock = $(`
                <div class="option-block" data-index="${index}">
                    <div class="option-header">
                        <label>Title: 
                            <input type="text" name="options[${index}][title]" placeholder="Section Title" class="widefat">
                        </label>
                        <div class="mode-selector">
                            <label>
                                <input type="radio" name="options[${index}][mode]" value="single" checked>
                                <span class="radio-custom"></span>
                                <span class="radio-label">Single</span>
                            </label>
                            <label>
                                <input type="radio" name="options[${index}][mode]" value="combine">
                                <span class="radio-custom"></span>
                                <span class="radio-label">Combine</span>
                            </label>
                            <label>
                                <input type="radio" name="options[${index}][mode]" value="connect">
                                <span class="radio-custom"></span>
                                <span class="radio-label">Connect</span>
                            </label>
                            <button type="button" class="remove-option button">Remove</button>
                        </div>
                    </div>
                    
                    <div class="separator-group" style="display:none;">
                        <label>Separator: 
                            <input type="text" name="options[${index}][separator]" value=", " class="widefat">
                        </label>
                        <p class="description">Character(s) to use when combining multiple values</p>
                    </div>
                    
                    <div class="keys-container">
                        <div class="key-input">
                            <input type="text" name="options[${index}][keys][]" placeholder="meta_key" class="widefat">
                            <button type="button" class="add-key button">+</button>
                        </div>
                        <p class="description">Add the meta keys you want to retrieve</p>
                    </div>
                    
                    <div class="template-group">
                        <label>Template Name: 
                            <input type="text" name="options[${index}][template]" class="widefat" placeholder="E.g.: author_info">
                        </label>
                        <p class="description">Use this name as placeholder (e.g. \${author_info}$) in your embed fields</p>
                    </div>
                    
                    <div class="information">
                        <label>More Information: 
                        <p class="description">
                            Single: Uses the first meta key's value<br>
                            Combine: Joins all values with separator<br>
                            Connect: Uses first value as post ID to get second value
                        </p></label>
                    </div>
                </div>
            `);
            $('#option-blocks-container').append(newBlock);
        },
        
        removeOptionBlock() {
            if ($('.option-block').length > 1) {
                $(this).closest('.option-block').remove();
            } else {
                alert('You must have at least one option block.');
            }
        },
        
        addKeyField() {
            const container = $(this).closest('.keys-container');
            const index = $(this).closest('.option-block').data('index');
            container.append(`
                <div class="key-input">
                    <input type="text" name="options[${index}][keys][]" placeholder="meta_key" class="widefat">
                    <button type="button" class="remove-key button">-</button>
                </div>
            `);
        },
        
        removeKeyField() {
            if ($(this).closest('.keys-container').find('.key-input').length > 1) {
                $(this).closest('.key-input').remove();
            } else {
                alert('You must have at least one meta key.');
            }
        }
    };

    // ======================
    // Discord Connection Type
    // ======================
    const discordConnection = {
        init() {
            this.toggleConnectionSettings();
            $('input[name="default_discord_settings[connection_type]"]').on('change', () => {
                this.toggleConnectionSettings();
                embedManager.updateButtonNotices();
            });
        },
        
        toggleConnectionSettings() {
            const connectionType = $('input[name="default_discord_settings[connection_type]"]:checked').val();
            $('.connection-settings').hide();
            $(`#${connectionType}-settings`).show();
        }
    };

    // ======================
    // Embed Management
    // ======================
    const embedManager = {
        init() {
            $('.add-embed').on('click', this.addEmbed);
            $(document).on('click', '.remove-embed', this.removeEmbed);
            $(document).on('click', '.add-field', this.addField);
            $(document).on('click', '.remove-field', this.removeField);
            $(document).on('click', '.add-component', this.addComponent);
            $(document).on('click', '.remove-component', this.removeComponent);
        },
        
        addEmbed() {
            const container = $('.embed-section');
            const index = $('.embed-block').length;
            
            const newEmbed = $(`
                <div class="embed-block" data-index="${index}">
                    <div class="option-header">
                        <h3>Embed #${index + 1}</h3>
                        <button type="button" class="remove-embed button">Remove</button>
                    </div>
                    
                    <!-- Author Section -->
                    <div class="setting-group">
                        <label>Author Name</label>
                        <input type="text" name="embed_options[embeded][${index}][author][name]" class="widefat">
                        <p class="description">The name that appears as the author of the embed</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Author URL</label>
                        <input type="text" name="embed_options[embeded][${index}][author][url]" class="widefat">
                        <p class="description">URL that the author name will link to (optional)</p>
                    </div>
                    
                    <!-- Title Section -->
                    <div class="setting-group">
                        <label>Title</label>
                        <input type="text" name="embed_options[embeded][${index}][title]" class="widefat">
                        <p class="description">The main title of your embed (appears in bold at the top)</p>
                    </div>
                    
                    <!-- Description Section -->
                    <div class="setting-group">
                        <label>Description</label>
                        <textarea name="embed_options[embeded][${index}][description]" class="widefat" rows="3"></textarea>
                        <p class="description">The main content of your embed (supports Markdown formatting)</p>
                    </div>
                    
                    <!-- Fields Section -->
                    <div class="fields-container">
                        <h4>Fields</h4>
                        <p class="description">Add key-value pairs to display in your embed</p>
                        <div class="field-group" data-index="0">
                            <div class="setting-group">
                                <label>Field Name</label>
                                <input type="text" name="embed_options[embeded][${index}][fields][0][name]" class="widefat">
                                <p class="description">The title of this field</p>
                            </div>
                            <div class="setting-group">
                                <label>Field Value</label>
                                <textarea name="embed_options[embeded][${index}][fields][0][value]" class="widefat" rows="2"></textarea>
                                <p class="description">The content of this field (supports Markdown)</p>
                            </div>
                            <div class="setting-group">
                                <label>
                                    <input type="checkbox" name="embed_options[embeded][${index}][fields][0][inline]" value="1">
                                    Inline
                                </label>
                                <p class="description">Display this field inline (side by side with other inline fields)</p>
                            </div>
                            <button type="button" class="add-field button">+ Add Field</button>
                        </div>
                    </div>
                    
                    <!-- Image Section -->
                    <div class="setting-group">
                        <label>Image URL</label>
                        <input type="text" name="embed_options[embeded][${index}][image][url]" class="widefat">
                        <p class="description">URL of an image to display at the bottom of the embed</p>
                    </div>
                    
                    <!-- Color Section -->
                    <div class="setting-group">
                        <label>Color (hex)</label>
                        <input type="text" name="embed_options[embeded][${index}][color]" class="widefat" placeholder="#FFFFFF">
                        <p class="description">The color of the embed border (hex format, e.g. #FF0000 for red)</p>
                    </div>
                    
                    <!-- Timestamp Section -->
                    <div class="setting-group">
                        <label>Timestamp</label>
                        <input type="text" name="embed_options[embeded][${index}][timestamp]" class="widefat" placeholder="Leave empty for current time">
                        <p class="description">ISO8601 timestamp (e.g. 2023-01-01T00:00:00.000Z) or leave empty for current time</p>
                    </div>
                    
                    <!-- Footer Section -->
                    <div class="setting-group">
                        <label>Footer Text</label>
                        <input type="text" name="embed_options[embeded][${index}][footer][text]" class="widefat">
                        <p class="description">Text to display in the footer of the embed</p>
                    </div>
                    
                    <!-- Button Components Section -->
                    <div class="components-section">
                        <h4>Button Components</h4>
                        <p class="section-description">
                            These buttons will appear below this embed (max 4 buttons allowed).
                            <span class="components-notice" style="color:#d63638; display:none;">
                                Note: Buttons only work with Bot connection type.
                            </span>
                        </p>
                        <div class="components-container"></div>
                        <button type="button" data-index="${index}" class="add-component button">+ Add Button</button>
                    </div>
                </div>
            `);
            
            container.append(newEmbed);
            this.updateButtonNotices();
        },
        
        removeEmbed() {
            if ($('.embed-block').length > 1) {
                $(this).closest('.embed-block').remove();
                embedManager.updateEmbedIndexes();
            } else {
                alert('You must have at least one embed.');
            }
        },
        
        updateEmbedIndexes() {
            $('.embed-block').each(function(index) {
                $(this).attr('data-index', index)
                       .find('h3').text(`Embed #${index + 1}`);
                
                // Update all input names with new index
                $(this).find('[name^="embed_options"]').each(function() {
                    const newName = $(this).attr('name')
                        .replace(/embed_options\[embeded\]\[\d+\]/, `embed_options[embeded][${index}]`);
                    $(this).attr('name', newName);
                });
            });
        },
        
        addField() {
            const embedIndex = $(this).closest('.embed-block').data('index');
            const container = $(this).closest('.fields-container');
            const fieldIndex = container.find('.field-group').length;
            if (fieldIndex >= 8) {
                alert('Maximum of 8 field allowed per embed.');
                return;
            }
            
            container.append(`
                <div class="field-group" data-index="${fieldIndex}">
                    <div class="setting-group">
                        <label>Field Name</label>
                        <input type="text" name="embed_options[embeded][${embedIndex}][fields][${fieldIndex}][name]" class="widefat">
                        <p class="description">The title of this field</p>
                    </div>
                    <div class="setting-group">
                        <label>Field Value</label>
                        <textarea name="embed_options[embeded][${embedIndex}][fields][${fieldIndex}][value]" class="widefat" rows="2"></textarea>
                        <p class="description">The content of this field (supports Markdown)</p>
                    </div>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" name="embed_options[embeded][${embedIndex}][fields][${fieldIndex}][inline]" value="1">
                            Inline
                        </label>
                        <p class="description">Display this field inline (side by side with other inline fields)</p>
                    </div>
                    <button type="button" class="remove-field button">- Remove Field</button>
                </div>
            `);
        },
        
        removeField() {
            if ($(this).closest('.fields-container').find('.field-group').length > 1) {
                $(this).closest('.field-group').remove();
            } else {
                alert('You must have at least one field.');
            }
        },
        
        addComponent() {
            const embedIndex = $(this).data('index');
            const container = $(this).prev('.components-container');
            
            if (container.find('.component-group').length >= 4) {
                alert('Maximum of 4 buttons allowed per embed.');
                return;
            }
            
            const compIndex = container.find('.component-group').length;
            
            container.append(`
                <div class="component-group" data-index="${compIndex}">
                    <input type="hidden" name="embed_options[embeded][${embedIndex}][components][${compIndex}][type]" value="2">
                    
                    <div class="setting-group">
                        <label>Button Label</label>
                        <input type="text" name="embed_options[embeded][${embedIndex}][components][${compIndex}][label]" class="widefat">
                        <p class="description">Text that appears on the button</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Button URL</label>
                        <input type="text" name="embed_options[embeded][${embedIndex}][components][${compIndex}][url]" class="widefat">
                        <p class="description">URL the button will link to</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Emoji ID</label>
                        <input type="text" name="embed_options[embeded][${embedIndex}][components][${compIndex}][emoji][id]" class="widefat">
                        <p class="description">Numeric ID of the emoji (available in Discord via \:emoji:)</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Emoji Name</label>
                        <input type="text" name="embed_options[embeded][${embedIndex}][components][${compIndex}][emoji][name]" class="widefat">
                        <p class="description">Name of the emoji (e.g. "smile")</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" name="embed_options[embeded][${embedIndex}][components][${compIndex}][emoji][animated]" value="1">
                            Animated Emoji
                        </label>
                        <p class="description">Check if using an animated emoji</p>
                    </div>
                    
                    <button type="button" class="remove-component button">- Remove Button</button>
                </div>
            `);
        },
        
        removeComponent() {
            $(this).closest('.component-group').remove();
            this.updateComponentIndexes($(this).closest('.components-container'));
        },
        
        updateComponentIndexes(container) {
            container.find('.component-group').each(function(index) {
                $(this).attr('data-index', index);
                
                $(this).find('[name^="embed_options"]').each(function() {
                    const newName = $(this).attr('name')
                        .replace(/components\]\[\d+\]/, `components][${index}]`);
                    $(this).attr('name', newName);
                });
            });
        },
        
        updateButtonNotices() {
            const isBot = $('input[name="default_discord_settings[connection_type]"]:checked').val() === 'bot';
            $('.components-notice').toggle(!isBot);
        }
    };

    // ======================
    // Category Options
    // ======================
    const categoryOptions = {
        init() {
            $('#add-category-option').on('click', this.addCategoryOption.bind(this));
            $(document).on('click', '.remove-category-option', this.removeCategoryOption);
            this.initAllSelects();
        },
        
        initAllSelects() {
            $('.category-select').each((index, element) => {
                this.initCategorySelect($(element));
            });
            
            $('.embed-style-select').each((index, element) => {
                this.initEmbedSelect($(element));
            });
        },
        
        initCategorySelect($select) {
            const selectedIds = JSON.parse($select.attr('data-selected') || '[]');
            $select.empty();
            wpdepData.categories.forEach(category => {
                const selected = selectedIds.includes(category.id) ? 'selected' : '';
                $select.append(`<option value="${category.id}" ${selected}>${category.name} (ID: ${category.id})</option>`);
            });
            
            $select.select2({
                placeholder: "Select categories...",
                allowClear: true,
                width: '100%'
            });
        },
        
        initEmbedSelect($select) {
            const selectedValue = $select.attr('data-selected') || '';
            const defaultOption = '<option value="">— Default Style —</option>';
            $select.empty().append(defaultOption);
            
            for (const [key, value] of Object.entries(wpdepData.embedStyles)) {
                const selected = key == selectedValue ? 'selected' : '';
                $select.append(`<option value="${key}" ${selected}>${value}</option>`);
            }
            
            $select.select2({
                placeholder: "Select an embed style...",
                allowClear: true,
                width: '100%'
            });
        },
        
        addCategoryOption() {
            const container = $('#category-options-container');
            const index = $('.category-option-block').length;
            
            container.append(`
                <div class="category-option-block" data-index="${index}">
                    <div class="option-header">
                        <h3>Category Setting #${index + 1}</h3>
                        <button type="button" class="remove-category-option button">Remove</button>
                    </div>
                    
                    <div class="setting-group">
                        <label>Categories</label>
                        <select name="category_options[${index}][cat_ids][]" 
                                class="widefat category-select" 
                                multiple="multiple"
                                data-selected="[]">
                        </select>
                        <p class="description">Select one or more categories</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Embed Style</label>
                        <select name="category_options[${index}][selected_embedded_style]" 
                                class="widefat embed-style-select"
                                data-selected="">
                        </select>
                        <p class="description">Choose the embed style for these categories</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Main Message (optional)</label>
                        <textarea name="category_options[${index}][main_message]" class="widefat"></textarea>
                        <p class="description">This Main Message Will Be Send As Default Message Like You Send Messages In Discord Normally Before The Embedded Message.</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Channel ID (optional)</label>
                        <input type="text" name="category_options[${index}][channel_id]" class="widefat">
                        <p class="description">Override default channel for these categories</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Bot Token (optional)</label>
                        <input type="text" name="category_options[${index}][bot_token]" class="widefat">
                        <p class="description">Override default bot token for these categories</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Webhook URL (optional)</label>
                        <input type="text" name="category_options[${index}][webhook_url]" class="widefat">
                        <p class="description">Override default webhook for these categories</p>
                    </div>
                </div>
            `);
            
            this.initCategorySelect(container.find('.category-option-block:last .category-select'));
            this.initEmbedSelect(container.find('.category-option-block:last .embed-style-select'));
        },
        
        removeCategoryOption() {
            if ($('.category-option-block').length > 1) {
                $(this).closest('.category-option-block').remove();
                categoryOptions.updateCategoryIndexes();
            } else {
                alert('You must have at least one category option.');
            }
        },
        
        updateCategoryIndexes() {
            $('.category-option-block').each(function(index) {
                const $block = $(this);
                $block.attr('data-index', index);
                $block.find('h3').text(`Category Setting #${index + 1}`);
                
                $block.find('[name]').each(function() {
                    const $input = $(this);
                    const name = $input.attr('name');
                    const newName = name.replace(
                        /category_options\[\d+\]/g, 
                        `category_options[${index}]`
                    );
                    $input.attr('name', newName);
                });
            });
        }
    };


    
    // ======================
    // Documentation 
    // ======================
    const DocScript = {
      init() {
          $('.wpdep-doc-nav li a').on('click', function (e) {
          e.preventDefault(); 
          $('.wpdep-doc-nav li a').removeClass('active');
          $(this).addClass('active');
          var targetId = $(this).attr('href');
          $('.wpdep-doc-content > div').removeClass('active'); 
          $(targetId).addClass('active');
        });

        $('a.sorot[href^="#"]').on('click', function(e) {
          const targetId = $(this).attr('href').substring(1);
          const $target = $('#' + targetId);
          if ($target.length) {
            if (!$target.data('originalBg')) {
              $target.data('originalBg', $target.css('background-color'));
            }
            if ($target.data('timeoutId')) {
              clearTimeout($target.data('timeoutId'));
            }
            $target.css({
              transition: 'background-color 0.5s ease',
              'background-color': '#ffffc0ac'
            });
            const timeoutId = setTimeout(() => {
              $target.css('background-color', $target.data('originalBg'));
              $target.removeData('timeoutId');
            }, 2000);
            $target.data('timeoutId', timeoutId);
          }
        });
      }
    };

    // Initialize all components
    DocScript.init();
    manageOptionBlocks.init();
    discordConnection.init();
    embedManager.init();
    categoryOptions.init();
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


