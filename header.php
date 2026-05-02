<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo home_url(); ?>">✈️ My Travel List</a>
            </div>
            <nav class="nav">
                <?php wp_nav_menu(array('theme_location' => 'primary')); ?>
            </nav>
        </div>
    </header>

    <div class="hero">
        <div class="container">
            <h1 class="site-title">My Travel List</h1>
            <p class="site-tagline">Explore the world, one destination at a time</p>
        </div>
    </div>