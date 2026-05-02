<?php
function travel_bucket_list_theme_setup() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');

    // Register navigation menu
    register_nav_menus(array(
        'primary' => 'Primary Menu'
    ));

    // Create default categories for Visited and Dream
    if (!term_exists('visited', 'category')) {
        wp_insert_term('Visited', 'category', array('slug' => 'visited'));
    }
    if (!term_exists('dream', 'category')) {
        wp_insert_term('Dream', 'category', array('slug' => 'dream'));
    }
}
add_action('after_setup_theme', 'travel_bucket_list_theme_setup');

function travel_bucket_list_enqueue_scripts() {
    wp_enqueue_style('travel-bucket-list-style', get_stylesheet_uri());
    wp_enqueue_script('travel-bucket-list-script', get_template_directory_uri() . '/js/script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'travel_bucket_list_enqueue_scripts');

// Custom function to get travel status
function get_travel_status($post_id) {
    $categories = get_the_category($post_id);
    foreach ($categories as $cat) {
        if ($cat->slug == 'visited') return 'visited';
        if ($cat->slug == 'dream') return 'dream';
    }
    return 'dream'; // default
}

// Custom function to display map section
function display_map_section() {
    echo '<div class="map-section">';
    echo '<h2>World Map</h2>';
    // You can embed a static map or use a plugin for interactive map
    echo '<img src="' . get_template_directory_uri() . '/images/map.jpg" alt="World Map" style="max-width:100%; border-radius: 8px;">';
    echo '</div>';
}
?>