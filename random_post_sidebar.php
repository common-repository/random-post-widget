<?php
/* 
Plugin name: Random Post Widget
Description: A sidebar widget plugin to create a link to a random published post on your blog.
Author: Nelson Pavlosky
Version: 0.1
Author URI: http://nelson.freeculture.org

This plugin borrows from the Google Search widget and the "random post" link in the top left
of the Contempt theme (as it was before being put on Wordpress.com).  
This plugin was originally based on the "Random post link" plugin at
<http://www.vituperation.com/ranpost>, but I've excised all of its code because 
get_permalink() is better.

INSTRUCTIONS

1. Copy this file into the plugins directory in your WordPress installation 
   (wp-content/plugins).
2. Log in to WordPress administration. Go to the Plugins page and Activate 
   this plugin.

    Copyright 2006 Nelson Pavlosky  (email : nelson@freeculture.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


// Put functions into one big function we'll call at the plugins_loaded
// action. This ensures that all required plugin functions are defined.
function widget_random_post_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;	
		
	// This is the function that outputs our little random post box.
	function widget_random_post($args) {
	
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_random_post');
		$title = $options['title'];
		$description = $options['description'];
		
		// The following lines generate our output. 
		
		// $before_widget, $after_widget, $before_title, and $after_title
		// are required for compatibility with various themes.
		// See <http://automattic.com/code/widgets/plugins/>
		echo $before_widget . $before_title . $title . $after_title;

		// According to <http://codex.wordpress.org/wpdb_Class>, 
		// the $wpdb class may need to be scoped to global when being called
		// from a plugin or external script (as opposed to a template).
		global $wpdb;

		// This grabs the ID and post title of every post that is published 
		// and public (excluding pages, drafts, and password-protected posts)
		// and puts them in an array that is sorted randomly. 
		$randompost = $wpdb->get_results("SELECT ID, post_title 
											FROM $wpdb->posts
											WHERE post_status= 'publish' AND post_type = 'post' AND post_password = ''
											ORDER BY RAND() LIMIT 0, 1");
		// This puts the first post in the random array into a variable.
		$r=$randompost[0];

		echo ('<ul><li>' . $description . '<br />' . '<a href="' . get_permalink($r->ID) . '">' . $r->post_title . '</a>' . '</li></ul>');

		echo $after_widget;
		
	}
	
	// This is the function that outputs the form to let the users edit
	// the widget's title. It's an optional feature that users cry for.
	function widget_random_post_control() {

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_random_post');
		if ( !is_array($options) )
			$options = array('title'=>'Random Post', 'description'=>'Jump to a random post from the archives: ');
		if ( $_POST['random_post-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['random_post-title']));
			$options['description'] = strip_tags(stripslashes($_POST['random_post-description']));
			update_option('widget_random_post', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$description = htmlspecialchars($options['description'], ENT_QUOTES);
		
		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		echo '<p style="text-align:right;"><label for="random_post-title">Title: <input style="width: 200px;" id="random_post-title" name="random_post-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="random_post-description">Link Text: <input style="width: 200px;" id="random_post-description" name="random_post-description" type="text" value="'.$description.'" /></label></p>';
		echo '<input type="hidden" id="random_post-submit" name="random_post-submit" value="1" />';
	}
		
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget('Random Post', 'widget_random_post');

	// This registers our optional widget control form. Because of this
	// our widget will have a button that reveals a 300x100 pixel form.
	register_widget_control('Random Post', 'widget_random_post_control', 300, 100);

}

// Run our code later in case this loads prior to any required plugins.
add_action('plugins_loaded', 'widget_random_post_init');

?>
