# WordPress Embedded Post

## Send Embedded Message To Desire Channels Or Server
Sending Embeded Message When Your WordPress Post Is Update In your current Category.  

##  How does it work?
This plugin works by running the `add_action` function to `transition_post_status` and then taking as many as 3 parameters on the action.   
One of them is the `$post` variable, which I take the ID of the post that is being published then check whether the post has the category that the user wants to send the embedded message.  

## Features 
This plugin has a dynamic placeholder feature that can help you to make embedded sent to discord more slick.  
Not only dynamic placeholders, there is also a default placehoder that allows you to retrieve information from the post.  
Here are each of the placeholders that can be used.  

### Dynamic Placeholder or Variable 
There are several dynamic placeholders that can be used and here is a further explanation.  

#### Where We Can Use The Placeholder?
1. Main Message
2. Embedded In Author Name Section
3. Embedded In Title Section
4. Embedded In Description Section
5. Embedded In Name And Value Inside Fields Section
6. Embedded In label Inside Button Components Section
7. Embedded In Text Inside Footer Section

#### Processing mode
1. **Single**: Uses the first meta key's value
2. **Combine**: Joins all values with separator
3. **Connect**: Uses first value as post ID to get second value

#### `get_post_meta`
This placeholder is able to take `meta_value` according to the parameters given, if it is placed into the embedded then the part that has this placeholder will be replaced with `meta_value`.   
**Example: **  
<code>${get_post_meta => [mode],[keys],[separator]}$</code>  
> **Mode**: like the explanation in above.  
> **Keys**: In the 'keys' section you can fill it with 1 or more meta keys, each separated by ','.  
> **Separator**: in the 'separator' section you can fill it with anything, because this section is the separator between each value of the meta key, but only for Combine mode.  

#### `get_post_info`
By using this you can retrieve information from posts either the current post, or a post with a certain ID, or even a post from the id that is in the meta in the current post.  
**Example: **  
<code>${get_post_info => [id/meta_key],[info]}$</code>  
> **ID/Meta_Key**: This section if you fill it with the ID of a post it will retrieve information based on the ID, but if you fill it with meta_key it will retrieve the meta value based on the current post ID then the value will be the basis for retrieving the post information, then if you leave it blank it will retrieve the ID of the current post.  
> **Info**: in this section you fill it with the information you want to retrieve from the post based on the ID column earlier, the list will be below.  
1. `post_author`: To Get Post Author.
2. `post_date`: To Get Publish Date Of The Post.
3. `post_name`: To Get Post Title Slug.
4. `post_content`: To Get Post Content.
5. `post_title`: To Get Post Title.
6. `post_status`: To Get Post Current Status.
7. `post_type`: To Get Post Type.
8. `post_category`: To Get Post Category.
9. `thumbnail_url`: To Get Post Thumbnail Url.
10. `permalink`: To Get Post Permalink.

#### Default 
1. `${author}$`: To Showing Author Of Current Post.
2. `${timestamp}$`: To Showing Timestamp Of Current Post, in Discord Format.
3. `${permalink}$`: To Showing Permalink Of Current Post.
4. `${thumbnail_url}$`: To Showing Thumbnails Url Of Current Post.
5. `${post_title}$`: To Showing Title Of Current Post.
6. `${post_type}$`: To Showing Status Of Current Post, Like Published, Draft, Future.
7. `${post_name}$`: To Showing Slug Of Current Post.
8. `${post_content}$`: To Showing Content Of Current Post.
9. `${default_tag}$`: To Showing Default Tag You Set in The Default Settings.

## First Setup
There are 2 types that you can use, namely using Bots and Using Webhooks.   
Each type has a difference, although only slightly.   
More details will be like this.  

### Bot's
For the first bot type you need to get the Bot Token.  
This can be obtained on the [Discord Developer Portal](https://discord.com/developers/applications) by creating an application.   
You can set the bot name and profile there.  
Then don't forget to invite your bot to your server, don't forget to set it so that it only has permission to send messages and tag roles.  
After getting your bot token, open discord to find which channel you want to choose as a place for the bot to send messages.  
Don't Forget to Set Your Bot So It Can't Send Messages on Other Channels or you can hide all channels from your bot and only allow the channel parts you want.  
This is intended to prevent unnecessary things such as attacks and so on.   
Enter the Channel settings section, you will see the Channel ID copy it, if you don't see it activate debug mode in your discord settings.  
After getting the Token and Channel ID, enter the Plugin settings, select Default Settings and select 'Bot' then enter according to what is listed.  

### Webhooks
for the webhooks part it's very easy.  
First you enter the channel settings section that you want on your server, select webhooks, set the profile of the webhooks such as name and pfp, then save.  
After copying the webhooks url, go to the plugin settings and then to the default settings after that select webhooks and then paste the url in the column provided.  

## How To Install
1. First Download On Release Page.
2. Go To http://yourwebsite.com/wp-admin
3. Go To Plugin Section => Add New Plugin => (press) Upload Plugin => Choose File You're already downloaded Or Drag It to Box That Appears => Install Now.
4. The Plugin Has Been Installed.