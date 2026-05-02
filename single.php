<?php get_header(); ?>

<div class="container">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article class="journal-post">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="featured-image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
                <h1><?php the_title(); ?></h1>
                <div class="meta">
                    <span>By <?php the_author(); ?> on <?php the_date(); ?></span>
                    <span class="status <?php echo get_travel_status(get_the_ID()); ?>"><?php echo ucfirst(get_travel_status(get_the_ID())); ?></span>
                </div>
                <div class="post-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php get_footer(); ?>