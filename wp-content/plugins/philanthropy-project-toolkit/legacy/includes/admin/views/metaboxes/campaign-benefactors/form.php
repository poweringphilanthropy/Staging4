<?php 
/**
 * Renders the campaign benefactors form.
 *
 * @since       1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a 
 */

$benefactor           = isset( $view_args[ 'benefactor' ] ) ? $view_args[ 'benefactor' ] : null;
$extension            = isset( $view_args['extension'] ) ? $view_args[ 'extension' ] : '';
$type                 = isset( $view_args['type'] ) ? $view_args[ 'type' ] : 'download';
$disable              = isset( $view_args['disable'] ) ? boolval($view_args[ 'disable' ]) : false;
$is_active_benefactor = is_null( $benefactor ) || $benefactor->is_active();

if ( is_null( $benefactor ) ) {
    $default_args = array(
        'index'                           => '_0',        
        'contribution_amount'             => '',
        'contribution_amount_is_per_item' => 0, 
        'date_created'                    => date_i18n( 'F d, Y' ), 
        'date_deactivated'                => 0,
    );

    $args = array_merge( $default_args, $view_args );
}
else {
    $args = array(
        'index'                           => $benefactor->campaign_benefactor_id,
        'contribution_amount'             => $benefactor->get_contribution_amount(), 
        'contribution_amount_is_per_item' => $benefactor->contribution_amount_is_per_item, 
        'date_created'                    => date_i18n( 'F d, Y', strtotime( $benefactor->date_created ) ), 
        'date_deactivated'                => '0000-00-00 00:00:00' == $benefactor->date_deactivated ? '' : date_i18n( 'F d, Y', strtotime( $benefactor->date_deactivated ) ),
    );  
}

$id_base   = 'campaign_'.$type.'_benefactor_' . $args[ 'index' ];
$name_base = '_campaign_benefactor[' . $args[ 'index' ] . ']';
$disable_attr = ($disable) ? 'disabled="disabled"' : '';
?>
<div id="<?php echo $id_base ?>" class="charitable-metabox-wrap charitable-benefactor-wrap" style="display: none;"> 
    <?php if ( is_null( $benefactor ) && !$disable ) : ?>
        <a class="charitable-benefactor-form-cancel" href="#"><?php _e( 'Cancel', 'charitable' ) ?></a>
    <?php endif ?>
    <p><strong><?php _e( 'Contribution Amount', 'charitable' ) ?></strong></p>
    <fieldset class="charitable-benefactor-contribution-amount">        
        <input type="text" id="<?php echo $id_base ?>_contribution_amount" class="contribution-amount" name="<?php echo $name_base ?>[contribution_amount]" value="<?php echo $args['contribution_amount'] ?>" placeholder="<?php _e( 'Enter amount. e.g. 10%, $2', 'charitable' ) ?>" <?php echo $disable_attr; ?> />       
        <select id="<?php echo $id_base ?>_contribution_amount_is_per_item" class="contribution-type" name="<?php echo $name_base ?>[contribution_amount_is_per_item]" <?php echo $disable_attr; ?>>
            <option value="1" <?php selected( 1, $args['contribution_amount_is_per_item'] ) ?>><?php _e( 'Apply to every matching item', 'charitable' ) ?></option>
            <option value="0" <?php selected( 0, $args['contribution_amount_is_per_item'] ) ?>><?php _e( 'Apply only once per purchase', 'charitable' ) ?></option>
        </select>
    </fieldset>
    <?php 
        // do_action( 'charitable_campaign_benefactor_form_extension_fields', $benefactor, $extension, $args[ 'index' ] );
    pp_toolkit_admin_view( 'metaboxes/campaign-benefactor-settings', array( 'benefactor' => $benefactor, 'index' => $args[ 'index' ], 'type' => $type, 'disable' => $disable ) );
    ?>
    <div class="charitable-benefactor-date-wrap cf">
        <label for="<?php echo $id_base ?>_date_created"><?php _e( 'Starting From:', 'charitable' ) ?>
            <input type="text" 
                id="<?php echo $id_base ?>_date_created" 
                name="<?php echo $name_base ?>[date_created]" 
                tabindex="3" class="charitable-datepicker" 
                data-date="<?php echo $args['date_created'] ?>"
                <?php echo $disable_attr; ?>
            />
        </label>
        <label for="<?php echo $id_base ?>_date_deactivated"><?php _e( 'Ending:', 'charitable' ) ?>
            <input type="text" 
                id="<?php echo $id_base ?>_date_deactivated" 
                name="<?php echo $name_base ?>[date_deactivated]"  
                placeholder="&#8734;" 
                tabindex="3" 
                class="charitable-datepicker" 
                data-date="<?php echo $args['date_deactivated'] ?>" 
                <?php echo $disable_attr; ?>
            />
        </label>
    </div>
</div>