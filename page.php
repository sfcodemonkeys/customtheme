<?php get_header(); ?>

<div class="container mt-4">
    <div class="row">
        <!-- Main content -->
        <div class="col-md-8">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post(); ?>
                    <article class="mb-4">
                        <h1 class="mb-3"><?php the_title(); ?></h1>
                        <div class="page-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endwhile;
            else : ?>
                <p><?php _e('Page not found.'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <?php if (is_active_sidebar('main-sidebar')) : ?>
                <?php dynamic_sidebar('main-sidebar'); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

