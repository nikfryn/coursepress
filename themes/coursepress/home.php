<?php
/**
 * The home template file
 *
 * useful if you use static page for posts page
 * if doesn't exists, index.php will be used in this case
 * even if you select the page template - it will be ignored
 * 
 * @package CoursePress
 */
get_header();
?>

<div id="primary" class="content-area content-side-area">
    <main id="main" class="site-main" role="main">

        <?php if ( have_posts() ) : ?>

            <?php /* Start the Loop */ ?>
            <?php while ( have_posts() ) : the_post(); ?>

                <?php
                /* Include the Post-Format-specific template for the content.
                 * If you want to override this in a child theme, then include a file
                 * called content-___.php ( where ___ is the Post Format name ) and that will be used instead.
                 */
                get_template_part( 'content', get_post_format() );
                ?>

            <?php endwhile; ?>

            <?php coursepress_numeric_posts_nav( 'navigation-pagination' ); ?>

        <?php else : ?>

            <?php get_template_part( 'content', 'none' ); ?>

        <?php endif; ?>

    </main><!-- #main -->
</div><!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
