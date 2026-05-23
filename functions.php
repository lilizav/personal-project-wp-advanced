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

    // Auto-create posts for static countries if they don't exist
    travel_bucket_list_create_static_country_posts();
}

function travel_bucket_list_create_static_country_posts() {
    $static_countries = travel_bucket_list_static_countries();

    foreach ($static_countries as $country) {
        $existing_place = get_page_by_title($country['title'], OBJECT, 'place');
        if ($existing_place) {
            continue;
        }

        $existing_post = get_page_by_title($country['title'], OBJECT, 'post');
        $cat_id = travel_bucket_list_get_category_id($country['status']);

        if ($existing_post && $existing_post->ID) {
            wp_update_post(array(
                'ID' => $existing_post->ID,
                'post_type' => 'place',
            ));

            if ($cat_id) {
                wp_set_post_categories($existing_post->ID, array($cat_id), false);
            }

            continue;
        }

        $post_data = array(
            'post_title'   => $country['title'],
            'post_content' => $country['excerpt'],
            'post_status'  => 'publish',
            'post_type'    => 'place',
            'post_category'=> $cat_id ? array($cat_id) : array(),
        );

        wp_insert_post($post_data);
    }
}

add_action('after_setup_theme', 'travel_bucket_list_theme_setup');

add_action('init', function() {
    if (!get_transient('travel_bucket_list_static_posts_created')) {
        travel_bucket_list_create_static_country_posts();
        set_transient('travel_bucket_list_static_posts_created', 1, WEEK_IN_SECONDS);
    }
}, 20);

add_action('init', 'travel_bucket_list_register_places_cpt');
function travel_bucket_list_register_places_cpt() {
    $labels = array(
        'name'               => 'Places',
        'singular_name'      => 'Place',
        'menu_name'          => 'Places',
        'name_admin_bar'     => 'Place',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Place',
        'new_item'           => 'New Place',
        'edit_item'          => 'Edit Place',
        'view_item'          => 'View Place',
        'all_items'          => 'All Places',
        'search_items'       => 'Search Places',
        'parent_item_colon'  => 'Parent Places:',
        'not_found'          => 'No places found.',
        'not_found_in_trash' => 'No places found in Trash.',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-location-alt',
        'supports'           => array('title', 'editor', 'excerpt', 'thumbnail'),
        'taxonomies'         => array('category'),
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'places'),
    );

    register_post_type('place', $args);
}

function travel_bucket_list_enqueue_scripts() {
    wp_enqueue_style('travel-bucket-list-style', get_stylesheet_uri());
    wp_enqueue_script('travel-bucket-list-script', get_template_directory_uri() . '/js/script.js', array('jquery'), '1.0', true);
    wp_localize_script('travel-bucket-list-script', 'TravelBucketList', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('travel_bucket_list_add_country'),
    ));
}
add_action('wp_enqueue_scripts', 'travel_bucket_list_enqueue_scripts');

add_action('wp_ajax_travel_bucket_list_add_country', 'travel_bucket_list_add_country');
add_action('wp_ajax_nopriv_travel_bucket_list_add_country', 'travel_bucket_list_add_country');

function travel_bucket_list_get_category_id($slug) {
    $term = get_term_by('slug', $slug, 'category');
    if ($term) {
        return $term->term_id;
    }

    $result = wp_insert_term(ucfirst($slug), 'category', array('slug' => $slug));
    if (is_wp_error($result)) {
        return 0;
    }

    return isset($result['term_id']) ? $result['term_id'] : 0;
}

function travel_bucket_list_add_country() {
    check_ajax_referer('travel_bucket_list_add_country', 'security');

    $country = trim(sanitize_text_field($_POST['country'] ?? ''));
    $status = sanitize_text_field($_POST['status'] ?? '');

    if (empty($country) || !in_array($status, array('dream', 'visited'), true)) {
        wp_send_json_error('Please enter a valid country and select Dream or Visited.');
    }

    $static_countries = get_bucket_list_countries();
    foreach ($static_countries as $item) {
        if (strcasecmp($item['title'], $country) === 0) {
            wp_send_json_error('That country already exists in the built-in list.');
        }
    }

    $cat_id = travel_bucket_list_get_category_id($status);
    $existing_place = get_page_by_title($country, OBJECT, 'place');
    if ($existing_place && $existing_place->ID) {
        if ($cat_id) {
            wp_set_post_categories($existing_place->ID, array($cat_id), false);
        }
        wp_send_json_success('Place already exists. Status updated.');
    }

    $existing_post = get_page_by_title($country, OBJECT, 'post');
    if ($existing_post && $existing_post->ID) {
        wp_update_post(array(
            'ID' => $existing_post->ID,
            'post_type' => 'place',
        ));
        if ($cat_id) {
            wp_set_post_categories($existing_post->ID, array($cat_id), false);
        }
        wp_send_json_success('Existing country converted to a Place and status updated.');
    }

    $post_data = array(
        'post_title'   => $country,
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'place',
        'post_category'=> $cat_id ? array($cat_id) : array(),
    );

    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id) || !$post_id) {
        wp_send_json_error('Unable to add the selected country.');
    }

    wp_send_json_success('Country added to your bucket list.');
}

// Custom function to get travel status
function get_travel_status($post_id) {
    $categories = get_the_category($post_id);
    foreach ($categories as $cat) {
        if ($cat->slug == 'visited') return 'visited';
        if ($cat->slug == 'dream') return 'dream';
    }
    return 'dream'; // default
}

// Static list of travel destinations for the bucket list page
function get_bucket_list_countries() {
    return array(
        array('slug' => 'japan', 'title' => 'Japan', 'status' => 'dream', 'excerpt' => 'From neon Tokyo to serene Kyoto, savor unforgettable culture and cuisine.'),
        array('slug' => 'italy', 'title' => 'Italy', 'status' => 'dream', 'excerpt' => 'Explore historic piazzas, coastal drives, and world-class cuisine from Rome to Venice.'),
        array('slug' => 'new-zealand', 'title' => 'New Zealand', 'status' => 'dream', 'excerpt' => 'Adventure through dramatic landscapes, fjords, and outdoor experiences in Aotearoa.'),
        array('slug' => 'canada', 'title' => 'Canada', 'status' => 'dream', 'excerpt' => 'Discover wide-open wilderness, vibrant cities, and glacier lakes across the north.'),
        array('slug' => 'iceland', 'title' => 'Iceland', 'status' => 'dream', 'excerpt' => 'Chase waterfalls, volcanoes, and the northern lights on a dramatic island adventure.'),
        array('slug' => 'greece', 'title' => 'Greece', 'status' => 'visited', 'excerpt' => 'Relax on sun-soaked islands, visit ancient ruins, and enjoy Mediterranean cuisine.'),
        array('slug' => 'south-africa', 'title' => 'South Africa', 'status' => 'dream', 'excerpt' => 'Combine safari, beaches, and vibrant culture along the Garden Route and beyond.'),
        array('slug' => 'peru', 'title' => 'Peru', 'status' => 'dream', 'excerpt' => 'Hike the Inca Trail, visit Machu Picchu, and taste the Andes food scene.'),
        array('slug' => 'australia', 'title' => 'Australia', 'status' => 'dream', 'excerpt' => 'Experience iconic coastlines, wildlife, and city life from Sydney to the Outback.'),
        array('slug' => 'france', 'title' => 'France', 'status' => 'visited', 'excerpt' => 'Enjoy elegant wine regions, world-class art, and charming villages in every region.'),
        array('slug' => 'spain', 'title' => 'Spain', 'status' => 'dream', 'excerpt' => 'Feel the rhythm of tapas bars, historic architecture, and warm Mediterranean shores.'),
        array('slug' => 'thailand', 'title' => 'Thailand', 'status' => 'dream', 'excerpt' => 'Taste street food, island-hop, and explore temples in a lively cultural destination.'),
        array('slug' => 'morocco', 'title' => 'Morocco', 'status' => 'dream', 'excerpt' => 'Wander ancient medinas, desert dunes, and colorful souks in North Africa.'),
        array('slug' => 'brazil', 'title' => 'Brazil', 'status' => 'dream', 'excerpt' => 'Discover rainforest, carnival energy, and sweeping coastal landscapes.'),
        array('slug' => 'norway', 'title' => 'Norway', 'status' => 'dream', 'excerpt' => 'Chase fjords, northern light vistas, and elegant Scandinavian design.'),
        array('slug' => 'portugal', 'title' => 'Portugal', 'status' => 'dream', 'excerpt' => 'Enjoy coastal charm, historic cities, and balanced European style.'),
        array('slug' => 'switzerland', 'title' => 'Switzerland', 'status' => 'dream', 'excerpt' => 'Ski alpine peaks, ride scenic railways, and savor refined mountain hospitality.'),
        array('slug' => 'egypt', 'title' => 'Egypt', 'status' => 'dream', 'excerpt' => 'Stand before the pyramids, cruise the Nile, and explore ancient temples.'),
        array('slug' => 'costa-rica', 'title' => 'Costa Rica', 'status' => 'dream', 'excerpt' => 'Find rainforests, volcanoes, and eco-friendly beach escapes in Central America.'),
        array('slug' => 'usa', 'title' => 'United States', 'status' => 'visited', 'excerpt' => 'A diverse mix of cities, national parks, and unforgettable road trip routes.'),
        array('slug' => 'kenya', 'title' => 'Kenya', 'status' => 'dream', 'excerpt' => 'Witness dramatic safari wildlife, Rift Valley scenery, and coastal calm.'),
        array('slug' => 'turkey', 'title' => 'Turkey', 'status' => 'dream', 'excerpt' => 'Blend ancient history, vibrant markets, and Mediterranean coastlines into one trip.'),
        array('slug' => 'argentina', 'title' => 'Argentina', 'status' => 'dream', 'excerpt' => 'Taste steak, tango through Buenos Aires, and explore Patagonia wilderness.'),
        array('slug' => 'india', 'title' => 'India', 'status' => 'dream', 'excerpt' => 'Experience colorful festivals, rich history, and unforgettable cuisine.'),
        array('slug' => 'vietnam', 'title' => 'Vietnam', 'status' => 'dream', 'excerpt' => 'Journey through lantern-filled streets, rice terraces, and coast-to-coast flavors.'),
        array('slug' => 'united-kingdom', 'title' => 'United Kingdom', 'status' => 'dream', 'excerpt' => 'Explore London, the countryside, coastal cliffs, and historic castles.'),
        array('slug' => 'china', 'title' => 'China', 'status' => 'dream', 'excerpt' => 'See ancient heritage, bustling cities, and iconic landscapes like the Great Wall.'),
        array('slug' => 'south-korea', 'title' => 'South Korea', 'status' => 'dream', 'excerpt' => 'Discover modern cities, temples, mountain scenery, and rich cuisine.'),
        array('slug' => 'mexico', 'title' => 'Mexico', 'status' => 'dream', 'excerpt' => 'Enjoy colorful culture, coastal resorts, and ancient archaeological sites.'),
        array('slug' => 'indonesia', 'title' => 'Indonesia', 'status' => 'dream', 'excerpt' => 'Island-hop across beaches, volcanoes, rice terraces, and tropical forests.'),
        array('slug' => 'philippines', 'title' => 'Philippines', 'status' => 'dream', 'excerpt' => 'Relax on white-sand beaches, explore islands, and enjoy warm island life.'),
        array('slug' => 'malaysia', 'title' => 'Malaysia', 'status' => 'dream', 'excerpt' => 'Combine jungle adventure, modern cities, and vibrant food scenes.'),
        array('slug' => 'uae', 'title' => 'United Arab Emirates', 'status' => 'dream', 'excerpt' => 'Experience futuristic cities, desert adventures, and luxury coastlines.'),
        array('slug' => 'chile', 'title' => 'Chile', 'status' => 'dream', 'excerpt' => 'Travel from desert landscapes to glaciers and vibrant urban culture.'),
        array('slug' => 'russia', 'title' => 'Russia', 'status' => 'dream', 'excerpt' => 'Explore historic cities, dramatic landscapes, and cultural heritage.'),
        array('slug' => 'poland', 'title' => 'Poland', 'status' => 'dream', 'excerpt' => 'Discover charming cities, castles, and scenic lakes in Central Europe.'),
        array('slug' => 'czech-republic', 'title' => 'Czech Republic', 'status' => 'dream', 'excerpt' => 'Wander Prague’s old town, medieval castles, and peaceful countryside.'),
        array('slug' => 'croatia', 'title' => 'Croatia', 'status' => 'dream', 'excerpt' => 'Enjoy Adriatic coastlines, historic towns, and national park islands.'),
    );
}

// Custom function to display map section
function display_map_section() {
    echo '<div class="map-section">';
    echo '<h2>World Map</h2>';
    echo '<div class="map-image-wrap">';
    echo '<img src="' . get_template_directory_uri() . '/images/map.jpg" alt="World Map">';
    echo '</div>';
    echo '</div>';
}

function travel_bucket_list_static_countries() {
    return array(
        array(
            'id' => 'static-italy',
            'title' => 'Italy',
            'status' => 'dream',
            'excerpt' => 'Visualize historic piazzas, coastal villages, and world-class cuisine.',
            'permalink' => '#country-card-static-italy',
            'image' => '',
        ),
        array(
            'id' => 'static-japan',
            'title' => 'Japan',
            'status' => 'dream',
            'excerpt' => 'Experience rich culture, modern design, and serene temples.',
            'permalink' => '#country-card-static-japan',
            'image' => '',
        ),
        array(
            'id' => 'static-new-zealand',
            'title' => 'New Zealand',
            'status' => 'dream',
            'excerpt' => 'Hike dramatic landscapes, fjords, and mountains in style.',
            'permalink' => '#country-card-static-new-zealand',
            'image' => '',
        ),
        array(
            'id' => 'static-france',
            'title' => 'France',
            'status' => 'visited',
            'excerpt' => 'Stroll Parisian streets, vineyards, and charming countryside towns.',
            'permalink' => '#country-card-static-france',
            'image' => '',
        ),
        array(
            'id' => 'static-jamaica',
            'title' => 'Jamaica',
            'status' => 'visited',
            'excerpt' => 'Soak up tropical beaches, reggae culture, and warm island energy.',
            'permalink' => '#country-card-static-jamaica',
            'image' => '',
        ),
        array(
            'id' => 'static-canada',
            'title' => 'Canada',
            'status' => 'dream',
            'excerpt' => 'Discover scenic national parks, lakes, and vibrant cities.',
            'permalink' => '#country-card-static-canada',
            'image' => '',
        ),
        array(
            'id' => 'static-iceland',
            'title' => 'Iceland',
            'status' => 'dream',
            'excerpt' => 'Chase waterfalls, glaciers, and the northern lights.',
            'permalink' => '#country-card-static-iceland',
            'image' => '',
        ),
        array(
            'id' => 'static-thailand',
            'title' => 'Thailand',
            'status' => 'visited',
            'excerpt' => 'Enjoy vibrant street food, temples, beaches, and city life.',
            'permalink' => '#country-card-static-thailand',
            'image' => '',
        ),
        array(
            'id' => 'static-brazil',
            'title' => 'Brazil',
            'status' => 'dream',
            'excerpt' => 'Feel carnival energy, rainforest adventures, and coastal culture.',
            'permalink' => '#country-card-static-brazil',
            'image' => '',
        ),
        array(
            'id' => 'static-spain',
            'title' => 'Spain',
            'status' => 'dream',
            'excerpt' => 'Feel the rhythm of tapas bars, historic architecture, and warm Mediterranean shores.',
            'permalink' => '#country-card-static-spain',
            'image' => '',
        ),
        array(
            'id' => 'static-greece',
            'title' => 'Greece',
            'status' => 'dream',
            'excerpt' => 'Relax on island terraces, stroll ancient ruins, and savor seaside dining.',
            'permalink' => '#country-card-static-greece',
            'image' => '',
        ),
        array(
            'id' => 'static-australia',
            'title' => 'Australia',
            'status' => 'dream',
            'excerpt' => 'Experience iconic beaches, urban culture, and outback wilderness.',
            'permalink' => '#country-card-static-australia',
            'image' => '',
        ),
        array(
            'id' => 'static-morocco',
            'title' => 'Morocco',
            'status' => 'dream',
            'excerpt' => 'Wander colorful medinas, desert dunes, and coastal kasbahs.',
            'permalink' => '#country-card-static-morocco',
            'image' => '',
        ),
        array(
            'id' => 'static-india',
            'title' => 'India',
            'status' => 'dream',
            'excerpt' => 'Discover vibrant markets, spiritual sites, and bold cuisine.',
            'permalink' => '#country-card-static-india',
            'image' => '',
        ),
        array(
            'id' => 'static-canary-islands',
            'title' => 'Canary Islands',
            'status' => 'dream',
            'excerpt' => 'Enjoy sunny coastlines, volcanic scenery, and relaxed island life.',
            'permalink' => '#country-card-static-canary-islands',
            'image' => '',
        ),
        array(
            'id' => 'static-south-africa',
            'title' => 'South Africa',
            'status' => 'dream',
            'excerpt' => 'Combine safari adventure, coastal drives, and city experiences.',
            'permalink' => '#country-card-static-south-africa',
            'image' => '',
        ),
        array(
            'id' => 'static-portugal',
            'title' => 'Portugal',
            'status' => 'dream',
            'excerpt' => 'Savor historical cities, dramatic coastlines, and refined cuisine.',
            'permalink' => '#country-card-static-portugal',
            'image' => '',
        ),
        array(
            'id' => 'static-netherlands',
            'title' => 'Netherlands',
            'status' => 'dream',
            'excerpt' => 'Cycle through canals, charming towns, and colorful tulip fields.',
            'permalink' => '#country-card-static-netherlands',
            'image' => '',
        ),
        array(
            'id' => 'static-norway',
            'title' => 'Norway',
            'status' => 'dream',
            'excerpt' => 'Chase fjords, northern lights, and scenic coastal roadways.',
            'permalink' => '#country-card-static-norway',
            'image' => '',
        ),
        array(
            'id' => 'static-singapore',
            'title' => 'Singapore',
            'status' => 'dream',
            'excerpt' => 'Blend modern design, lush gardens, and vibrant city cuisine.',
            'permalink' => '#country-card-static-singapore',
            'image' => '',
        ),
        array(
            'id' => 'static-switzerland',
            'title' => 'Switzerland',
            'status' => 'dream',
            'excerpt' => 'Enjoy alpine peaks, lakes, and refined European mountain towns.',
            'permalink' => '#country-card-static-switzerland',
            'image' => '',
        ),
        array(
            'id' => 'static-argentina',
            'title' => 'Argentina',
            'status' => 'dream',
            'excerpt' => 'Taste steak, dance tango, and explore Patagonia wilderness.',
            'permalink' => '#country-card-static-argentina',
            'image' => '',
        ),
        array(
            'id' => 'static-costa-rica',
            'title' => 'Costa Rica',
            'status' => 'dream',
            'excerpt' => 'Find rainforest adventure, volcano treks, and surf-friendly beaches.',
            'permalink' => '#country-card-static-costa-rica',
            'image' => '',
        ),
        array(
            'id' => 'static-egypt',
            'title' => 'Egypt',
            'status' => 'dream',
            'excerpt' => 'Visit pyramids, Nile landscapes, and ancient temple cities.',
            'permalink' => '#country-card-static-egypt',
            'image' => '',
        ),
        array(
            'id' => 'static-turkey',
            'title' => 'Turkey',
            'status' => 'dream',
            'excerpt' => 'Explore historic bazaars, coastal towns, and cultural crossroads.',
            'permalink' => '#country-card-static-turkey',
            'image' => '',
        ),
        array(
            'id' => 'static-south-korea',
            'title' => 'South Korea',
            'status' => 'dream',
            'excerpt' => 'Experience tech-forward cities, temples, and culinary diversity.',
            'permalink' => '#country-card-static-south-korea',
            'image' => '',
        ),
        array(
            'id' => 'static-ireland',
            'title' => 'Ireland',
            'status' => 'dream',
            'excerpt' => 'Discover emerald landscapes, coastal cliffs, and historic castles.',
            'permalink' => '#country-card-static-ireland',
            'image' => '',
        ),
        array(
            'id' => 'static-mexico',
            'title' => 'Mexico',
            'status' => 'dream',
            'excerpt' => 'Dive into vibrant culture, coastal resorts, and ancient ruins.',
            'permalink' => '#country-card-static-mexico',
            'image' => '',
        ),
        array(
            'id' => 'static-usa',
            'title' => 'United States',
            'status' => 'visited',
            'excerpt' => 'A diverse mix of cities, national parks, and iconic routes.',
            'permalink' => '#country-card-static-usa',
            'image' => '',
        ),
        array(
            'id' => 'static-venezuela',
            'title' => 'Venezuela',
            'status' => 'dream',
            'excerpt' => 'Chase dramatic waterfalls, Amazon rainforest, and Caribbean coast.',
            'permalink' => '#country-card-static-venezuela',
            'image' => '',
        ),
    );
}

function travel_bucket_list_get_items() {
    $items = array();
    $query = new WP_Query(array(
        'post_type' => array('post', 'place'),
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $status = get_travel_status(get_the_ID());
            $items[] = array(
                'id' => 'post-' . get_the_ID(),
                'title' => get_the_title(),
                'status' => $status,
                'excerpt' => wp_trim_words(get_the_excerpt(), 20, '...'),
                'permalink' => get_permalink(),
                'image' => has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium') : '',
            );
        }
    }
    wp_reset_postdata();

    // Auto-detect images in the theme `images/` folder for items missing an image.
    // Matching strategy: sanitized title (slug) and fallback to id-derived slug.
    foreach ($items as &$item) {
        if (empty($item['image'])) {
            $candidates = array();
            // slug from title
            if (!empty($item['title'])) {
                $candidates[] = sanitize_title($item['title']);
            }
            // slug from id (remove common prefixes like static- or post-)
            if (!empty($item['id'])) {
                $idSlug = preg_replace('/^(static-|post-)/', '', $item['id']);
                $candidates[] = $idSlug;
            }

            $extensions = array('jpg', 'jpeg', 'png', 'webp');
            foreach ($candidates as $base) {
                foreach ($extensions as $ext) {
                    $rel = '/images/' . $base . '.' . $ext;
                    $abs = get_template_directory() . $rel;
                    if (file_exists($abs)) {
                        $item['image'] = get_template_directory_uri() . $rel;
                        break 2; // stop both loops when found
                    }
                }
            }
        }
    }

    return $items;
}
?>