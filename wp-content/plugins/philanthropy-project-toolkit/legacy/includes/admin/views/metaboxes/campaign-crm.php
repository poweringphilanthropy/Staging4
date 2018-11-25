<?php 
global $post;
$crm_id_on_save = get_post_meta( $post->ID, 'post_sub_campaign_crm_id', true );


?>

<div class="charitable-metabox charitable-metabox-wrap">
<table class="widefat">
		<tbody>
						<tr>
				<th>Crm id</th>
				<td><input type="text" name="crm_id" value="<?php echo $crm_id_on_save;?>"></td>
			</tr>
					</tbody>
	</table>

</div>