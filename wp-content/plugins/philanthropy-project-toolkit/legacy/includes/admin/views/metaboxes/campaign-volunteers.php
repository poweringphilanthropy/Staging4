<?php
global $post;
$volunteers = get_post_meta( $post->ID, '_campaign_volunteers', true );

// echo "<pre>";
// print_r($volunteers);
// echo "</pre>";
?>
<div id="" class="charitable-metabox-wrap">    
    <table id="charitable-campaign-volunteers" class="widefat charitable-campaign-volunteers pp-repeatable-fields">
        <thead>
            <tr class="table-header">
                <th colspan="4"><label for="campaign_volunteers"><?php _e('Volunteer Tasks', 'pp-toolkit'); ?></label></th>
            </tr>
            <tr>   
                <th  class="description-col"><?php _e('Task Description', 'pp-toolkit'); ?></th>
                <th class="remove-col"></th>        
            </tr>
        </thead>        
        <tbody>
            <!-- Template -->
            <tr data-index="{?}" class="default repeatable-field empty-row screen-reader-text">
                <td class="amount-col">
                    <input type="text" class="campaign_volunteers" name="_campaign_volunteers[{?}][need]" value="" placeholder="<?php _e('Task', 'pp-toolkit'); ?>" />
                </td>
                <td class="remove-col"><span class="dashicons-before dashicons-dismiss pp-remove-row"></span></td>
            </tr>


            <?php 
            if(!empty($volunteers) && is_array($volunteers)){
            foreach ($volunteers as $i => $task) : ?>
            <tr data-index="<?php echo $i; ?>" class="default repeatable-field">

                <td class="amount-col">
                    <input type="text" class="campaign_volunteers" name="_campaign_volunteers[<?php echo $i; ?>][need]" value="<?php echo $task['need']; ?>" placeholder="<?php _e('Task', 'pp-toolkit'); ?>" />
                </td>
                <td class="remove-col"><span class="dashicons-before dashicons-dismiss pp-remove-row"></span></td>

            </tr>
            <?php endforeach; 
            } else {   
            ?>      
            <tr data-index="0" class="default repeatable-field">

                <td class="amount-col">
                    <input type="text" class="campaign_volunteers" name="_campaign_volunteers[0][need]" value="" placeholder="<?php _e('Task', 'pp-toolkit'); ?>" />
                </td>
                <td class="remove-col" data-pp-toolkit-remove-row="volunteer-task"><span class="dashicons-before dashicons-dismiss pp-remove-row"></span></td>

            </tr>
            <?php } ?>                  
        </tbody>
        <tfoot>
            <tr>                
                <td colspan="2"><a class="button pp-add-row" href="#" ><?php _e('+ Add a Volunteer Task', 'pp-toolkit'); ?></a></td>                
            </tr>
        </tfoot>
    </table>  
</div>