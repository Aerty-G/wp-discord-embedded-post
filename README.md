# WordPress Embedded Post Plugin

A powerful WordPress plugin that automatically sends rich embedded messages to Discord channels when your posts are published or updated.

## ✨ Features

- **Automated Discord Notifications**: Send embed messages when posts are published
- **Category-Based Triggers**: Only notify for specific post categories
- **Dynamic Placeholders**: Customize messages with post-specific data
- **Multiple Integration Options**: Supports both Discord bots and webhooks
- **Rich Embeds**: Create beautiful, formatted Discord messages
- **Customizable Components**: Buttons, fields, and more

## 🚀 How It Works

The plugin hooks into WordPress's `transition_post_status` action to detect when posts are published or updated. It then:

1. Checks if the post belongs to a monitored category
2. Constructs a rich embed message using your templates
3. Sends the message to your specified Discord channel
4. Supports both bot and webhook integration methods

## 🔌 Installation

1. Download the latest version from the [Releases page](#)
2. Navigate to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the downloaded ZIP file
4. Activate the plugin after installation

## ⚙️ Configuration

### Bot Setup (Recommended)
1. Create a Discord application at the [Developer Portal](https://discord.com/developers/applications)
2. Add a bot to your application and copy the token
3. Invite the bot to your server with only necessary permissions
4. Note your channel ID (enable Developer Mode in Discord settings)
5. Enter these details in the plugin settings

### Webhook Setup
1. In Discord channel settings, create a new webhook
2. Customize the webhook name and avatar if desired
3. Copy the webhook URL
4. Paste into the plugin settings

## 📝 Placeholder System

The plugin offers powerful placeholders to customize your embeds:

### Dynamic Placeholders
Available in:
- Main message
- Embed author/title/description
- Field names/values
- Button labels
- Footer text

#### `get_post_meta` Syntax:
`${get_post_meta => [mode],[keys],[separator]}$`

**Modes:**
- `single`: Use first meta key's value
- `combine`: Join multiple values with separator
- `connect`: Use first value as post ID to get second value

**Example:**  
- `${get_post_meta => [single],[product_price]}$`  
- `${get_post_meta => [combine],[name_product,product_price], [: ]}$`  
- `${get_post_meta => [connect],[parent_product,product_price]}$`  

#### `get_post_info` Syntax:
`${get_post_info => [id/meta_key],[info]}$`

**Available Info:**
- `post_author` - Post author name
- `post_date` - Publication date
- `post_title` - Post title
- `thumbnail_url` - Featured image URL
- `permalink` - Post URL
- `post_name` - Post slug
- `post_content` - Post Content
- `post_status` - Post Current Status
- `post_type` - Post Type
- `post_category` - Post Category.

**Example:**  
- `${get_post_meta => [],[permalink]}$`  
- `${get_post_meta => [parent_post],[permalink]}$`  

### Default Placeholders
- `${permalink}$` - Post URL
- `${author}$` - Post author
- `${post_content}$` - Post Content
- `${timestamp}$` - Publication time (Discord format)
- `${post_title}$` - Post title
- `${thumbnail_url}$` - Featured image
- `${default_tag}$` - Your custom default tag
- `${post_type}$` - Post Type
- `${post_name}$` - Post slug

## 📸 Screenshots
Currently Unavailable 

![Settings Page]()  
*Plugin configuration panel*

![Embed Example]()  
*Sample Discord embed output*

## 🤝 Contributing

We welcome contributions! Please fork the repository and submit pull requests.
