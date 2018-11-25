<?php if ( has_post_thumbnail( get_the_ID() ) ) : ?>
    <div class="edd-download-image">
        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute() ?>">
            <?php echo get_the_post_thumbnail( get_the_ID(), 'campaign-thumbnail-medium' ); ?>
        </a>
    </div>
<?php endif; ?>
