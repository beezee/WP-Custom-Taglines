<?php
/*
 Plugin Name: Custom Taglines
 Plugin URI: http://www.workinginboxershorts.com/wordpress-custom-taglines
 Description: Adds an option for each post and page to use a custom tagline that overrides the sitewide tagline.
 Author: Brian Zeligson
 Version: 1
 Author URI: http://www.workinginboxershorts.com

 ==
 Copyright 2011 - present date  Brian Zeligson 

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
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/*register_activation_hook( __FILE__, bz_custom_taglines_is_on );

function bz_custome_taglines_is_on() {

}

register_deactivation_hook( __FILE__, bz_custom_taglines_is_off );

function bz_custom_taglines_is_off() {

}*/

/* Define the custom box */

// WP 3.0+
// add_action( 'add_meta_boxes', 'bz_taglines_add_custom_box' );

// backwards compatible
add_action( 'admin_init', 'bz_taglines_add_custom_box', 1 );

/* Do something with the data entered */
add_action( 'save_post', 'bz_taglines_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function bz_taglines_add_custom_box() {
    add_meta_box( 
        'bz_taglines_sectionid',
        __( 'Custom Tagline', 'bz_taglines_textdomain' ),
        'bz_taglines_inner_custom_box',
        'post' 
    );
}

/* Prints the box content */
function bz_taglines_inner_custom_box( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'bz_taglines_nonce' );

  // The actual fields for data entry
  echo '<label for="bz_taglines_enabled">';
       _e("Use a custom tagline?", 'bz_taglines_textdomain' );
  echo '</label> ';
  $isItEnabled = get_post_meta($post->ID, 'bz_taglines_enabled', true);
  echo '<input type="checkbox" style="margin-right:60px;" id="bz_taglines_enabled" name="bz_taglines_enabled" value="yes"'; if ($isItEnabled == 'yes') echo 'checked="yes"'; echo '" />';

  
  echo '<label for="bz_taglines_content">';
       _e("Custom Tagline Content", 'bz_taglines_textdomain' );
  echo '</label> ';
  $taglineContent = get_post_meta($post->ID, 'bz_taglines_content', true);
  echo '<input type="text" id="bz_taglines_content" size="90" name="bz_taglines_content" value="'; if ($taglineContent != '') echo $taglineContent; echo '" size="25" />';
  
  }

/* When the post is saved, saves our custom data */
function bz_taglines_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['bz_taglines_nonce'], plugin_basename( __FILE__ ) ) )
      return;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data

  $bzCustomTaglineEnabled = $_POST['bz_taglines_enabled'];
  $bzCustomTaglinesContent = $_POST['bz_taglines_content'];
  
  update_post_meta($post_id, 'bz_taglines_enabled', $bzCustomTaglineEnabled);
  update_post_meta($post_id, 'bz_taglines_content', $bzCustomTaglinesContent);

}

add_filter('bloginfo','bz_taglines_filter',10,2);

function bz_taglines_filter($info, $show)
{
	global $post;
	if ($show == 'description') {
		if (is_page() or is_post())
				{
				$isItEnabled = get_post_meta($post->ID, 'bz_taglines_enabled', true);
				$taglineContent = get_post_meta($post->ID, 'bz_taglines_content', true);
				if ($isItEnabled == 'yes') $info = $taglineContent;
				}
			}
	return $info;
}