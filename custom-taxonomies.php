<?php

/**
 * Abstract class for defining custom taxonomies.
 *
 **/
abstract class CustomTaxonomy {
	public
		$name			= 'custom_taxonomy',

		// Do not register the taxonomy with the post type here.
		// Register it on the `taxonomies` attribute of the post type in
		// custom-post-types.php
		$object_type	= Array(),

		$general_name		= 'Post Tags',
		$singular_name      = 'Post Tag',
		$search_items       = 'Search Tags',
		$popular_items      = 'Popular Tags',
		$all_times          = 'All Tags',
		$parent_item        = 'Parent Category',
		$parent_item_colon  = 'Parent Category:',
		$edit_item          = 'Edit Tag',
		$update_item        = 'Update Tag',
		$add_new_item       = 'Add New Tag',
		$new_item_name      = 'New Tag Name',
		$menu_name          = NULL,

		$public                = True,
		$show_in_name_menus    = NULL,
		$show_ui               = NULL,
		$show_tagcloud         = NULL,
		$hierarchical          = False,
		$update_count_callback = '',
		$rewrite               = True,
		$query_var             = NULL,
		$capabilities          = Array();

	function __construct() {
		if(is_null($this->show_in_name_menus)) $this->show_in_name_menus = $this->public;
		if(is_null($this->show_ui)) $this->show_ui = $this->public;
		if(is_null($this->show_tagcloud)) $this->show_tagcloud = $this->show_ui;
		if(is_null($this->menu_name)) $this->menu_name = $this->general_name;
	}

	public function options($key){
		$vars = get_object_vars($this);
		return $vars[$key];
	}

	public function labels() {
		return Array(
				'name'                       => _x($this->options('general_name'), 'taxonomy general name'),
				'singular_name'              => _x($this->options('singular_name'), 'taxonomy singular name'),
				'search_items'               => __($this->options('search_items')),
				'popular_items'              => __($this->options('popular_items')),
				'all_items'                  => __($this->options('all_items')),
				'parent_item'                => __($this->options('popular_items')),
				'parent_item_colon'          => __($this->options('parent_item_colon')),
				'edit_item'                  => __($this->options('edit_item')),
				'update_item'                => __($this->options('update_item')),
				'add_new_item'               => __($this->options('add_new_item')),
				'new_item_name'              => __($this->options('new_item_name')),
				'separate_items_with_commas' => __($this->options('separate_items_with_commas')),
				'add_or_remove_items'        => __($this->options('add_or_remove_items')),
				'choose_from_most_used'      => __($this->options('choose_from_most_used')),
				'menu_name'                  => __($this->options('menu_name'))
				);
	}

	public function register() {
		$args = Array(
				'labels'                => $this->labels(),
				'public'                => $this->options('public'),
				'show_in_nav_menus'     => $this->options('show_in_nav_menus'),
				'show_ui'               => $this->options('show_ui'),
				'show_tagcloud'         => $this->options('show_tagcloud'),
				'hierarchical'          => $this->options('hierarchical'),
				'update_count_callback' => $this->options('update_count_callback'),
				'rewrite'               => $this->options('rewrite'),
				'query_var'             => $this->options('query_var'),
				'capabilities'          => $this->options('capabilities')
			);
		register_taxonomy($this->options('name'), $this->options('object_type'), $args);
	}
}

/**
 * Expert taxonomy
 *
 * @package default
 * @author Chris Conover
 **/
class Experts extends CustomTaxonomy{
	public
		$name				= 'experts',
		$general_name		= 'Post Experts',
		$singular_name 		= 'Post Expert',
		$search_items		= 'Search Experts',
		$popular_items		= 'Popular Experts',
		$all_times			= 'All Experts',
		$parent_item		= 'Parent Expert',
		$parent_item_colon	= 'Parent Expert:',
		$edit_item			= 'Edit Expert',
		$update_item		= 'Update Expert',
		$add_new_item		= 'Add New Expert',
		$new_item_name		= 'New Expert Name',
		$menu_name			= NULL,
		$hierarchical		= True;
}

/**
 * Group profiles
 *
 * @package default
 * @author Chris Conover
 **/
class Groups extends CustomTaxonomy{
	public
		$name				= 'groups',
		$general_name		= 'Post Groups',
		$singular_name 		= 'Post Group',
		$search_items		= 'Search Groups',
		$popular_items		= 'Popular Groups',
		$all_times			= 'All Groups',
		$parent_item		= 'Parent Group',
		$parent_item_colon	= 'Parent Group:',
		$edit_item			= 'Edit Group',
		$update_item		= 'Update Group',
		$add_new_item		= 'Add New Group',
		$new_item_name		= 'New Group Name',
		$menu_name			= NULL,
		$hierarchical		= False;
}

/**
 * Source profiles
 *
 * @package default
 * @author RJ Bruneel
 **/
class Sources extends CustomTaxonomy{

	public
		$name				= 'sources',
		$general_name		= 'Post Sources',
		$singular_name 		= 'Post Source',
		$search_items		= 'Search Sources',
		$popular_items		= 'Popular Sources',
		$all_times			= 'All Sources',
		$parent_item		= 'Parent Source',
		$parent_item_colon	= 'Parent : Source',
		$edit_item			= 'Edit Source',
		$update_item		= 'Update Source',
		$add_new_item		= 'Add New Source',
		$new_item_name		= 'New Source Name',
		$menu_name			= NULL,
		$hierarchical		= True;
}


?>
