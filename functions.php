<?php

/** Top posts widget * */
include('widget-top_posts.php');

// register projects filtering widget and area for it
function register_top_posts_widget() {
    register_widget('Top_Posts_Widget');
}

add_action('widgets_init', 'register_top_posts_widget');




