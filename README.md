![Banner](https://raw.githubusercontent.com/Aerty-G/wp-discord-embedded-post/refs/heads/main/assets/images/simple-banner.png)

# ![Icon](https://raw.githubusercontent.com/Aerty-G/wp-discord-embedded-post/refs/heads/main/assets/images/simple-icon.svg) WordPress Embedded Post Plugin

A powerful WordPress plugin that automatically sends rich embedded messages to Discord channels when your posts are published.
And now also send new comments as embedded to discord!!

## ✨ Features

- **Automated Discord Notifications**: Send embed messages when posts are published
- **Category-Based Triggers**: Only notify for specific post categories
- **Dynamic Placeholders**: Customize messages with post-specific data
- **Multiple Integration Options**: Supports both Discord bots and webhooks
- **Rich Embeds**: Create beautiful, formatted Discord messages
- **Customizable Components**: Buttons, fields, and more
- **Filter To Markdown**: Filter html tag and other to markdown. eg `<strong>` to `**`

## 🚀 How It Works

The plugin hooks into 3-type hooks/action.
1. `transition_post_status` Default action thats executed when the post move from one status to other status.
2. `${old_status}_to_${new_status}` Action that's specifically hooks when specific status change to other specific status. eg `draft_to_publish`.
3. `save_post` Action that's executed when user save the post to database, or when the user press publish button when they add new post.
I Personally Recommended you choose `save_post`. 

How The Proceed going?
1. Checks if the post belongs to a monitored category
2. Constructs a rich embed message using your templates
3. Sends the message to your specified Discord channel
4. Supports both bot and webhook integration methods


## 🔌 Installation

1. Download the latest version from the [Releases page](https://github.com/Aerty-G/wp-discord-embedded-post/releases) or The lastest in blob branch main [Here](https://github.com/Aerty-G/wp-discord-embedded-post/archive/refs/heads/main.zip)
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

### Tag Setup 
1. Go to your discord server
2. Open any channels
3. Type your tag, example @News update
4. Send The Message or Press 'Enter' your keyboard
5. Copy the message
6. Delete the message
7. Now you have a id of the role you have to tag
8. Put this id (Example: `<@&12345678912>`) to whenever you want like the default tag or directly in Embedded or the main message

### Post Section Setup
1. Go to default settings 
2. Fill the requirement eg. Bot Token or Webhook URL
3. Go to Embedded Style
4. Customize Your Embedded
5. Go to Category Settings 
6. Fill The requirement eg. Categories, Default Messages (optional) etc.

### Comment Section Setup
1. First go to default settings
2. Select comment service eg. WordPress, Wpdiscuz, Disqus(still on development)
3. Insert webhooks or Bot Token and the channel id

## 📝 Placeholder System

The plugin offers powerful placeholders to customize your embeds:

### Dynamic Placeholders
Available in almost every section in embedded input:
- Main message
- Embed author/title/description
- Field names/values
- Button labels
- Footer text

### `default_message` Standard
**Syntax**:
`${default_message}$` or `${default_message => [var_1,var_2, and more]}$`

**Example**:
```markdown
if in default settings you set, 
'hello %var_0% Current Post Title %var_1% has updated at %var_2%'
~This is just example, ignore my grammar 
and in your category setting or embedded setting you set like this.
'${default_message => [@everyone,extract_post_title,extract_post_date]}'
the results will be like this.
'hello @everyone Current Post Title MotoGP has updated at 2025-07-23 14:30:00'
```
**Available Info**:
- `author` - Post author name
- `post_date` - Publication date
- `post_title` - Post title
- `thumbnail_url` - Featured image URL
- `permalink` - Post URL
- `post_name` - Post slug
- `post_content` - Post Content
- `post_status` - Post Current Status
- `post_type` - Post Type
- `post_category` - Post Category.

Don't Forget to put `extract_` when you want to get the post info. 
And `%var_0%` is can be more than 3.

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

| Template | Sample Output |
|----------|---------------|
| `${...[],[post_date]}$` | `2023-07-15 14:30:00` |
| `${...[42],[post_title]}$` | `Summer Sale Event` |
| `${...[event_ref],[thumbnail_url]}$` | `https://example.com/image.jpg` |


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
- `${post_content:mode:much}$` - Post Content, mode => `0` for word, `1` for characters, much => how much in number
- `${timestamp}$` - Publication time (Discord format)
- `${post_title}$` - Post title
- `${thumbnail_url}$` - Featured image
- `${default_tag}$` - Your custom default tag
- `${post_type}$` - Post Type
- `${post_name}$` - Post slug
- `${post_category}$` - Post Category 

### Default Placeholder For Comments Embedded 

**Quick Reference Table**:

| Placeholder | Equivalent PHP | Sample Output |
|-------------|-----------------------|--------------------|
| `${comment_content}$` | `$comment->comment_content` | `This Products Are Soo Good!!` |
| `${comment_discord_timestamp}$` | `<t:UNIXTIME:R>` | `2 hours ago` |
| `${comment_author}$` | `$comment->comment_author` | `John Doe` |
| `${comment_post_title}$` | `$comment->comment_post_ID => get_post() => $post->post_title` | `Nikke New Shoes` |

**Available Default Placeholders:**
- `${comment_permalink}$` - Comment URL
- `${comment_author}$` - Comment author
- `${comment_content}$` - Comment Content
- `${comment_date}$` - Date Of the Comment
- `${timestamp}$` - Publication time 
- `${comment_discord_timestamp}$` - Timestamp will show like `2 day ago`
- `${comment_post_title}$` - Post title of the comment
- `${comment_parent_content}$` - The Main Comment Content if the current Comment was a reply
- `${comment_parent_author}$` - The Main Comment Author if the current Comment was a reply
- `${comment_parent_date}$` - The Main Comment Date if the current Comment was a reply
- `${comment_parent_permalink}$` - The Main Comment Permalink if the current Comment was a reply
- `${comment_parent_post_title}$` - The Main Comment Post Title if the current Comment was a reply

## 🛠️ Advanced Usage

### Template Examples

**Basic Announcement**:
```yaml
main_message: "${default_tag}$ @here"
title: "New Post: ${post_title}$"
description: "${post_content:0:25}$... [Read More](${permalink}$)"
thumbnail: "${thumbnail_url}$"
color: "#FF5733"
```

**E-Commerce Product Alert**:
```yaml
main_message: "${default_tag}$ @here"
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

![Default Settings Page](https://raw.githubusercontent.com/Aerty-G/wp-discord-embedded-post/main/assets/images/01.jpg)  
![Manage Variable Page](https://raw.githubusercontent.com/Aerty-G/wp-discord-embedded-post/main/assets/images/02.jpg)
![Embed Style Setting Page](https://raw.githubusercontent.com/Aerty-G/wp-discord-embedded-post/main/assets/images/03.jpg)
![Embed Comment Style Page](https://raw.githubusercontent.com/Aerty-G/wp-discord-embedded-post/main/assets/images/04.jpg)
![Category Settings Page](https://raw.githubusercontent.com/Aerty-G/wp-discord-embedded-post/main/assets/images/05.jpg)
*Plugin configuration panel*

![Embed Example]()  
*Sample Discord embed output*

## 🤝 Contributing

We welcome contributions! Please fork the repository and submit pull requests.

**Todo List**:
- [ ] More Platform integration
- [ ] Make The Frontend Better
- [ ] Make Cache System For Better Performance


## License GPL v2

![License: GPL v2 or later](https://img.shields.io/badge/License-GPL%20v2%20%7C%20later-blue.svg)