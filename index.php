<?php 
/*
Plugin Name: WPMS data
Plugin URI:  https://github.com/
Description: For stuff that's magical
Version:     1.0
Author:      ALT Lab
Author URI:  http://
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


add_action('wp_enqueue_scripts', 'wpms_data_load_scripts');

function wpms_data_load_scripts() {                           
    $deps = array('jquery');
    $version= '1.0'; 
    $in_footer = true;    
    wp_enqueue_style( 'wpms-table-css', plugin_dir_url( __FILE__) . 'css/wpms-data-style.css');
    wp_enqueue_script('data-tables-js', '//cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js', $version, $in_footer); 
    wp_enqueue_style( 'data-tables-css', '//cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css');
    wp_enqueue_script('wpms-data-js', plugin_dir_url( __FILE__) . 'js/wpms-data.js', 'data-tables-js', $version, $in_footer); 
}

function wpms_data_theme_details(){
   // $blogs = get_sites([ "number" => 1000 ]);//might be dramatic at some point
   // foreach( $blogs as $b ){
   //     $b->blog_id 
   //     //Do stuff
   // }  
   global $wpdb; //THIS SEEMS MUCH FASTER THAN THE get_sites option 
   $blogs = $wpdb->get_results( "SELECT blog_id path FROM $wpdb->blogs ORDER BY blog_id" );
   $html = '';
   foreach ($blogs as $key => $blog_id) {   
      //$options = get_site($blog_id->path);
      //var_dump($options);
      $url = get_blog_option($blog_id->path, 'siteurl');
      $name = get_blog_option($blog_id->path, 'blogname');
      if(get_blog_option($blog_id->path, 'current_theme')){
               $theme = get_blog_option($blog_id->path, 'current_theme');
      } else {
               $theme = get_blog_option($blog_id->path, 'template');
      }
      $plugins = get_blog_option($blog_id->path, 'active_plugins');
      $admin = get_blog_option($blog_id->path, 'admin_email');
      $post_count = post_count_zero(get_blog_option($blog_id->path, 'post_count'));
      $page_count = post_count_zero(get_blog_option($blog_id->path, 'wpms_data_pages_count'));
      $the_plugins = '';
      if($plugins != ''){
         $the_plugins = wpms_data_plugin_namer($plugins);
      }
      $html .= "<tr class='site'>
                     <td><a href='{$url}'>{$name}</a></td>
                     <td>{$theme}</td>
                     <td>{$the_plugins}</td>
                     <td>{$post_count}</td>
                     <td>{$page_count}</td>
               </tr>";
   }
   return "<table id='theSites' class='display' style='width:100%''>
           <thead>
               <tr>
                   <th>Name</th>
                   <th>theme</th>
                   <th>plugins</th>
                   <th>posts</th> 
                   <th>pages</th>                
               </tr>
           </thead>
           <tbody>{$html}</tbody></table>";
}

   add_shortcode('wpms-theme-details', 'wpms_data_theme_details');

function post_count_zero($count){
   if ($count < 1 || $count == ''){
      return 'no data';
   } else {
      return $count;
   }
}

function wpms_data_plugin_namer($array){
   $plugin_names = '';
   foreach ($array as $key => $plugin) {
      // code...
      if(@get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin)){
         $plugin_data = @get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
         if(array_key_exists('Name', $plugin_data)){
            $plugin_name = $plugin_data['Name'];
            $plugin_names .= "<div class='plugin-item'>{$plugin_name}*</div>";
         }

      }
      
   }
   return $plugin_names;
}


//This is what I turned on at the network level to write the pages count to the wp_options table
// function wmps_page_count(){
//     $page_count  = wp_count_posts('page')->publish;
//     if ( get_option( 'wpms_data_pages_count' ) !== false ) {
//         update_option('wpms_data_pages_count', $page_count);
//     } else {
//         add_option('wpms_data_pages_count', $page_count);
//     }
// }

//Do that action on the transition of pages (but not anything else)
// add_action( 'transition_post_status', function ( $new_status, $old_status, $post )
// {

//     if( 'publish' == $new_status && 'publish' != $old_status && $post->post_type == 'page' ) {

//         wmps_page_count();

//     }
// }, 10, 3 );


//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

  //print("<pre>".print_r($a,true)."</pre>");
