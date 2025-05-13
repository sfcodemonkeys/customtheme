<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header class="bg-dark text-white p-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="site-title mb-0"><?php bloginfo('name'); ?></h1>
            <nav class="navbar navbar-expand-lg navbar-light py-3">
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNavbar">
        <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'menu_class'     => 'navbar-nav ms-auto nav nav-pills',
          'walker'         => new Bootstrap_Nav_Pills_Walker(),
        ]);
        ?>
      </div>
    </nav>


            
        </div>
    </header>
    <div class="container mt-4">
        <div class="row">
            <main class="col-md-12">

