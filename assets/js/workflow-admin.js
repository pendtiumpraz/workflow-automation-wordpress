/**
 * Admin JavaScript for Workflow Automation
 */

(function($) {
    'use strict';

    // Copy to clipboard functionality
    $(document).on('click', '.copy-webhook-url', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $input = $button.siblings('input');
        
        // Select the text
        $input.select();
        
        // Copy to clipboard
        try {
            document.execCommand('copy');
            
            // Show feedback
            var originalText = $button.text();
            $button.text('Copied!');
            
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        } catch (err) {
            console.error('Failed to copy:', err);
        }
    });

    // Delete workflow confirmation
    $(document).on('click', '.delete-workflow', function(e) {
        if (!confirm(wa_admin.i18n.confirm_delete)) {
            e.preventDefault();
            return false;
        }
    });

    // Test workflow
    $(document).on('click', '.test-workflow', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var workflowId = $button.data('workflow-id');
        
        $button.prop('disabled', true).text('Testing...');
        
        // Make API call
        wp.apiRequest({
            path: 'wa/v1/executions/trigger',
            method: 'POST',
            data: {
                workflow_id: workflowId,
                trigger_data: {
                    type: 'manual_test',
                    user_id: wa_admin.current_user_id
                }
            }
        }).done(function(response) {
            $button.text('Test Started');
            
            // Redirect to execution
            if (response.id) {
                window.location.href = wa_admin.admin_url + 'admin.php?page=workflow-automation-executions&execution=' + response.id;
            }
        }).fail(function(response) {
            alert('Test failed: ' + response.responseJSON.message);
            $button.prop('disabled', false).text('Test Run');
        });
    });

    // Auto-refresh executions page
    if ($('.executions-list').length) {
        var refreshInterval = setInterval(function() {
            // Only refresh if there are running executions
            if ($('.execution-status.running').length) {
                location.reload();
            }
        }, 5000); // Refresh every 5 seconds
    }

    // Settings form
    $('#workflow-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submit = $form.find('input[type="submit"]');
        
        $submit.prop('disabled', true).val('Saving...');
        
        // Gather form data
        var data = {};
        $form.serializeArray().forEach(function(item) {
            data[item.name] = item.value;
        });
        
        // Save via AJAX
        $.post(wa_admin.ajax_url, {
            action: 'wa_save_settings',
            nonce: wa_admin.nonce,
            settings: data
        }).done(function(response) {
            if (response.success) {
                $submit.val('Saved!');
                setTimeout(function() {
                    $submit.prop('disabled', false).val('Save Settings');
                }, 2000);
            } else {
                alert('Failed to save settings');
                $submit.prop('disabled', false).val('Save Settings');
            }
        }).fail(function() {
            alert('Failed to save settings');
            $submit.prop('disabled', false).val('Save Settings');
        });
    });

})(jQuery);