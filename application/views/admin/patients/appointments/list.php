 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       <?php echo ucwords($title); ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/patients'); ?>"><i class="fa fa-user"></i> Patients</a></li>
        <li class="active"> Appointments List</li>
      </ol>
    </section>

<section class="content">
 <?php $this->load->view('admin/patients/appointments/search');?>
 <?php $this->load->view('admin/patients/appointments/table_list');?>
 <?php //$this->load->view('admin/patients/appointments/table_list_action');?>
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
 
