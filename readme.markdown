# UCF Today Bootstrap WordPress Theme

Theme to replace the original Today WordPress theme, which relies on the Thematic framework. Written from the UCF Generic Bootstrap theme.



## Installation

### Required Plugins:
* Ad-minister
* Social
* Regenerate Thumbnails (recommended)
* Twitter Tools (requires Social)
* WP-Print

### Required Installation Settings:
* Settings > Permalinks: set Category base to 'section'; set Tag base to 'topic' (if not already set)
* Update pages with old Blueprint markup to use Bootstrap markup, or remove those pages entirely
* Settings > Discussion: Make sure Trackbacks/Pingbacks are DISABLED.
* Replace all image references in Ad-minister ad content with protocol-relative URLs

### Installation Recommendations:
* Regenerate thumbnails (new [photo_sets] thumbnail size has been created)
* Update all post types to remove Pingback functionality. A lot of posts were originally created with the Pingback functionality turned on, and unfortunately they are still capable of receiving spam. Pingback functionality for posts/pages can be disabled through the WordPress interface in bulk; modify your default number of displayed posts in the Posts admin area, select all, then Bulk Edit the 'Pings' value to 'do not allow'. (http://wordpress.stackexchange.com/questions/84633/how-to-disable-wordpress-trackback)
The primary issue with Pingbacks, though, is that media attachments allow them, and there isn't an option in the WordPress admin interface to disable them on existing attachments. To actually disable Pingbacks on every post type, a SQL statement will need to be run against the database directly: `UPDATE wp_posts SET ping_status="closed";` (note: wp_posts will be named 'wp_SITEID_posts' on multisite installs.)


## Deployment

This theme relies on Twitter's Bootstrap framework. UCF's fork of the Bootstrap project (http://github.com/UCF/bootstrap/) is added as submodule in static/bootstrap. Bootstrap must be initialized as a submodule with every new clone of this theme repository.

#### Initializing Bootstrap with a new clone:
1. Pull/Clone the theme repo
2. From the theme's root directory, run `git submodule update --init static/bootstrap`
3. From the static/bootstrap directory, run `git checkout today`.  Make sure a branch has been checked out for submodules as they will default to 'no branch' when cloned.

#### Alternative method using Git v1.6.5+:
1. Run `git clone` using the `--recursive` parameter to clone the repo with all of its submodules; e.g. `git clone --recursive https://github.com/UCF/Today-Bootstrap.git`
2. From the static/bootstrap directory, run `git checkout today`.  Make sure a branch has been checked out for submodules as they will default to 'no branch' when cloned.


## Development

This theme relies on Twitter's Bootstrap framework. Bootstrap is a CSS framework that uses LESS to programatically develop stylesheets.
UCF's fork of the Bootstrap project (http://github.com/UCF/bootstrap/) is added as submodule in static/bootstrap.

### Setup
** Note: This theme uses a version of Bootstrap whose package requirements result in Bootstrap's CSS files compiling to empty files. Follow the steps below completely to install the packages so that the `make` command works correctly. (https://github.com/twitter/bootstrap/issues/8088) **

0. If they're not already installed on your machine, install node and npm for node-related package management.
1. If this is a brand new clone, run `git submodule update --init static/bootstrap` from the theme's root directory.
2. Navigate to static/bootstrap, then run `npm install` to install necessary dependencies for building Bootstrap's .less files. These packages are excluded in the submodule .gitignore.
3. Navigate to the submodule's node_modules/recess folder, and open **package.json**. Under 'dependencies', update 'less' from '>= 1.3.0' to '1.3.3' and save. Delete node_modules/ from within the recess directory.
4. From the recess directory, run `npm install`.
5. Navigate back to the root bootstrap directory and remove the compiled bootstrap directory, if it exists.

### Compiling
Once the setup instructions above have been completed, you can compile modified .less files from the root bootstrap directory with `make bootstrap`. Compiled files will save to a new directory 'bootstrap' within the root directory (static/bootstrap/bootstrap/).

### Importing Data
Today tends to export HUGE XML files which don't import well-- use a WXR splitter (http://github.com/suhastech/Wordpress-WXR-Splitter/) to generate smaller chunks of data for import.


## Custom Post Types
* Alert (Header)
* Post (Extends base post type)
* Expert
* Photoset
* Video
* Profile
* External Story

Note: the Update post type was not carried over from the original theme.


## Custom Taxonomies
* Experts (for Post, Photoset CPTs)
* Groups (for Profile CPT)


## Shortcodes
This theme carries over the 'post-type-search' and 'search_form' shortcodes from Generic Theme. Note that no CPTs in this theme use the autogenerated 'posttype-list' shortcode.

Every shortcode custom to this theme takes a `css` attribute that allows the user to specify CSS classes that should be applied to the shortcode's wrapper div.
Other attributes are listed below the given shortcode's name, if available.

### Posts (lists)
* feature
* more_headlines
	* social (0,1...default:1) - Display social network buttons
	* header (0,1...default:1) - Display the More headlines header
	* num_post (number...default:3) - How many headlines should be displayed
* subpage_features
* promos

### Posts (single)
* single_post
* single_post_meta
* single_post_more_tag
* single_post_more_cat
* single_post_comments
* single_post_topics
* single_post_recommended
* single_post_related_experts

### Photos
* ucf_photo
	* link_page_name (string...default:"Photos") - Determines the text of the header and name of the page the More link will go to.

### Photo Sets
* photo_set
* photo_sets

### Videos
* ucf_video
	* height(number) - Height of the embed. If left blank, will be autosized based on width
	* width(number...default:400) - Width of the embed
* videos

### Feeds
* events
* announcements

### Experts
* expert_short
* expert_meta
* expert_tagged
* expert_videos
* expert_photos

### External Stories
* external_stories

### Profiles
* profile

### Other
* myucf_signon
* advertisement
	* loc (string) - Ad-minister plugin position name
	* type (horizontal,vertical...default:"vertical") - Orientation of the ad. Vertical will center the title and content


## Styling
Most utility CSS classes have been carried over from the old Today theme, and are listed below.
Border-related classes can be added anywhere, but are intended for use on .span divs.

Note that the `checkered` CSS class has been removed in favor of Bootstrap's `table-striped` class.

* .border-both: Add vertical borders to the left and right of a div.
* .border-left
* .border-right
* .border-top
* .border-bottom
* .orange: Adds orange color to text.
* .cropped: Should be added to a `.thumb` element to proportionally stretch a thumbnail to a confined space. Elements with this class should have a background image specified, an explicit `height` value (in pixels), as well as a child image (with the same URL) contained within an `<a>` tag. Thumbnails with the .cropped style will adjust to best fit the designated space, despite the actual thumbnail's dimensions.


## Other Notes

* The video_carousel shortcode was removed from the previous version of the theme.
* This theme does not use Bootstrap's responsive styling; CSS and javascript related to Bootstrap responsiveness (style-responsive.css, config options, etc.) have been removed from this theme.
* New template functionality has been added to this theme: any post/CPT or taxonomy that previously relied on a page with markup/shortcodes for its content will now fall back to a default template if no content or page is provided. (Category, Tag, Expert, Home, Photo Set, Single, Tag, Videos)
