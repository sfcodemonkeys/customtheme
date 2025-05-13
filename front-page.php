<?php get_header(); ?>

<div class="container mt-4">
    <div class="row">
        <!-- Main content -->
        <div class="col-md-8">
            <div class="row">
                <?php
                $query = new WP_Query(array(
                    'post_type' => 'post',
                    'posts_per_page' => 4
                ));

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post(); ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php the_post_thumbnail_url('medium'); ?>" class="card-img-top" alt="<?php the_title(); ?>">
                                <?php endif; ?>
                                <p class="card-meta text-muted small mb-2">
                                        <?php echo get_the_date(); ?> | <?php the_author(); ?>
                                    </p>
                                <div class="card-body">
                                    <h5 class="card-title"><?php the_title(); ?></h5>
                                    <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                                    
                                    <a href="<?php the_permalink(); ?>" class="btn btn-success btn-sm">Read more</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata();
                else : ?>
                    <p><?php _e('No posts found.'); ?></p>
                <?php endif; ?>
            </div>
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

