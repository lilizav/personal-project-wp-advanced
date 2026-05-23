<?php get_header(); ?>

<div class="container single-post-container">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article class="country-detail-post">
                <div class="detail-header">
                    <div class="detail-title-area">
                        <h1><?php the_title(); ?></h1>
                        <div class="detail-meta">
                            <span class="status <?php echo get_travel_status(get_the_ID()); ?>"><?php echo ucfirst(get_travel_status(get_the_ID())); ?></span>
                        </div>
                    </div>
                </div>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="detail-featured-image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="detail-content">
                    <?php the_content(); ?>
                </div>

                <div class="detail-back">
                    <a href="<?php echo esc_url(home_url()); ?>" class="back-link">← Back to bucket list</a>
                </div>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php get_footer(); ?>