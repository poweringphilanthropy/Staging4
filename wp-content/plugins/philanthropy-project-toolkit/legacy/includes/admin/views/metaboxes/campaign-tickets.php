<?php 
/**
 * Renders a benefactors addon metabox. Used by any plugin that utilizes the Benefactors Addon.
 *
 * @since       1.0.0
 * @author      Eric Daams
 * @package     Charitable/Admin Views/Metaboxes
 * @copyright   Copyright (c) 2017, Studio 164a 
 */
global $post;

// for now we just disable all input
$disable = true;

$extension = 'charitable-edd';
$type = 'ticket';
$benefactors = pp_get_campaign_download_benefactor( $post->ID, 'ticket', false );
$ended = charitable_get_campaign( $post->ID )->has_ended();
?>
<div class="charitable-metabox charitable-metabox-wrap">
    <?php 
    if ( empty( $benefactors ) ) : 

        if ( $ended ) : ?>

            <p><?php _e( 'You did not add any contribution rules.', 'charitable' ) ?></p>

        <?php else : ?>

            <p><?php _e( 'You have not added any contribution rules yet.', 'charitable' ) ?></p>

        <?php 
        endif;
    else :
        foreach ( $benefactors as $benefactor ) :

            $benefactor_object = Charitable_Benefactor::get_object( $benefactor, $extension );

            if ( $benefactor_object->is_active() ) {
                $active_class = 'charitable-benefactor-active'; 
            } elseif ( $benefactor_object->is_expired() ) {
                $active_class = 'charitable-benefactor-expired';
            } else {
                $active_class = 'charitable-benefactor-inactive';
            }

            ?>
            <div class="charitable-metabox-block charitable-benefactor <?php echo $active_class ?>">
                <?php  

                pp_toolkit_admin_view( 'metaboxes/campaign-benefactors/summary', array(
					'benefactor' => $benefactor_object,
					'extension' => $extension,
					'type' => $type,
					'disable' => $disable
				) );

				pp_toolkit_admin_view( 'metaboxes/campaign-benefactors/form', array(
					'benefactor' => $benefactor_object,
					'extension' => $extension,
					'type' => $type,
					'disable' => $disable
				) );
                ?>
            </div>
            <?php

        endforeach;
    endif;
    
    if(!$disable) 
    	pp_toolkit_admin_view( 'metaboxes/campaign-benefactors/form', array( 'benefactor' => null, 'extension' => $extension, 'type' => $type ) );
    
    if ( ! $ended && ! $disable ) :
    ?>
        <p><a href="#" class="button" data-charitable-toggle="campaign_<?php echo $type; ?>_benefactor__0"><?php _e( '+ Add New Contribution Rule', 'charitable' ) ?></a></p> 
    <?php 
    endif;
    ?>    
</div>