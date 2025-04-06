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
            $(document).on('change', 'input[name$="[mode]"]', this.SeparatorField);
        },
        
        SeparatorField() {
            var separatorGroup = $(this).closest('.option-block').find('.separator-group');
            if ($(this).val() === 'combine') {
                separatorGroup.show();
            } else {
                separatorGroup.hide();
            }
        },
        
        addOptionBlock() {
            var index = Date.now();
            var newBlock = $(`
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
                        </div>
                          <button type="button" class="remove-option button">Remove</button>
                    </div>
                    
                    <div class="separator-group" style="display:none;">
                        <label>Separator: 
                            <input type="text" name="options[${index}][separator]" value=", " class="widefat">
                        </label>
                    </div>
                    
                    <div class="keys-container">
                        <div class="key-input">
                            <input type="text" name="options[${index}][keys][]" placeholder="meta_key" class="widefat">
                            <button type="button" class="add-key button">+</button>
                        </div>
                    </div>
                    
                    <div class="template-group">
                        <label>Output Template: 
                            <input type="text" name="options[${index}][template]" class="widefat" 
                                   placeholder="E.g.: Post Title: \${title_post}$" value="">
                        </label>
                        <p class="description">Use \${meta_key}$ to insert meta values</p>
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
            const newKey = $(`
                <div class="key-input">
                    <input type="text" name="options[${index}][keys][]" placeholder="meta_key" class="widefat">
                    <button type="button" class="remove-key button">-</button>
                </div>
            `);
            container.append(newKey);
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
            this.checkConnectionType();
            $('input[name="default_discord_settings[connection_type]"]').on('change', () => this.checkConnectionType());
        },
        
        checkConnectionType() {
            const connectionType = $('input[name="default_discord_settings[connection_type]"]:checked').val();
            $('.connection-settings').hide();
            $('#' + connectionType + '-settings').show();
            this.toggleButtonComponents(connectionType);
        },
        
        toggleButtonComponents(connectionType) {
            if (connectionType === 'bot') {
                $('#button-components-section').show();
            } else {
                $('#button-components-section').hide();
            }
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
            $('.add-component').on('click', this.addComponent);
            $(document).on('click', '.remove-component', this.removeComponent);
        },
        
        addEmbed() {
            const container = $(this).closest('.embed-section');
            const index = $('.embed-block').length;
            const newEmbed = $(`
                <div class="embed-block" data-index="${index}">
                    <div class="option-header">
                        <h3>Embed #${index + 1}</h3>
                        <button type="button" class="remove-embed button">Remove</button>
                    </div>
                    
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
                    
                    <div class="setting-group">
                        <label>Title</label>
                        <input type="text" name="embed_options[embeded][${index}][title]" class="widefat">
                        <p class="description">The main title of your embed (appears in bold at the top)</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Description</label>
                        <textarea name="embed_options[embeded][${index}][description]" class="widefat" rows="3"></textarea>
                        <p class="description">The main content of your embed (supports Markdown formatting)</p>
                    </div>
                    
                    <div class="fields-container">
                        <h4>Fields</h4>
                        <p class="description">Add key-value pairs to display in your embed</p>
                    </div>
                    <button type="button" class="add-field button">Add Field</button>
                    
                    <div class="setting-group">
                        <label>Image URL</label>
                        <input type="text" name="embed_options[embeded][${index}][image][url]" class="widefat">
                        <p class="description">URL of an image to display at the bottom of the embed</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Color (hex)</label>
                        <input type="text" name="embed_options[embeded][${index}][color]" class="widefat" placeholder="#FFFFFF">
                        <p class="description">The color of the embed border (hex format, e.g. #FF0000 for red)</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Timestamp</label>
                        <input type="text" name="embed_options[embeded][${index}][timestamp]" class="widefat" placeholder="Leave empty for current time">
                        <p class="description">ISO8601 timestamp (e.g. 2023-01-01T00:00:00.000Z) or leave empty for current time</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Footer Text</label>
                        <input type="text" name="embed_options[embeded][${index}][footer][text]" class="widefat">
                        <p class="description">Text to display in the footer of the embed</p>
                    </div>
                </div>
            `);
            container.find('.embed-block:last').after(newEmbed);
        },
        
        removeEmbed() {
            if ($('.embed-block').length > 1) {
                $(this).closest('.embed-block').remove();
                this.updateEmbedIndexes();
            } else {
                alert('You must have at least one embed.');
            }
        },
        
        updateEmbedIndexes() {
            $('.embed-block').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('h3').text('Embed #' + (index + 1));
                
                $(this).find('[name^="embed_options"]').each(function() {
                    const name = $(this).attr('name')
                        .replace(/embed_options\[embeded\]\[\d+\]/, `embed_options[embeded][${index}]`);
                    $(this).attr('name', name);
                });
            });
        },
        
        addField() {
            const embedIndex = $(this).closest('.embed-block').data('index');
            const container = $(this).prev('.fields-container');
            const fieldIndex = container.find('.field-group').length;
            
            const newField = $(`
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
                    <button type="button" class="remove-field button">Remove Field</button>
                </div>
            `);
            container.append(newField);
        },
        
        removeField() {
            $(this).closest('.field-group').remove();
        },
        
        addComponent() {
            const container = $(this).prev('.components-container');
            const componentCount = container.find('.component-group').length;
            
            if (componentCount >= 4) {
                alert('Maximum of 4 buttons allowed.');
                return;
            }
            
            const newComponent = $(`
                <div class="component-group" data-index="${componentCount}">
                    <div class="setting-group">
                        <label>Button Label</label>
                        <input type="text" name="embed_options[embeded_button][0][components][${componentCount}][label]" class="widefat">
                        <p class="description">Text that appears on the button</p>
                    </div>
                    <div class="setting-group">
                        <label>Button URL</label>
                        <input type="text" name="embed_options[embeded_button][0][components][${componentCount}][url]" class="widefat">
                        <p class="description">URL the button will link to</p>
                    </div>
                    <div class="setting-group">
                        <label>Emoji ID</label>
                        <input type="text" name="embed_options[embeded_button][0][components][${componentCount}][emoji][id]" class="widefat">
                        <p class="description">Numeric ID of the emoji (available in Discord via \:emoji:)</p>
                    </div>
                    <div class="setting-group">
                        <label>Emoji Name</label>
                        <input type="text" name="embed_options[embeded_button][0][components][${componentCount}][emoji][name]" class="widefat">
                        <p class="description">Name of the emoji (e.g. "smile")</p>
                    </div>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" name="embed_options[embeded_button][0][components][${componentCount}][emoji][animated]" value="1">
                            Animated Emoji
                        </label>
                        <p class="description">Check if using an animated emoji</p>
                    </div>
                    <button type="button" class="remove-component button">Remove Button</button>
                </div>
            `);
            container.append(newComponent);
        },
        
        removeComponent() {
            $(this).closest('.component-group').remove();
            this.updateComponentIndexes($(this).closest('.components-container'));
        },
        
        updateComponentIndexes(container) {
            container.find('.component-group').each(function(index) {
                $(this).attr('data-index', index);
                
                $(this).find('[name^="embed_options"]').each(function() {
                    const name = $(this).attr('name')
                        .replace(/components\]\[\d+\]/, `components][${index}]`);
                    $(this).attr('name', name);
                });
            });
        }
    };

    // ======================
    // Category Options
    // ======================
    const categoryOptions = {
        init() {
            $('#add-category-option').on('click', this.addCategoryOption);
            $(document).on('click', '.remove-category-option', this.removeCategoryOption);
        },
        
        addCategoryOption() {
            const container = $('#category-options-container');
            const index = $('.category-option-block').length;
            
            // Get categories HTML
            let categoriesHtml = '';
            $('.category-select:first option').each(function() {
                const selected = $(this).is(':selected') ? ' selected' : '';
                categoriesHtml += `<option value="${$(this).val()}"${selected}>${$(this).text()}</option>`;
            });
            
            // Get embed styles HTML
            let embedStylesHtml = '';
            $('.embed-style-select:first option').each(function() {
                embedStylesHtml += `<option value="${$(this).val()}">${$(this).text()}</option>`;
            });
            
            const newOption = $(`
                <div class="category-option-block" data-index="${index}">
                    <div class="option-header">
                        <h3>Category Setting #${index + 1}</h3>
                        <button type="button" class="remove-category-option button">Remove</button>
                    </div>
                    
                    <div class="setting-group">
                        <label>Categories</label>
                        <select name="category_options[${index}][cat_ids][]" class="widefat category-select" multiple="multiple">
                            ${categoriesHtml}
                        </select>
                        <p class="description">Select one or more categories</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Embedded Style</label>
                        <select name="category_options[${index}][selected_embedded_style]" class="widefat embed-style-select">
                            ${embedStylesHtml}
                        </select>
                        <p class="description">Choose the embed style for these categories</p>
                    </div>
                    
                    <div class="setting-group">
                        <label>Main Messages (optional)</label>
                        <input type="text" name="category_options[${index}][main_message]" class="widefat">
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
            
            container.append(newOption);
            
            // Initialize Select2 for new elements
            newOption.find('.category-select').select2({
                placeholder: "Select categories...",
                allowClear: true,
                width: '100%'
            });
            
            newOption.find('.embed-style-select').select2({
                placeholder: "Select an embed style...",
                allowClear: true,
                width: '100%'
            });
        },
        
        removeCategoryOption() {
            if ($('.category-option-block').length > 1) {
                $(this).closest('.category-option-block').remove();
                this.updateCategoryOptionIndexes();
            } else {
                alert('You must have at least one category option.');
            }
        },
        
        updateCategoryOptionIndexes() {
            $('.category-option-block').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('h3').text('Category Setting #' + (index + 1));
                
                $(this).find('[name^="category_options"]').each(function() {
                    const name = $(this).attr('name')
                        .replace(/category_options\[\d+\]/, `category_options[${index}]`);
                    $(this).attr('name', name);
                });
            });
        }
    };

    // Initialize all modules
    manageOptionBlocks.init();
    discordConnection.init();
    embedManager.init();
    categoryOptions.init();
});