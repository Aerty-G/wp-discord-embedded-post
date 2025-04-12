# WordPress Embedded Post Plugin

A powerful WordPress plugin that automatically sends rich embedded messages to Discord channels when your posts are published.

## ✨ Features

- **Automated Discord Notifications**: Send embed messages when posts are published
- **Category-Based Triggers**: Only notify for specific post categories
- **Dynamic Placeholders**: Customize messages with post-specific data
- **Multiple Integration Options**: Supports both Discord bots and webhooks
- **Rich Embeds**: Create beautiful, formatted Discord messages
- **Customizable Components**: Buttons, fields, and more

## 🚀 How It Works

The plugin hooks into WordPress's `transition_post_status` action to detect when posts are published. It then:

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

#### `get_post_meta` Deep Dive

**Syntax**:  
`${get_post_meta => [mode],[keys],[separator]}$`

**Mode Comparison**:

| Mode     | Input Example | Output Example | Use Case |
|----------|---------------|----------------|----------|
| single   | `[single],[price]` | `29.99` | Simple field values |
| combine  | `[combine],[city,state],[, ]` | `New York, NY` | Composite fields |
| connect  | `[connect],[related_post,title]` | `Featured Product` | Cross-post data |

**Real-World Examples**:
```markdown
1. Product Price Display:  
   `${get_post_meta => [single],[product_price]}$` → `49.99`

2. Location Formatting:  
   `${get_post_meta => [combine],[city,country], [ - ]}$` → `Paris - France`

3. Related Content:  
   `${get_post_meta => [connect],[featured_product,product_name]}$` → `Premium Headphones`
```

#### `get_post_info` Mastery

**Advanced Syntax**:
```markdown
# Get from meta-referenced post
${get_post_info => [featured_event],[post_title]}$

# Get from direct ID
${get_post_info => [42],[permalink]}$

# Current post fallback
${get_post_info => [],[thumbnail_url]}$
```

**Output Samples**:
```markdown
| Template | Sample Output |
|----------|---------------|
| `${...[],[post_date]}$` | `2023-07-15 14:30:00` |
| `${...[42],[post_title]}$` | `Summer Sale Event` |
| `${...[event_ref],[thumbnail_url]}$` | `https://example.com/image.jpg` |
```

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

### Default Placeholders Cheat Sheet

**Quick Reference Table**:

| Placeholder | Equivalent PHP | Sample Output |
|-------------|----------------|---------------|
| `${author}$` | `get_the_author()` | `John Doe` |
| `${discord_timestamp}$` | `<t:UNIXTIME:R>` | `2 hours ago` |
| `${post_content}$` | `get_the_content()` | Full HTML content |
| `${post_category}$` | `get_the_category_list()` | `News, Updates` |

**Available Default Placeholders:**
- `${permalink}$` - Post URL
- `${author}$` - Post author
- `${post_content}$` - Post Content
- `${timestamp}$` - Publication time (Discord format)
- `${post_title}$` - Post title
- `${thumbnail_url}$` - Featured image
- `${default_tag}$` - Your custom default tag
- `${post_type}$` - Post Type
- `${post_name}$` - Post slug

## 🛠️ Advanced Usage

### Template Examples

**Basic Announcement**:
```yaml
title: "New Post: ${post_title}$"
description: "${post_content}$... [Read More](${permalink}$)"
thumbnail: "${thumbnail_url}$"
color: "#FF5733"
```

**E-Commerce Product Alert**:
```yaml
title: "🚀 New Product: ${get_post_meta => [single],[product_name]}$"
fields:
  - name: "Price"
    value: "$${get_post_meta => [single],[price]}$"
  - name: "Availability"
    value: "${get_post_meta => [single],[stock_status]}$"
button:
  label: "Buy Now"
  url: "${permalink}$"
```

## 📸 Screenshots

![Settings Page]()  
*Plugin configuration panel*

![Embed Example]()  
*Sample Discord embed output*

## 🤝 Contributing

We welcome contributions! Please fork the repository and submit pull requests.

**Todo List**:
- [ ] More Platform integration
- [ ] Make The Frontend Better
