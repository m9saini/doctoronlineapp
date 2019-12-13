  <div class="row">
     <div class="col-md-12">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Actions  </h3>
              <?php /*  <button type="button" class="btn btn-success select_all_row">Select All</button>
                <button type="button" class="btn btn-success unselect_all_row">Unselect All</button> */ ?>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputaction" class="col-sm-2 control-label">Select </label>
                  <div class="col-sm-10">
                  <?php 
                    $arr = array(
                        "" => "Action for selected...",
                        '1' => "Activate",
                        '2' => "Deactivate",
                        '3' => "Delete",
                    );
                    echo form_dropdown("action", $arr, '', "class='small form-control' id='table_action_type'");
                    ?>
                    <p id="select_one" class="select_one"></p>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <button type="button" id="table_list_action" class="btn btn-info pull-right">Submit</button>
              </div>
              <!-- /.box-footer -->
            </form>
          </div>
       <!-- /.box -->
  </div>
<script >
$(document).ready(function() {


$('#table_action_type').click(function(){
  var listtype= $("#table_action_type").val(); 
    if(listtype !='' ){
      $("#select_one").html('');

    }
  });

$('.select_all_row').click(function(){
  var table = $('#example1').DataTable();
  var info  = table.page.info();
  for (var i = info['start']; i < info['end']; i++) {
        $('#check_'+i).removeAttr('checked');
        $('#check_'+i).attr('checked','checked');
        $('#check_'+i).prop('checked', true);
        $('#select_all_check').removeAttr('checked');
        $('#select_all_check').attr('checked','checked'); 
        $('#select_all_check').prop('checked', true);
          
    }
  });


$('.unselect_all_row').click(function(){
  var table = $('#example1').DataTable();
  var info  = table.page.info();
  for (var i = info['start']; i < info['end']; i++) {
          $('#check_'+i).prop('checked', false);
          $('#check_'+i).removeAttr('checked');
          $('#select_all_check').prop('checked', false);
        $('#select_all_check').removeAttr('checked');   
    }
  });

// Selected list updations
$('#table_list_action').click(function(){  

    var listtype= $("#table_action_type").val(); 
    if(listtype !='' ){
      $("#select_one").html('');
        var arr=[];
        var status=0;
        var updatetype = ''; 
        var url = '';
        var del_type=''; var fieldtype='';
        var object_id = "test53543543";
        var table = $('#example1').DataTable();
        var info = table.page.info(); 
        for (var i = info['start']; i < info['end']; i++) { 
          if($('#check_'+i).is(':checked')==true)
            { 
                var obj_id=$('#check_'+i).val(); 
                if(obj_id !== void 0){
                        arr.push(obj_id);
                    if(listtype==1){ //active
                    var updatehtml='<i class="fa fa-check"></i>';
                        $("#obj_id"+obj_id).removeClass('btn-danger');
                        $("#obj_id"+obj_id).addClass('btn-success');
                        $("#obj_id"+obj_id).attr('data-val',"0");
                        $("#set_update_value_"+obj_id).html('');
                        $("#set_update_value_"+obj_id).html(updatehtml);
                        url = "<?php echo base_url('admin/update-status');?>";
                        $("#obj_id"+obj_id).attr("title",'Actived');
                        updatetype = $("#obj_id"+obj_id).attr("data-type");
                        fieldtype  = $("#obj_id"+obj_id).attr("data-status-type");
                        status=1;

                    }else if(listtype==2){ //deactive
                         var updatehtml='<i class="fa fa-close"></i>';
                        $("#obj_id"+obj_id).removeClass('btn-success');
                        $("#obj_id"+obj_id).addClass('btn-danger');
                        $("#obj_id"+obj_id).attr('data-val',"0");
                        $("#set_update_value_"+obj_id).html('');
                        $("#set_update_value_"+obj_id).html(updatehtml);
                        url = "<?php echo base_url('admin/update-status');?>";
                        $("#obj_id"+obj_id).attr("title",'Deactive');
                        updatetype=$("#obj_id"+obj_id).attr("data-type");
                        fieldtype  = $("#obj_id"+obj_id).attr("data-status-type");
                        

                    }else if(listtype==3){ //delete

                        updatetype = $("#del_id"+obj_id).attr("data-type");
                        $("#row_"+obj_id).remove(); 
                        url = "<?php echo base_url('admin/deleted-by-admin');?>";
                    }
                }
                $('#check_'+i).prop('checked', false);
                $('#check_'+i).removeAttr('checked');
            }
        }

        $('#select_all_check').prop('checked', false);
        $('#select_all_check').removeAttr('checked');    
         if((arr.length)>0){ 

            $.ajax({
            type: "POST",
            url: url,
            data:{"status":status,"data_type":"list","data_list":arr,"fieldtype":fieldtype,"del_type":del_type,"object_id":object_id,"type":updatetype},
            success: function(data){
              if(data==1){

                    
                    alertify.success('Successfully Documents Updated.');
             
              }
              else{
                     alertify.error("Sorry, You are not authorized to update status!!"); 
        
              }
             }
            });
         } else{
          $("#select_one").html('Please select atleast one record');
         }
        
    }else{
        $("#select_one").html('Please select atleast one record or action');

    }
    });


});
</script>