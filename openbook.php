<?php
/*
Plugin Name: Open Book
Description: Append '?secretpreview' to the URL of any draft post to view it before it's published.
Author:      Daniel Reeves and Bethany Soule
Author URI:  http://dreev.es
*/

$openbook_magic_string = "secretpreview";

#error_log("null [".is_admin()."]\n", 3, '/tmp/php.log');

if(!is_admin()) add_action('init',       'openbook_init');
if(is_admin())  add_action('admin_menu', 'openbook_meta_box');

function openbook_init() {
  global $openbook_magic_string;
  #error_log("init: $openbook_magic_string\n", 3, '/tmp/php.log');
  if(isset($_GET[$openbook_magic_string])) {
    #error_log("magic string; adding fake publish filter\n", 3, '/tmp/php.log');
    add_filter('posts_results', 'openbook_fake_publish');
  }
}

function openbook_fake_publish($posts) {
  #error_log("fake: ".get_permalink($posts[0]->ID)."\n", 3, '/tmp/php.log');
  if($posts[0]->post_status === 'publish') { # already published
    #error_log("redirect to ".get_permalink($posts[0]->ID), 3, '/tmp/php.log');
    wp_redirect(get_permalink($posts[0]->ID), 301);
    exit;
  } else
    #error_log("temporarily changing ".$posts[0]->post_status." to publish\n",3,
    #          '/tmp/php.log');
    $posts[0]->post_status = 'publish'; # temporarily set status to published
  return $posts;
}

# Content for meta box
function openbook_preview_link($post) {
  global $openbook_magic_string;
  $url = get_permalink($post->ID);
  if(!in_array($post->post_status, array('publish'))) 
    $url = add_query_arg(array($openbook_magic_string => ''), $url);
  echo "<p><a href='$url'>$url</a></p>";
}

# Register meta box
function openbook_meta_box() {
  add_meta_box('openbookpreviewlink', 'Open Book: Public URL for this post', 
               'openbook_preview_link', 'post', 'normal', 'high');
}

?>
