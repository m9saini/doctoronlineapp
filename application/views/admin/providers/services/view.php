 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       <?php echo ucwords($title); ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Providers Schedule List</li>
      </ol>
    </section>

<section class="content">
 <?php $this->load->view('admin/providers/services/table_list');?>
 <?php $this->load->view('admin/providers/services/table_list_action');?>
 </section>
 
 <script type="text/javascript">
$(document).ready(function(){

$('#services_upsert')
        .bootstrapValidator({
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
             },
            fields: { 
                     name: {
                    validators: {
                        notEmpty: {
                            message: 'Services Name is required !'
                        },
                       
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

   //edit set id
$('.set_value').click(function(){ 

    var obj_id= $(this).attr('data-id');
    var name=   $(this).attr('data-name');
    $("#edit_id").val(obj_id);
    $("#edit_name").val(name);
    $("#edit_type").val('edit');
    $('#EditServicesModalShow').modal();
   
  });

$('#services_upsert').click(function(){ 

    $("#edit_id").val('');
    $("#edit_name").val('');
    $("#edit_type").val('');
    $('#EditServicesModalShow').modal();
   
  });

$("#update_name").click(function(){ 

  obj_id  = $("#edit_id").val();
  name   = $("#edit_name").val(); 
  type    = $("#edit_type").val();
    if(name=='')
    { 
      alertify.error('Services Name is required'); 
      return false;
    } else {
          var data={name:name};
          if(type == "edit" ){ 
            if(obj_id==''){ 
              alertify.error('Object id Not found'); 
              return false;
            } else{
                data={name:name,id:obj_id};
            }
          }
          var url = '<?php echo base_url('admin/providers/services-edit');?>';
          $.ajax({
          type: "POST",
          url: url,
          data:data,
          success: function(output){
            var obj = jQuery.parseJSON(output);
            if(obj['id']){
                var set_date= new Date();
                   if(type!="edit"){

                      set_date= new Date();
                      var rowCount = $('#example1 tr').length;
                      rowCount= rowCount-1;
                      var table = $('#example1').DataTable();
                      var checkbox='<input type="checkbox" name="check[]" value="'+obj['id']+'" id="check_'+rowCount+'">';

                      var ac_btn='<a id="obj_id'+obj['id']+'" class="actived_or_deactived btn btn-danger btn-xs" data-type="<?php echo $CollectionKey ?>" data-id="'+obj['id']+'" data-status-type="<?php echo $CollectionField ?>" data-val="1" href="javascript:void(0);" title="Deactived"> <span id="set_update_value_'+obj['id']+'"> <i class="fa fa-close"></i>  </span> </a>' 
                      var e_btn ='<a class="btn btn-primary btn-xs set_value" title="View Profile" data-id="'+obj['id']+'" data-name="'+name+'"><i class="fa fa-edit"></i></a>';
                      var sname="<span name_"+obj['id']+">"+name;
                      var action_btn= ac_btn+' '+e_btn;
                      $('#example1').dataTable().fnAddData([checkbox,sname,set_date,'',action_btn]);
                      table.order([1, 'asc']).draw();
                      table.page('last').draw(false);
                      alertify.success('Successfully Added');
                   } else{

                     alertify.success('Successfully Updated');
                   }
                   $("#name_"+obj_id).html('');
                   $("#name_"+obj_id).html(name);
                   $(".set_value").attr('data-name',name);
                 
                  $('#EditServicesModalShow').modal('hide');
                  return true;  
            }
            else{
                  alertify.error(obj[0]); 
                return false;                             
            }
           }
          });
      }

});

})
 </script>
 <!-- Model confirm message Sow -->
<div id="EditServicesModalShow" class="modal fade " role="dialog">
  <div class="modal-dialog">
   <div class="modal-content">

  <div class="modal-body" id="EditServicesModalContent">
  <h4>Provider Services</h4>
     <form class="form-horizontal" id="edit_services" name="add_services" action="" method="POST" >
        <div class="box-body">
          <div class="form-group">
            <label for="inputaction" class="col-sm-2 control-label"> Name </label>
            <div class="col-sm-10">
            <input type="text" name="name" class="form-control pull-right" id="edit_name" value="" >
            <input type="hidden" name="id" class="" id="edit_id" value=""  >
            <input type="hidden" name="edit_type" class="" id="edit_type" value=""  >
            </div>
          </div>
        <!-- /.box-footer -->
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" id="update_name">Ok</button>
    <button type="button" data-dismiss="modal" class="btn btn-primary">Cancel</button>
  </div>
  </form>
  </div>
  </div>
  
</div>
<!-- confirm Model close -->
 
