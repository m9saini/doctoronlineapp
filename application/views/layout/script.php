
<script>
$(document).ready(function() {

    $('#change_password')
        .bootstrapValidator({
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
             },
            fields: {
				
            	 old_password: {
                    validators: {
                        notEmpty: {
                            message: 'Valid Old Password required'
                        },
                    }
                },
                new_password: {
                    validators: {
                        notEmpty: {
                            message: 'Password required'
                        },
                    }
                },
                confirm_password: {
                    validators: {
                        notEmpty: {
                            message: 'The confirm password is required and cannot be empty'
                        },
                        identical: {
                            field: 'new_password',
                            message: 'New password and confirm password does not match'
                        }
                    }
                },
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



        $('#profile')
        .bootstrapValidator({
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
             },
            fields: { 
                     firstname: {
                    validators: {
                        notEmpty: {
                            message: 'First Name is required !'
                        },
                       
                    }
                    },
                    lastname: {
                    validators: {
                        notEmpty: {
                            message: 'First Name is required !'
                        },
                       
                    }
                    },
                    email: {
                    validators: {
                    notEmpty: {
                            message: 'Unique Email/Username is required !'
                        },
                      }
                    },

                    phone: {
                    validators: {
                        notEmpty: {
                            message: 'Mobile Number is required !'
                        },
                    digits: {
                            message: 'invalid mobile number entered'
                    },
                    stringLength: {
                            min: 10,
                            message: 'The invalid mobile number entered'
            
                    }
                            }
                    },
                    address: {
                    validators: {
                        notEmpty: {
                            message: 'Address is required !'
                        },
                        }
                    },
                    image:
                            {
                            validators:
                            {
                            /*  notEmpty:
                                {
                                    message: 'Profile image is required'
                                },
                                */
                                file:
                                {
                                    extension: 'jpeg,png,jpg',
                                    type: 'image/jpeg,image/png',
                                    maxSize: 2097152,   // 2048 * 1024
                                    message: 'The selected file is not valid'
                                }
                            }
                        },
      
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

        //Close Admin Profile

          $('#user_view')
        .bootstrapValidator({
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
             },
            fields: { 
                     name: {
                    validators: {
                        notEmpty: {
                            message: 'First Name is required !'
                        },
                       
                    }
                    },
                    email: {
                    validators: {
                    notEmpty: {
                            message: 'Unique Email/Username is required !'
                        },
                      }
                    },

                    mobile: {
                    validators: {
                        notEmpty: {
                            message: 'Mobile Number is required !'
                        },
                    digits: {
                            message: 'invalid mobile number entered'
                    },
                    stringLength: {
                            min: 10,
                            message: 'The invalid mobile number entered'
            
                    }
                            }
                    },
                    address: {
                    validators: {
                        notEmpty: {
                            message: 'Address is required !'
                        },
                        }
                    },
                    profile_image:
                            {
                            validators:
                            {
                            /*  notEmpty:
                                {
                                    message: 'Profile image is required'
                                },
                                */
                                file:
                                {
                                    extension: 'jpeg,png,jpg',
                                    type: 'image/jpeg,image/png',
                                    maxSize: 2097152,   // 2048 * 1024
                                    message: 'The selected file is not valid'
                                }
                            }
                        },
      
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

        
 //bollean vlaue updated by admin 

   $('.confirm_btn_show').click(function(){
     var object_id= $(this).attr("data-id");
    var updatetype = $(this).attr("data-type");
    var del_type = $(this).attr("data-del-type"); 
  $('#ConfirmModalContent').html('');
  if(updatetype=='PTS')
    del_name='Patient';
  else if(updatetype=='PDS')
    del_name='Provider';
    else if(updatetype=='SPS')
        del_name='Page';
    else
        del_name=''; 
  var text_msg='<p>Are you sure delete this '+del_name+' ?</p></br>';
    $('#ConfirmModalContent').html(text_msg);
    $('#ConfirmModalShow').modal({backdrop: 'static',keyboard: false})
   .one('click', '#confirm_ok', function(e) {
    var url = '<?php echo base_url('admin/deleted-by-admin');?>';
            $.ajax({
            type: "POST",
            url: url,
            data:{"type":updatetype,"object_id":object_id,"del_type":del_type},
            success: function(data){
              if(data==1){
                $("#row_"+object_id).remove();
                    alertify.success('Successfully Document Deleted');
                    return true;  
              }
              else{
                    alertify.error("Sorry, You are not authorized to update status!!"); 
                  return true;                             
              }
             }
            });
      });
  });


// Restore Document by admin user


    $('.restore_document').click(function(){
    var object_id= $(this).attr("data-id");
    var updatetype = $(this).attr("data-type");
    var url = '<?php echo base_url('admin/restore-by-admin');?>';
            $.ajax({
            type: "POST",
            url: url,
             data:{"type":updatetype,"object_id":object_id},
            success: function(data){
              if(data==1){
                $("#row_"+object_id).remove();
                    alertify.success('Successfully Document Restored.');
                    return true; 
              }
              else{
                     alertify.error("Sorry, You are not authorized to update status!!"); 
                  return true;         
              }
             }
            });
     
  });

  //actived and  deactived by admin 

  $('.actived_or_deactived').click(function(){
     var object_id= $(this).attr("data-id");
    var updatetype = $(this).attr("data-type");
    var fieldtype = $(this).attr("data-status-type");
    var status = $(this).attr("data-val");
    var obj_id = $(this).attr("id");
    var url = '<?php echo base_url('admin/update-status');?>';
            $.ajax({
            type: "POST",
            url: url,
            data:{"type":updatetype,"status":status,"object_id":object_id,"fieldtype":fieldtype},
            success: function(data){
              if(data==1){
                    if(status=="1"){
                        var updatehtml='<i class="fa fa-check"></i>';
                       $("#"+obj_id).removeClass('btn-danger');
                       $("#"+obj_id).addClass('btn-success');
                       if(updatetype=='SPS'){
                        $("#del_id"+object_id).remove();
                       }
                    }else {
                        var updatehtml='<i class="fa fa-close"></i>';
                        $("#"+obj_id).removeClass('btn-success');
                        $("#"+obj_id).addClass('btn-danger');
                        
                    };
                    var msg=(status=="1")?"Activated":"Deactivated";
                    var change_vla=(status=="1")?"0":"1";
                    $("#"+obj_id).attr('data-val',change_vla);
                    $("#"+obj_id).attr('title',msg);
                    $("#set_update_value_"+object_id).html('');
                    $("#set_update_value_"+object_id).html(updatehtml);
                    
                    
                    //$("#row_"+object_id).remove();
                    alertify.success("Successfully "+msg); 
                    return true; 
              }
              else{
                    alertify.error("Sorry, You are not authorized to update status!!"); 
                  return true; 
              }
             }
            });
    
  });


});

</script>