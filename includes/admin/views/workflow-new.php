<?php
/**
 * New Workflow View
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/admin/views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php _e('Add New Workflow', 'workflow-automation'); ?></h1>
    
    <form id="wa-new-workflow-form" method="post" action="">
        <?php wp_nonce_field('wa_create_workflow', 'wa_nonce'); ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="workflow-name"><?php _e('Workflow Name', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="workflow_name" id="workflow-name" 
                               class="regular-text" required 
                               placeholder="<?php esc_attr_e('My Workflow', 'workflow-automation'); ?>">
                        <p class="description">
                            <?php _e('Give your workflow a descriptive name.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="workflow-description"><?php _e('Description', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <textarea name="workflow_description" id="workflow-description" 
                                  class="large-text" rows="3"
                                  placeholder="<?php esc_attr_e('What does this workflow do?', 'workflow-automation'); ?>"></textarea>
                        <p class="description">
                            <?php _e('Optional. Describe what this workflow does.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="workflow-template"><?php _e('Start From', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <select name="workflow_template" id="workflow-template" class="regular-text">
                            <option value=""><?php _e('Blank Workflow', 'workflow-automation'); ?></option>
                            <optgroup label="<?php esc_attr_e('Templates', 'workflow-automation'); ?>">
                                <option value="webhook-to-email"><?php _e('Webhook to Email', 'workflow-automation'); ?></option>
                                <option value="webhook-to-slack"><?php _e('Webhook to Slack', 'workflow-automation'); ?></option>
                                <option value="form-to-spreadsheet"><?php _e('Form to Google Sheets', 'workflow-automation'); ?></option>
                                <option value="email-digest"><?php _e('Daily Email Digest', 'workflow-automation'); ?></option>
                            </optgroup>
                        </select>
                        <p class="description">
                            <?php _e('Choose a template or start with a blank workflow.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <?php _e('Initial Status', 'workflow-automation'); ?>
                    </th>
                    <td>
                        <fieldset>
                            <label for="workflow-status-active">
                                <input type="radio" name="workflow_status" id="workflow-status-active" 
                                       value="active" checked>
                                <?php _e('Active', 'workflow-automation'); ?>
                            </label>
                            <br>
                            <label for="workflow-status-inactive">
                                <input type="radio" name="workflow_status" id="workflow-status-inactive" 
                                       value="inactive">
                                <?php _e('Inactive', 'workflow-automation'); ?>
                            </label>
                        </fieldset>
                        <p class="description">
                            <?php _e('Active workflows will run when triggered. Inactive workflows will not run.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php _e('Create Workflow', 'workflow-automation'); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=workflow-automation'); ?>" class="button">
                <?php _e('Cancel', 'workflow-automation'); ?>
            </a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#wa-new-workflow-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitButton = $form.find('.button-primary');
        
        // Disable submit button
        $submitButton.prop('disabled', true).text('<?php esc_attr_e('Creating...', 'workflow-automation'); ?>');
        
        // Prepare data
        var data = {
            name: $('#workflow-name').val(),
            description: $('#workflow-description').val(),
            status: $('input[name="workflow_status"]:checked').val(),
            template: $('#workflow-template').val()
        };
        
        // Create workflow via AJAX
        $.ajax({
            url: wa_admin.api_url + '/workflows',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wa_admin.nonce);
            },
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                // Redirect to workflow builder
                window.location.href = '<?php echo admin_url('admin.php?page=workflow-automation-builder&workflow='); ?>' + response.id;
            },
            error: function(xhr) {
                alert('<?php esc_attr_e('Failed to create workflow. Please try again.', 'workflow-automation'); ?>');
                $submitButton.prop('disabled', false).text('<?php esc_attr_e('Create Workflow', 'workflow-automation'); ?>');
            }
        });
    });
});
</script>