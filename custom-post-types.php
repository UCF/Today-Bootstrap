<?php

/**
 * Abstract class for defining custom post types.
 *
 **/
abstract class CustomPostType{
	public
		$name           = 'custom_post_type',
		$plural_name    = 'Custom Posts',
		$singular_name  = 'Custom Post',
		$add_new_item   = 'Add New Custom Post',
		$edit_item      = 'Edit Custom Post',
		$new_item       = 'New Custom Post',
		$public         = True,  # I dunno...leave it true
		$use_title      = True,  # Title field
		$use_editor     = True,  # WYSIWYG editor, post content field
		$use_revisions  = True,  # Revisions on post content and titles
		$use_thumbnails = False, # Featured images
		$use_order      = False, # Wordpress built-in order meta data
		$use_metabox    = False, # Enable if you have custom fields to display in admin
		$use_shortcode  = False, # Auto generate a shortcode for the post type
		                         # (see also objectsToHTML and toHTML methods)
		$taxonomies     = array('post_tag'),
		$built_in       = False,
		$rewrite 		= True,
		$show_in_rest   = False,

		# Optional default ordering for generic shortcode if not specified by user.
		$default_orderby = null,
		$default_order   = null;


	/**
	 * Wrapper for get_posts function, that predefines post_type for this
	 * custom post type.  Any options valid in get_posts can be passed as an
	 * option array.  Returns an array of objects.
	 **/
	public function get_objects($options=array()){

		$defaults = array(
			'numberposts'   => -1,
			'orderby'       => 'title',
			'order'         => 'ASC',
			'post_type'     => $this->options('name'),
		);
		$options = array_merge($defaults, $options);
		$objects = get_posts($options);
		return $objects;
	}


	/**
	 * Similar to get_objects, but returns array of key values mapping post
	 * title to id if available, otherwise it defaults to id=>id.
	 **/
	public function get_objects_as_options($options=array()){
		$objects = $this->get_objects($options);
		$opt     = array();
		foreach($objects as $o){
			switch(True){
				case $this->options('use_title'):
					$opt[$o->post_title] = $o->ID;
					break;
				default:
					$opt[$o->ID] = $o->ID;
					break;
			}
		}
		return $opt;
	}


	/**
	 * Return the instances values defined by $key.
	 **/
	public function options($key){
		$vars = get_object_vars($this);
		return $vars[$key];
	}


	/**
	 * Additional fields on a custom post type may be defined by overriding this
	 * method on an descendant object.
	 **/
	public function fields(){
		return array();
	}


	/**
	 * Using instance variables defined, returns an array defining what this
	 * custom post type supports.
	 **/
	public function supports(){
		#Default support array
		$supports = array();
		if ($this->options('use_title')){
			$supports[] = 'title';
		}
		if ($this->options('use_order')){
			$supports[] = 'page-attributes';
		}
		if ($this->options('use_thumbnails')){
			$supports[] = 'thumbnail';
		}
		if ($this->options('use_editor')){
			$supports[] = 'editor';
		}
		if ($this->options('use_revisions')){
			$supports[] = 'revisions';
		}
		return $supports;
	}


	/**
	 * Creates labels array, defining names for admin panel.
	 **/
	public function labels(){
		return array(
			'name'          => __($this->options('plural_name')),
			'singular_name' => __($this->options('singular_name')),
			'add_new_item'  => __($this->options('add_new_item')),
			'edit_item'     => __($this->options('edit_item')),
			'new_item'      => __($this->options('new_item')),
		);
	}


	/**
	 * Creates metabox array for custom post type. Override method in
	 * descendants to add or modify metaboxes.
	 **/
	public function metabox(){
		if ($this->options('use_metabox')){
			return array(
				'id'       => $this->options('name').'_metabox',
				'title'    => __($this->options('singular_name').' Fields'),
				'page'     => $this->options('name'),
				'context'  => 'normal',
				'priority' => 'high',
				'fields'   => $this->fields(),
			);
		}
		return null;
	}


	/**
	 * Registers metaboxes defined for custom post type.
	 **/
	public function register_metaboxes(){
		if ($this->options('use_metabox')){
			$metabox = $this->metabox();
			add_meta_box(
				$metabox['id'],
				$metabox['title'],
				'show_meta_boxes',
				$metabox['page'],
				$metabox['context'],
				$metabox['priority']
			);
		}
	}


	/**
	 * Registers the custom post type and any other ancillary actions that are
	 * required for the post to function properly.
	 **/
	public function register(){
		$registration = array(
			'labels'       => $this->labels(),
			'supports'     => $this->supports(),
			'public'       => $this->options('public'),
			'taxonomies'   => $this->options('taxonomies'),
			'_builtin'     => $this->options('built_in'),
			'rewrite'	   => $this->options('rewrite')
		);

		if ( $this->options('show_in_rest') ) {
			$registration['show_in_rest'] = True;
			$registration['rest_base'] = $this->name . 's';
			$registration['rest_controller_class'] = 'WP_REST_Posts_Controller';
		}

		if ($this->options('use_order')){
			$registration = array_merge($registration, array('hierarchical' => True,));
		}

		register_post_type($this->options('name'), $registration);

		if ($this->options('use_shortcode')){
			add_shortcode($this->options('name').'-list', array($this, 'shortcode'));
		}
	}


	/**
	 * Shortcode for this custom post type.  Can be overridden for descendants.
	 * Defaults to just outputting a list of objects outputted as defined by
	 * toHTML method.
	 **/
	public function shortcode($attr){
		$default = array(
			'type' => $this->options('name'),
		);
		if (is_array($attr)){
			$attr = array_merge($default, $attr);
		}else{
			$attr = $default;
		}
		return sc_object_list($attr);
	}


	/**
	 * Handles output for a list of objects, can be overridden for descendants.
	 * If you want to override how a list of objects are outputted, override
	 * this, if you just want to override how a single object is outputted, see
	 * the toHTML method.
	 **/
	public function objectsToHTML($objects, $css_classes){
		if (count($objects) < 1){ return '';}

		$class = get_custom_post_type($objects[0]->post_type);
		$class = new $class;

		ob_start();
		?>
		<ul class="<?php if($css_classes):?><?=$css_classes?><?php else:?><?=$class->options('name')?>-list<?php endif;?>">
			<?php foreach($objects as $o):?>
			<li>
				<?=$class->toHTML($o)?>
			</li>
			<?php endforeach;?>
		</ul>
		<?php
		$html = ob_get_clean();
		return $html;
	}


	/**
	 * Outputs this item in HTML.  Can be overridden for descendants.
	 **/
	public function toHTML($object){
		$html = '<a href="'.get_permalink($object->ID).'">'.$object->post_title.'</a>';
		return $html;
	}
}


/**
 * Header alert message. (.e.g. traffic advisory, severe weather)
 *
 * @author Chris Conover
 **/
class Alert extends CustomPostType{
	public
		$name           = 'alert',
		$plural_name    = 'Alerts',
		$singular_name  = 'Alert',
		$add_new_item   = 'Add New Alert',
		$edit_item      = 'Edit Alert',
		$new_item       = 'New Alert',
		$use_thumbnails = True,
		$use_metabox    = True;

	public function fields() {
		$prefix = $this->options('name').'_';
		return array(
			array(
				'name'	=> 'Text',
				'desc'	=> '',
				'id'	=> $prefix.'text',
				'type'	=> 'text'
			),
			array(
				'name'	=> 'Link Text',
				'desc'	=> 'If left blank, the link will not be displayed.',
				'id'	=> $prefix.'link_text',
				'type'	=> 'text'
			),
			array(
				'name'	=> 'Link URL',
				'desc'	=> 'If left blank, the link portion of the alert will not be displayed.',
				'id'	=> $prefix.'link_url',
				'type'	=> 'text'
			),
			array(
				'name'		=> 'Type',
				'desc'		=> '',
				'id'		=> $prefix.'type',
				'type'		=> 'select',
				'options'	=> Array('Advisory (Background Color: #F79501, Text Color: #FFFFFF)' => 'advisory', 'Severe (Background Color: #FF0000, Text Color: #FFFFFF)' => 'severe'),
				'std'		=> 'advisory'
			),
			array(
				'name'		=> 'Background Color',
				'desc'		=> 'Example: #000000<br /> If left blank, the alert will default to the selected type\'s color scheme.',
				'id'		=> $prefix.'bg_color',
				'type'		=> 'text'
			),
			array(
				'name'		=> 'Text Color',
				'desc'		=> 'Example: #FFFFFF<br /> If left blank, the alert will default to the selected type\'s color scheme.',
				'id'		=> $prefix.'text_color',
				'type'		=> 'text'
			)
		);
	}
}

/**
 * Override base post type to add display options meta box
 *
 * @author Chris Conover
 **/
class Post extends CustomPostType
{
	public
		$name = 'post',
		$plural_name = 'Posts',
		$singular_name = 'Post',
		$add_new_item = 'Add New Post',
		$edit_item = 'Edit Post',
		$new_item = 'New Post',
		$public = True,
		$use_editor = True,
		$use_thumbnails = True,
		$use_order = False,
		$use_title = True,
		$use_metabox = True,
		$taxonomies = array('experts', 'post_tag', 'category'),
		$built_in = True,
		$show_in_rest = True;

	public function fields() {
		global $post;

		$primary_tag_options = Array();
		foreach(wp_get_post_tags($post->ID) as $tag) {
			$primary_tag_options[$tag->name] = $tag->term_id;
		}

	 	return Array(
				Array(
					'name'		=> 'Type',
					'desc'		=> 'Specify where this post will appear when it is published.<br/>
									<table><tr>
									<td>Normal:</td><td>Post will appear in top left column of home page.</td></tr><tr>
									<td>Promotional:</td><td>Post will appear in the top left column on the front page.</td></tr><tr>
									<td>Featured:</td><td>Post will appear at the top of the middle column on the front page.</td></tr></table>',
					'id'		=> 'display_type',
					'type'		=> 'radio',
					'options'	=> array(
										'Normal'					=> 'normal',
										'Promotional/Press Release' => 'promotional',
										'Featured' 					=> 'featured',
									),
				),
				Array(
					'name'	=> 'Promo',
					'desc'	=> 'Used to promote the story. Appears below the story title in the UCF Today email. <br><em>(Recommended word count is ~30 words)</em>',
					'id'	=> 'promo',
					'type'	=> 'textarea'
				),
				Array(
					'name'	=> 'Subtitle',
					'desc'	=> 'Appears below the post title on the single and featured story pages.',
					'id'	=> 'subtitle',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Deck',
					'desc'	=> 'Appears below the subtitle on the featured story page.',
					'id'	=> 'deck',
					'type'	=> 'textarea'
				),
				Array(
					'name'	=> 'Author Title',
					'desc'	=> 'Appears below the author\'s name on the single story page.',
					'id'	=> 'author_title',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Author Byline',
					'desc'	=> 'Appears in place of post author\'s name.',
					'id'	=> 'author_byline',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Author Bio',
					'desc'	=> 'Appears at the end of the story under the Author\'s name and title.',
					'id'	=> 'author_bio',
					'type'	=> 'wysiwyg'
				),
				Array(
					'name'	=> 'Source',
					'desc'	=> 'Appears below the date on the single story page and below the content on the featured story page.',
					'id'	=> 'source',
					'type'	=> 'textarea',
				),
				Array(
					'name'		=> 'Primary Tag',
					'desc'		=> 'Used to populate "More stories about" menu on the single story page.',
					'id'		=> 'primary_tag',
					'type'		=> 'select',
					'options'	=> $primary_tag_options
				),
				Array(
					'name'	=> 'Video URL',
					'desc'	=> 'If set, this video will replace the featured image on the single story page and display under the header on the featured story page.',
					'id'	=> 'video_url',
					'type'	=> 'text'
				),
			);
	}
}
/**
 * Expert guide
 *
 * @author Chris Conover
 **/
class Expert extends CustomPostType
{
	public
		$name           = 'expert',
		$plural_name    = 'Experts',
		$singular_name  = 'Expert',
		$add_new_item   = 'Add New Expert',
		$edit_item      = 'Edit Expert',
		$new_item       = 'New Expert',
		$use_editor		= True,
		$use_thumbnails = True,
		$use_metabox    = True;

	public function fields() {
		$prefix = $this->options('name').'_';
		return Array(
				Array(
					'name'	=> 'Name',
					'desc'	=> '',
					'id'	=> $prefix.'name',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Title',
					'desc'	=> '',
					'id'	=> $prefix.'title',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'College/School/Department',
					'desc'	=> '',
					'id'	=> $prefix.'association',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Contact Email',
					'desc'	=> '',
					'id'	=> $prefix.'email',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Contact Phone Number(s)',
					'desc'	=> 'Multiple phone numbers should be comma delimited',
					'id'	=> $prefix.'phone',
					'type'	=> 'text'
				)
			);
	}
}

/**
 * PhotoSet source
 *
 * @author Chris Conover
 **/
class PhotoSet extends CustomPostType
{
	public
		$name           = 'photoset',
		$plural_name    = 'Photo Sets',
		$singular_name  = 'Photo Set',
		$add_new_item   = 'Add New Photo Set',
		$edit_item      = 'Edit Photo Set',
		$new_item       = 'New Photo Set',
		$use_editor		= True,
		$use_thumbnails = True,
		$use_metabox    = True,

		$rewrite		= Array('slug' => 'ucf-in-photos'),

		$taxonomies     = Array('experts');

	public function fields() {
		$prefix = $this->options('name').'_';
		return Array(
				Array(
					'name'	=> 'Note',
					'desc'	=> 'Images for the set should be uploaded to the Image Gallery
								via the Featured Image section on the right. The image set
								as the Featured Image will be used as the set\'s representative
								image on the home page.',
					'id'	=> $prefix.'note',
					'type'	=> 'note'
					)
				);
	}
}

/**
 * Video source
 *
 * @author Chris Conover
 **/
class Video extends CustomPostType
{
	public
		$name           = 'video',
		$plural_name    = 'Videos',
		$singular_name  = 'Video',
		$add_new_item   = 'Add New Video',
		$edit_item      = 'Edit Video',
		$new_item       = 'New Video',
		$use_thumbnails = False,
		$use_metabox    = True,
		$use_editor		= True,

		$taxonomies		= Array('category', 'post_tag', 'experts');

	public function fields() {
		$prefix = $this->options('name').'_';
		return Array(
				Array(
					'name'	=> 'Video Link',
					'desc'	=> 'Example: http://www.youtube.com/watch?v=6rZ_UGsR2ZA',
					'id'	=> $prefix.'url',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Display on Main Page',
					'desc'	=> '',
					'id'	=> $prefix.'main_page',
					'type'	=> 'checkbox'
				)
			);
	}
}

/**
 * Profile for At Work Tag
 *
 * @author Chris Conover
 **/
class Profile extends CustomPostType
{
	public
		$name           = 'profile',
		$plural_name    = 'Profiles',
		$singular_name  = 'Profile',
		$add_new_item   = 'Add New Profile',
		$edit_item      = 'Edit Profile',
		$new_item       = 'New Profile',
		$use_editor     = True,
		$use_thumbnails = True,
		$use_metabox    = True,
		$taxonomies     = Array('groups');

	public function fields() {
		$prefix = $this->options('name').'_';
		return Array(
				Array(
					'name'	=> 'Job Title',
					'desc'	=> '',
					'id'	=> $prefix.'jobtitle',
					'type'	=> 'text'
				),

			);
	}
}

/**
 * External story
 *
 * @author Chris Conover
 **/
class ExternalStory extends CustomPostType
{
	public
		$name           = 'externalstory',
		$plural_name    = 'External Stories',
		$singular_name  = 'External Story',
		$add_new_item   = 'Add New External Story',
		$edit_item      = 'Edit External Story',
		$new_item       = 'New External Story',
		$use_thumbnails = False,
		$use_metabox    = True,
		$use_editor		= False,
		$taxonomies		= Array('category', 'experts');

	public function fields() {
		$prefix = $this->options('name').'_';
		return Array(
				Array(
					'name'	=> 'Link Text *',
					'desc'	=> '',
					'id'	=> $prefix.'text',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Link URL *',
					'desc'	=> '',
					'id'	=> $prefix.'url',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Link Description',
					'desc'	=> '',
					'id'	=> $prefix.'description',
					'type'	=> 'wysiwyg',
					'wysiwyg_media_buttons' => false,
					'wysiwyg_textarea_rows' => 5
				),
				Array(
					'name'	=> 'Source *',
					'desc'	=> '',
					'id'	=> $prefix.'source',
					'type'	=> 'text'
				),
				Array(
					'name'	=> 'Note',
					'desc'	=> '<em>Fields marked with an asterisk are required. If any of the required fields are left empty, this external story will not be displayed.</em>',
					'id'	=> $prefix.'note',
					'type'	=> 'note'
				)
			);
	}
}
?>
