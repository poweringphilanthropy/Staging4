<?php
/**
 * The sidebar containing the campaigns widget area.
 *
 * @package Reach
 */
if ( ! is_active_sidebar( 'sidebar_tpp_campaign' ) ) {
    return;
}
?>
<div id="secondary" class="widget-area sidebar sidebar-campaign" role="complementary">
    <?php dynamic_sidebar( 'sidebar_tpp_campaign' ) ?>
</div>