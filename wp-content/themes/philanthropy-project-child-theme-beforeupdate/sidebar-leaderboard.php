<?php
/**
 * The sidebar containing the leaderboard widgets.
 *
 * @package Reach
 */
if ( ! is_active_sidebar( 'leaderboard' ) ) {
    return;
}
?>
<div id="secondary" class="widget-area sidebar sidebar-leaderboard" role="complementary">
    <?php dynamic_sidebar( 'leaderboard' ) ?>
</div>