<?php get_header(); ?>

<?php
$items = travel_bucket_list_get_items();
$status_counts = array_count_values(wp_list_pluck($items, 'status'));
$dream_count = $status_counts['dream'] ?? 0;
$visited_count = $status_counts['visited'] ?? 0;
?>

<div class="container bucket-list-page">
    <section class="bucket-list-intro">
        <h2>Travel Bucket List</h2>
        <p>Choose the places you want to visit or have already visited. Use the tabs to switch between <strong>All</strong>, <strong>Dream</strong>, and <strong>Visited</strong>.</p>
    </section>

    <section class="bucket-list-overview">
        <div class="overview-copy">
            <p class="eyebrow">Organize your next adventure</p>
            <h3>Turn your travel goals into a clear plan.</h3>
            <p>This page combines destination ideas, progress tracking, and planning prompts so every trip feels more intentional and polished.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <span>Total destinations</span>
                <strong><?php echo count($items); ?></strong>
            </div>
            <div class="stat-card">
                <span>Dream list</span>
                <strong><?php echo esc_html($dream_count); ?></strong>
            </div>
            <div class="stat-card">
                <span>Visited places</span>
                <strong><?php echo esc_html($visited_count); ?></strong>
            </div>
        </div>
    </section>

    <div class="bucket-list-actions">
        <div class="bucket-list-tabs">
            <button class="bucket-tab active" data-status="all">All</button>
            <button class="bucket-tab" data-status="dream">Dream</button>
            <button class="bucket-tab" data-status="visited">Visited</button>
        </div>
    </div>

    <div class="bucket-list-layout">
        <aside class="bucket-list-sidebar">
            <div class="sidebar-panel">
                <h3>All countries</h3>
                <div class="country-scroll" id="country-scroll">
                    <?php if (!empty($items)) : ?>
                        <?php foreach ($items as $item) : ?>
                            <button class="country-link" data-status="<?php echo esc_attr($item['status']); ?>" data-target="#country-card-<?php echo esc_attr($item['id']); ?>">
                                <span><?php echo esc_html($item['title']); ?></span>
                                <span class="mini-status <?php echo esc_attr($item['status']); ?>"><?php echo ucfirst($item['status']); ?></span>
                            </button>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No countries found yet. Add posts for your destinations.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar-panel sidebar-filters">
                <h3>Quick filters</h3>
                <button class="filter-button active" data-status="all">Show all</button>
                <button class="filter-button" data-status="dream">Dream list</button>
                <button class="filter-button" data-status="visited">Visited now</button>
            </div>

            <div class="sidebar-panel sidebar-add-country">
                <h3>Add a country</h3>
                <form id="bucket-list-add-form" class="bucket-list-add-form">
                    <label for="bucket-country-input">Country</label>
                    <input list="bucket-country-list" id="bucket-country-input" name="country" placeholder="Type a country name" autocomplete="off">
                    <datalist id="bucket-country-list">
                        <?php foreach (get_bucket_list_countries() as $country) : ?>
                            <option value="<?php echo esc_attr($country['title']); ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <p class="bucket-list-helper">Select a country, then choose Dream or Visited.</p>
                    <div class="status-options">
                        <label><input type="radio" name="status" value="dream" checked> Dream</label>
                        <label><input type="radio" name="status" value="visited"> Visited</label>
                    </div>
                    <button type="submit" class="add-country-button">Add to bucket list</button>
                    <p class="bucket-list-form-message" id="bucket-form-message"></p>
                </form>
            </div>
        </aside>

        <main class="bucket-list-content">
            <div class="travel-cards js-travel-grid">
                <?php if (!empty($items)) : ?>
                    <?php foreach ($items as $item) : ?>
                        <article id="country-card-<?php echo esc_attr($item['id']); ?>" class="country-card" data-status="<?php echo esc_attr($item['status']); ?>">
                            <?php if (!empty($item['image'])) : ?>
                                <a class="card-image-link" href="<?php echo esc_url($item['permalink']); ?>">
                                    <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                                </a>
                            <?php else : ?>
                                <div class="card-image-placeholder">
                                    <span><?php echo esc_html($item['title']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="card-content">
                                <h3><a href="<?php echo esc_url($item['permalink']); ?>"><?php echo esc_html($item['title']); ?></a></h3>
                                <span class="status <?php echo esc_attr($item['status']); ?>"><?php echo ucfirst($item['status']); ?></span>
                                <p><?php echo esc_html($item['excerpt']); ?></p>
                                <div class="card-actions">
                                    <a class="card-button" href="<?php echo esc_url($item['permalink']); ?>">View details</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>No destinations are available. Add a post in the Travel categories.</p>
                <?php endif; ?>
            </div>
            <div class="show-more-wrapper">
                <button class="show-more-button" id="show-more-btn">Show More Countries ↓</button>
            </div>
            <div class="no-results-message hide">
                <p>No destinations match this filter yet. Try another tab or add more destinations.</p>
            </div>
        </main>
    </div>

    <section class="bucket-list-boost">
        <div class="boost-card">
            <h3>Plan with confidence</h3>
            <p>Use the Dream and Visited views to keep your bucket list fresh. Decide which destinations to book next, and use this page as a reference while you pack, save, and prepare.</p>
            <span class="chip">Trip planning</span>
        </div>
        <div class="boost-card">
            <h3>Keep your goals visible</h3>
            <p>Every country card is an inspiration point — make notes, save itineraries, or link journal posts to build a fuller travel story.</p>
            <span class="chip">Inspiration</span>
        </div>
        <div class="boost-card">
            <h3>Track progress quickly</h3>
            <p>Jump from the sidebar to each destination, review your visited places, and see what remains on your dream radar.</p>
            <span class="chip">Progress</span>
        </div>
    </section>

    <?php display_map_section(); ?>
</div>

<?php get_footer(); ?>