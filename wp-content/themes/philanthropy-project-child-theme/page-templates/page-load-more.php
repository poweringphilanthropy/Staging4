<?php
/**
 * Template name: Load More
 */

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$offset += 15;
$next = add_query_arg('offset', $offset, site_url('/load-more-campaigns/'));

$campaigns = Charitable_Campaigns::query([
//    'post__not_in' => [PHILANTHROPY_PROJECT_CAMPAIGN_ID],
    'offset'       => $offset
]);

// Add 15 more to run a successful comparison for the Load More button
$offset += 15;

charitable_template( 'campaign-load-more.php', array( 'campaigns' => $campaigns, 'columns' => 4 ) );

wp_reset_postdata();

if ($campaigns->max_num_pages > 1 && $campaigns->found_posts > $offset) : ?>

    <p class="center">
        <a class="button button-alt"
           href="<?= $next ?>">
            Load More
        </a>
    </p>

<?php endif;