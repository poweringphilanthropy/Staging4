<?php 
/**
 * Renders the Campaign Parent metabox.
 *
 * @since       1.1.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a 
 */

global $post;

$campaigns = get_posts( array(
    'post_type' => Charitable::CAMPAIGN_POST_TYPE,
    'post_status' => 'publish',
    'posts_per_page' => -1
) );
?>
<div class="charitable-metabox">
    <select name="post_parent">
        <option value="0" <?php selected( 0, $post->post_parent ) ?>><?php _e( 'No parent campaign', 'charitable-ambassadors' ) ?></option>
        <?php foreach ( $campaigns as $campaign ) : ?>
            <option value="<?php echo $campaign->ID ?>" <?php selected( $campaign->ID, $post->post_parent ) ?>><?php echo $campaign->post_title ?></option>
        <?php endforeach ?>
    </select>
</div>