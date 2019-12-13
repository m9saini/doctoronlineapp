
<script>

$(document).ready(function() {
    $('#add_page')
        .bootstrapValidator({
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
             },
            fields: { 
                page_title: {
                    validators: {
                        notEmpty: {
                            message: 'Page title is missing !'
                        },
                       
                    }
                },
                menu: {
                    validators: {
                        notEmpty: {
                            message: 'Menu is missing !'
                        },
                       
                    }
                },
                page_heading: {
                    validators: {
                        notEmpty: {
                            message: 'Page heading is missing  !'
                        },
                       
                    }
                },
                content: {
                    validators: {
                        notEmpty: {
                            message: 'Page description is missing  !'
                        },
                       
                    }
                }
            }
        })
        .on('error.validator.bv', function(e, data) {
            data.element
                .data('bv.messages')
                // Hide all the messages
                .find('.help-block[data-bv-for="' + data.field + '"]').hide()
                // Show only message associated with current validator
                .filter('[data-bv-validator="' + data.validator + '"]').show();
        });
});


//================================================================
</script>
<script src="<?php echo base_url();?>assets/bower_components/ckeditor/ckeditor.js"></script>
