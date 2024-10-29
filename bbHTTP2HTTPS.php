<?php
/*
Plugin Name: bbHTTP2HTTPS
Plugin URI: http://www.burobjorn.nl
Description: This plugin will attempt to convert a wordpress mu database blogs siteurl and home options to use https instead of http
Version: 1.0
Author: Bjorn Wijers <burobjorn [at] burobjorn [dot] nl>
Author URI: http://www.burobjorn.nl
*/

/*
* Copyright 2008 Bjorn Wijers
* This file is part of bbHTTP2HTTPS.
* bbHTTP2HTTPS is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
* bbHTTP2HTTPS is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty 
* of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
* You should have received a copy of the GNU General Public License along with bbHTTP2HTTPS; 
* if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
* The latest source code is available at http://www.burobjorn.nl
* 
* Contact: Bjorn Wijers <burobjorn [AT] burobjorn [DOT] nl>
* 
* Description: This plugin will attempt to convert a wordpress mu database blogs siteurl and home options to use https instead of http
* 
*/


/**
 * bbHTTP2HTTPS is a class/plugin which strives to convert a wordpress mu database blogs siteurl and home options to use https instead of http
 * @package bbHTTP2HTTPS
 * @author Bjorn Wijers
 * @copyright Bjorn Wijers 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 **/
class bbHTTP2HTTPS {

  /**
   * reference to the wordpress wpdb database object
   * @var object reference to wpdb
   * @access private
  **/
  var $wpdb;
    
  
  /**
   * Constructor (backwards compatible with php4..)
   * Calls the _setClassVars function to set the class variables
   * and calls the _setWPHooks in order to set the appropriate hooks.
   * @access public
   **/
  function bbHTTP2HTTPS() 
  {
    
    $this->_setupClassVars();
    $this->_setWPHooks();
  }  
  
  
  /**
   * Set the defaults for the class variables
   * @access private
  **/
  function _setupClassVars() 
  {
    global $wpdb;
    $this->wpdb =& $wpdb;  // reference to the wordpress database object
  }
  
  
  /**
   * Set the Wordpress specific filter and action hooks
   * @access private
  **/
  function _setWPHooks()
  {
    // Insert the addToMenu sink into the plugin hook list for 'admin_menu'
    add_action('admin_menu', array(&$this, '_addToMenu'));
  }


  /**
   * Sink function for the 'admin_menu' hook
   * Makes it easy to add optional pages as a sub-option of the top level menu items
   * @access private
   **/
  function _addToMenu() 
  {
    if( $this->isWPMU() ) {
      add_submenu_page('wpmu-admin.php', __('bbHTTP2HTTPS'), __('bbHTTP2HTTPS'), 'edit_plugins', basename(__FILE__), array(&$this, 'showInterface') );
    }
  }
  
  /**
   * Builds the interface and handles the different steps of the conversion.
   * Basically the core of this plugin
   * Called by the add_submenu_page hook.
   * @access public
   **/
  function showInterface() 
  {
    $html  = "<div class='wrap'>\n";
    $html .= "<h2>bbHTTP2HTTPS</h2>\n";
    $html .= "<p>Press the button below after you made sure you have a working backup of your database. 
      After pressing the button a log will be shown, please copy this as a reference in case you have any issues since it will help you debug.</p>\n";
    if( isset($_POST['bbhttp2https']) ) {
      $html .= $this->processBlogOptions();
    }
    $html  .= "<form action =\"\" method=\"post\"><input type=\"submit\" name=\"bbhttp2https\" value=\"HTTP2HTTPS\" /></form>\n";
    $html  .="</div>\n";
    echo $html;  
  }


  /**
   * Transform all existing active(!) blogs from http to https
   * by changing their siteurl, home and fileupload site options
   * in their options table
   */
  function processBlogOptions()
  {
    // retrieve all public blog ids
    $all_site_blogs = get_blog_list($start = 0, $num = 'all');
    $log = '';
      if( is_array($all_site_blogs) && sizeof($all_site_blogs) ) {
        foreach ($all_site_blogs as $blog) {
          if ( array_key_exists('blog_id', $blog) ) {
            switch_to_blog($blog['blog_id']); 
            $log .= "Switched to (id: {$blog['blog_id']}): " . get_bloginfo('name') . "<br />\n"; 
            $log .= "Continue processing options: <br />\n"; 
            $log .= $this->transform_option('siteurl', false);
            $log .= $this->transform_option('home', false);
            $log .= $this->transform_option('fileupload_url', false);
            $log .= "finished processing options for this blog <br />\n";
            $log .= "---------------------------------------------------------------------<br />\n";  
            $this->flush_rewrite_rules(); 
            restore_current_blog();
          }
        }
      }
    return $log; 
  } 


  /**
   * Flushes the permalinks
   * and makes sure the new https urls will work 
   * 
   * @access public
   * @return void
   */
  function flush_rewrite_rules() 
  {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }


  /** 
   * Transform a given option from http to https and log it
   * for the user to see.
   *
   * @access public
   * @return string logged message or echo directly
   */
  function transform_option($option_name, $echo = true) 
  {
    $current_option_value = get_option($option_name);
    $new_option_value     = $this->http2https($current_option_value);   
   
    $result               = update_option($option_name, $new_option_value);
    $update_msg = ($result) ? 'succesful!' : 'failed!';

    $msg = " - $option_name change from: $current_option_value to: $new_option_value $update_msg <br /> \n";  
    
    if($echo) {
      echo $msg;
    } else {
      return $msg;
    }
  }


  /**
   * Replaces http:// with https:// in a non empty string
   *
   * @access public
   * @return string url (not changed on empty)
   */
  function http2https($url) 
  {
    if( ! empty($url) && is_string($url) ) {
      return str_ireplace('http://', 'https://', $url);
    } else {
      return $url;
    }
  }

   
   /**
   * Checks if we're dealing with Wordpress MU or not by checking the existance of wpmu-settings.php 
   * 
   * @access public
   * @return boolean TRUE when its Wordpress MU or FALSE when not 
   **/
  function isWPMU() { 
    return file_exists( ABSPATH . '/wpmu-settings.php'); 
  }
}

// initialize..
$bbHTTP2HTTPS = new bbHTTP2HTTPS();
?>
