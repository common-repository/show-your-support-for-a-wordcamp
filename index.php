<?php
  /**
  * Plugin Name: Show your Support
  * Plugin URI: http://bornforlogic.com/
  * Description: Plugin to show your Support for an Event by updating your profile picture on Facebook and Twitter adding a badge.
  * Version: 1.0.2
  * Author: Harshad Mane
  * Author URI: http://bornforlogic.com/
  * License: GPLv3 or later
  */

  /**
  * Copyright (C) 2016  Harshad Mane (email : harshadmane@gmail.com)
  * This program is free software: you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation, either version 3 of the License, or
  * (at your option) any later version.

  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.

  * Please check <http://www.gnu.org/licenses/>.
  */

  class WPSys {

  /**
  * Function name: __construct()
  * Contructor to initialize code
  * Parameters: none
  */

  public function __construct(){

    // create custom plugin settings menu
    add_action('admin_menu', array($this, 'plugin_sys_create_menu'));
    add_action('admin_head', array($this, 'sys_custom_admin_styles'));
    add_action('init', array($this, 'ft_sys_include_file'));
    
    // define constants for abosolute and url path
    define('SYS_DIR_PATH', plugin_dir_path(__FILE__));
    define('SYS_URL_PATH', plugin_dir_url(__FILE__));   
  }
  
  /**
  * Function name: ft_sys_include_file()
  * Function to check if the page/post has shortcode then include file.
  * Parameters: none
  */

  public function ft_sys_include_file(){
    $page_set = esc_attr( get_option('sys_ft_default_page') );
    if( $page_set ) { 
    	require_once(SYS_DIR_PATH.'social_media.php');
    }
  }

  /**
  * Function name: plugin_sys_create_menu()
  * Function to create Show your Support in wp-admin
  * Parameters: none
  */

  public function plugin_sys_create_menu() {

  	//create new top-level menu
  	add_menu_page('SYS Settings', 'SYS Settings', 'manage_options', 'sys_settings', array($this,'plugin_sys_settings_page'));

  	//call register settings function
  	add_action( 'admin_init', array($this, 'register_plugin_sys_create_menu'));
  }

  /**
  * Function name: sys_custom_admin_styles()
  * Function to include custom styles to SYS Settings page
  * Parameters: none
  */

  public function sys_custom_admin_styles() {
    echo '<style type="text/css">
      span.placeholder{
        margin: 5px !important;
        font-size: .8em !important;
        word-wrap: normal !important;
        word-break: keep-all !important;
      }</style>';

    if(isset($_REQUEST['act']) == 'delete_cache'){
      $cachedir = SYS_DIR_PATH.'cache';
      $delete   = $this->deleteCache($cachedir);

      if($delete == 1){
        $delete_message = __('Cache Deleted!','sys');
      }else{
        $delete_message = __('Problem deleting Cache','sys');
      }
?>
  <div class="updated notice">
      <p><?php echo $delete_message;?></p>
    </div>
<?php
    }
  }

  /**
  * Function name: register_plugin_sys_create_menu()
  * Function to register settings
  * Parameters: none
  */

  public function register_plugin_sys_create_menu() {

    //register our settings
    register_setting( 'plugin-sys-settings-group', 'sys_app_id' );
    register_setting( 'plugin-sys-settings-group', 'sys_app_secret' );
    register_setting( 'plugin-sys-settings-group', 'sys_api_version' );
    register_setting( 'plugin-sys-settings-group', 'sys_callback_url' );
    register_setting( 'plugin-sys-settings-group', 'sys_privacy_page_url' );
    register_setting( 'plugin-sys-settings-group', 'sys_share_phrase' );
    register_setting( 'plugin-sys-settings-group', 'sys_twitter_share_message' );
    register_setting( 'plugin-sys-settings-group', 'sys_default_image' );
    register_setting( 'plugin-sys-settings-group', 'sys_overlay_image' );
    register_setting( 'plugin-sys-settings-group', 'sys_twitter_app_secret' );
    register_setting( 'plugin-sys-settings-group', 'sys_twitter_api_secret' );
    register_setting( 'plugin-sys-settings-group', 'sys_twitter_callback_url' );
    register_setting( 'plugin-sys-settings-group', 'sys_facebook_album_name' );
    register_setting( 'plugin-sys-settings-group', 'sys_ft_default_page' );
  }

  /**
  * Function name: plugin_sys_settings_page()
  * Function to update/display values on Show your Support settings admin page
  * Parameters: none
  */

  public function plugin_sys_settings_page() {

  if (isset($_POST["action"]) && $_POST["action"] == 'update') {
    update_option( 'sys_app_id', sanitize_text_field($_POST['sys_app_id'] ));
    update_option( 'sys_app_secret', sanitize_text_field($_POST['sys_app_secret'] ));
    update_option( 'sys_api_version', sanitize_text_field($_POST['sys_api_version'] ));
    update_option( 'sys_callback_url', sanitize_text_field($_POST['sys_callback_url'] ));
    update_option( 'sys_privacy_page_url', sanitize_text_field($_POST['sys_privacy_page_url'] ));
    update_option( 'sys_heading', sanitize_text_field($_POST['sys_heading'] ));
    update_option( 'sys_share_phrase', sanitize_text_field($_POST['sys_share_phrase']) );
    update_option( 'sys_twitter_share_message', sanitize_text_field($_POST['sys_twitter_share_message']));
    update_option( 'sys_para', sanitize_text_field($_POST['sys_para']));
    update_option( 'sys_twitter_app_secret', sanitize_text_field($_POST['sys_twitter_app_secret']));
    update_option( 'sys_twitter_api_secret', sanitize_text_field($_POST['sys_twitter_api_secret']));
    update_option( 'sys_twitter_callback_url', sanitize_text_field($_POST['sys_twitter_callback_url']));
    update_option( 'sys_facebook_album_name', sanitize_text_field($_POST['sys_facebook_album_name']));
    update_option( 'sys_ft_default_page', sanitize_text_field($_POST['sys_ft_default_page']));
      
  if(!empty($_FILES['sys_overlay_image']['tmp_name'])){
	move_uploaded_file($_FILES['sys_overlay_image']['tmp_name'], SYS_DIR_PATH.'images/' . $_FILES['sys_overlay_image']['name']);
    $path = SYS_URL_PATH.'images/'. sanitize_file_name($_FILES['sys_overlay_image']['name']);
    update_option( 'sys_overlay_image', $path );
    }
      
  if(!empty($_FILES['sys_default_image']['tmp_name'])){
    move_uploaded_file($_FILES['sys_default_image']['tmp_name'], SYS_DIR_PATH.'images/' . $_FILES['sys_default_image']['name']);
    $path = SYS_URL_PATH.'images/'.sanitize_file_name($_FILES['sys_default_image']['name']);
    update_option( 'sys_default_image', $path );
    }
?>
    
    <div class="updated notice">
      <p><?php _e('Settings Updated!','sys');?></p>
    </div>
<?php
    }
?>
    <div class="wrap">
      <h2><?php _e("Show your Support (Facebook/Twitter)","sys"); ?></h2>
      <p><strong><?php _e("* Use [ft_sys] shortcode in post/page","sys"); ?></strong></p>

      <form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">
      <?php settings_fields( 'plugin-sys-settings-group' ); ?>
      <?php do_settings_sections( 'plugin-sys-settings-group' ); ?>
    
      <table class="form-table">
        
        <tr valign="top">
        <th scope="row"><h1><?php _e('Facebook Settings','sys');?></h1></th>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e('Facebook App ID:','sys');?></th>
        <td><input type="text" name="sys_app_id" value="<?php echo esc_attr( get_option('sys_app_id') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Facebook App ID','sys');?></span></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Facebook App Secret:','sys');?></th>
        <td><input type="text" name="sys_app_secret" value="<?php echo esc_attr( get_option('sys_app_secret') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Facebook App Secret','sys');?></span></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Facebook API Version:','sys');?></th>
        <td><input type="text" name="sys_api_version" value="<?php echo esc_attr( get_option('sys_api_version') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Facebook API Version','sys');?></span></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Facebook Callback URL:','sys');?></th>
        <td><input type="text" name="sys_callback_url" value="<?php echo esc_url( get_option('sys_callback_url') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Facebook Callback URL','sys');?></span></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Facebook Privacy Page URL:','sys');?></th>
        <td><input type="text" name="sys_privacy_page_url" value="<?php echo esc_url( get_option('sys_privacy_page_url') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Privacy Page URL','sys');?></span></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Facebook Album Name:','sys');?></th>
        <td><input type="text" name="sys_facebook_album_name" value="<?php echo esc_attr( get_option('sys_facebook_album_name') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Facebook Creates Album for your updated picture, Please Enter Facebook Album Name','sys');?></span></td>
        </tr>

        <tr valign="top">
         <th scope="row"><h1><?php _e('Twitter Settings','sys');?></h1></th>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Twitter Consumer Key (API Key):','sys');?></th>
        <td><input type="text" name="sys_twitter_app_secret" value="<?php echo esc_attr( get_option('sys_twitter_app_secret') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Twitter Consumer Key (API Key)','sys');?></span></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Twitter Consumer Secret (API Secret):','sys');?></th>
        <td><input type="text" name="sys_twitter_api_secret" value="<?php echo esc_attr( get_option('sys_twitter_api_secret') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Twitter API Secret','sys');?></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Twitter Callback URL:','sys');?></th>
        <td><input type="text" name="sys_twitter_callback_url" value="<?php echo esc_url( get_option('sys_twitter_callback_url') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Twitter Callback URL','sys');?></td>
        </tr>

        <tr valign="top">
         <th scope="row"><h1><?php _e('General Settings','sys');?></h1></th>
        </tr>

	<tr valign="top">
        <th scope="row"><?php _e('Page link of Shortcode used:','sys');?></th>
        <td><input type="text" name="sys_ft_default_page" value="<?php echo esc_url( get_option('sys_ft_default_page') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Page link where shortcode [ft_sys] is used','sys');?></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Heading:','sys');?></th>
        <td><input type="text" name="sys_heading" value="<?php echo esc_attr( get_option('sys_heading') ); ?>" style="width:400px;"/><br/><span class="placeholder"><?php _e('Enter Header title to appear on Screen 1. Ex: Show your support for WordCamp Event','sys');?></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Excerpt:','sys');?></th>
        <td><textarea style="width:400px;height:200px;" name="sys_para"><?php echo esc_textarea( get_option('sys_para') ); ?></textarea><br/><span class="placeholder"><?php _e('Enter Excerpt to appear on Screen 1. Ex. Show your support for WordCamp Event','sys');?></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Default Share message for Facebook:','sys');?></th>
        <td><textarea style="width:400px;height:200px;" name="sys_share_phrase"><?php echo esc_textarea( get_option('sys_share_phrase') ); ?></textarea><br/><span class="placeholder"><?php _e('Enter default text to appear on users timeline on Facebook.','sys');?></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Default Share message for Twitter (140 chars):','sys');?></th>
        <td><textarea style="width:400px;height:200px;" name="sys_twitter_share_message"><?php echo esc_textarea( get_option('sys_twitter_share_message') ); ?></textarea><br/><span class="placeholder"><?php _e('Enter default text to appear on users timeline on Twitter, 140 chars.','sys');?></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Default Image (480 X 480):','sys');?><br/>
        <?php _e("This is default image that will appear on Screen 1.","sys");?>
        </th>
        <td>
        
        <input type="file" name="sys_default_image" />
        <?php if(!empty(get_option('sys_default_image'))){ ?>
        <br/>
        <img src="<?php echo esc_url( get_option('sys_default_image')); ?>" height="480" width="480" style="border:1px solid black;"/>
<?php 
      } 
?>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e('Overlay Image (480 X 480):','sys');?><br/>
        <?php _e("This is overlay image that will appear on your Facebook Profile pic.","sys");?>
        </th>
        <td>
        <input type="file" name="sys_overlay_image" />
        <?php if(!empty(get_option('sys_overlay_image'))){ ?>
        <br/>
        <img src="<?php echo esc_url( get_option('sys_overlay_image')); ?>" height="480" width="480" style="border:1px solid black;"/>
<?php   
      } 

      $cachedir   = SYS_DIR_PATH.'cache';  
      $countfiles = $this->countfiles($cachedir);
      if($countfiles > 0 ){
?>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e('Delete Cache:','sys');?></th>
        <td><a href="<?php echo admin_url('admin.php?page=sys_settings&act=delete_cache');?>"><input type="button" name="sys_delete_cache" value="<?php _e('Delete Cache','sys'); ?>" class="button button-primary"/></a><br/><span class="placeholder"><?php _e('Total: '.$countfiles.' file(s)','sys');?></span></td>
        </tr>
<?php
  }else{
?>

        </td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e('Delete Cache:','sys');?></th>
        <td><span class="placeholder"><?php _e('No files to delete!','sys');?></span></td>
        </tr>
<?php
}
?>
    </table>

<?php 
    submit_button(); 
?>
    </form>
    </div>
<?php 
}

  /**
  * Function name: deleteCache();
  * Function to remove Cache
  * Parameters: $dir
  */

  public function deleteCache($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir."/".$object))
           rrmdir($dir."/".$object);
         else
           unlink($dir."/".$object); 
       } 
     } 
   } 
   return 1;
  }

  /**
  * Function name: countfiles();
  * Function to count image files in Cache directory
  * Parameters: $dir
  */

  public function countfiles($dir) {

  $count = 0;
  if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir."/".$object))
          $count++;
         else
          $count++; 
       } 
     } 
   } 
   return $count;
  }

}// end of Class WPSys

//Create object for WPSys Class
$wpSys = new WPSys();