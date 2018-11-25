<?php  
$campaign = $view_args['campaign'];

$terms = wp_get_post_terms( $campaign->ID, 'campaign_group' );

if(empty($terms))
	return;

$term = current($terms);
// echo "<pre>";
// print_r($term);
// echo "</pre>";
?>
<div class="campaign-term">
	<?php echo $term->name; ?>
</div>