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
        <div class="container header-inner">
            <div class="logo">
                <a href="<?php echo home_url(); ?>">✈️ Wander List</a>
            </div>
            <nav class="nav">
                <?php wp_nav_menu(array('theme_location' => 'primary')); ?>
            </nav>
        </div>
    </header>

    <div class="hero">
        <div class="container">
            <h1 class="site-title">Plan your next adventure</h1>
            <p class="site-tagline">Track visited places, explore dream destinations, and keep your bucket list organized.</p>
        </div>
    </div>