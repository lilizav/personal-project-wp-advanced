<?php get_header(); ?>

<div class="container">
    <div class="toggle">
        <button id="show-all" class="active">All</button>
        <button id="show-visited">Visited</button>
        <button id="show-dream">Dream</button>
    </div>

    <div class="gallery">
        <?php
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'category_name' => '' // will be filtered by JS
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $status = get_travel_status(get_the_ID());
                ?>
                <div class="country-card" data-status="<?php echo $status; ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title(); ?>">
                    <?php endif; ?>
                    <div class="card-content">
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <span class="status <?php echo $status; ?>"><?php echo ucfirst($status); ?></span>
                        <p><?php the_excerpt(); ?></p>
                    </div>
                </div>
            <?php endwhile;
        else : ?>
            <p>No travel destinations found. Start adding your bucket list!</p>
        <?php endif; wp_reset_postdata(); ?>
    </div>

    <?php display_map_section(); ?>
</div>

<?php get_footer(); ?>