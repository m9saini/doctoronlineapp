<div class="box">
    <div class="box-header">
      <h3 class="box-title"><?php echo $title; ?></h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
      <table id="example1" class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
          <th><?php $select_all = array('name' => "select_all_check",'id' => "select_all_check");
            echo form_checkbox($select_all) ?></th>
          <th>Patient Name</th>
          <th>Mobile </th>
          <th>Appointment Date</th>
          <th>Appointment Status</th>
          <th>Apppointment Type</th>
          <th></th>
        </tr>
        </thead>
      <tbody class="tbody_tr_count">
      <?php  if(isset($DataList) && count($DataList)>0 ) {
      foreach($DataList as $key=>$value): ?>
          <tr id="row_<?php echo $value['_id']->{'$id'}; ?>">
         <td><?php $data = array('name' => "check[]",'id' => "check_$key" ,"value"=>$value['_id']->{'$id'});
         echo form_checkbox($data) ?>
         </td>
        <td> <?php echo $value['firstname'].' '.$value['lastname']; ?> </td> 
        <td><?php echo isset($value['mobile'])?$value['mobile']:''; ?></td>
        <td><?php date_default_timezone_set($timezone);
        echo date('Y-m-d',$value['appointment_date']); ?></td>
        <td>
          <?php 
            if($DataListType !='deleted'){
            if($value[$CollectionField]==1){ ?> 
            
              <a class="btn btn-success btn-xs" href="javascript:void(0);" title="Confirmed">
               <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"> Confirmed </span> </a>
              <?php }else if($value[$CollectionField]==2){ ?>
              <a id="obj_id<?php echo $value['_id']->{'$id'} ;?>" class="appointment_status_update btn btn-danger btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id="<?php echo $value['_id']->{'$id'} ;?>" data-status-type="<?php echo $CollectionField; ?>" data-val="1" href="javascript:void(0);" title="Deactived" > 
              <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"> Cancelled </span> </a> 
          <?php } else if($value[$CollectionField]==3) { ?> 

              <a id="obj_id<?php echo $value['_id']->{'$id'} ;?>" class="appointment_status_update btn btn-primary btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id="<?php echo $value['_id']->{'$id'} ;?>" data-status-type="<?php echo $CollectionField; ?>" data-val="1" href="javascript:void(0);" title="Deactived" > 
              <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"> Booking </span> </a> 

          <?php } else{ ?> 

          <a id="obj_id<?php echo $value['_id']->{'$id'} ;?>" class="appointment_status_update btn btn-warning btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id="<?php echo $value['_id']->{'$id'} ;?>" data-status-type="<?php echo $CollectionField; ?>" data-val="1" href="javascript:void(0);" title="Deactived" > 
              <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"> Request </span> </a> 

          <?php } } else { ?>

             <a class="restore_document btn btn-primary btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id='<?php echo $value['_id']->{'$id'} ;?>' >
                <i class="fa fa-refresh"></i> Restore </a>

          <?php } ?>

        </td>
        <td><?php echo $value['appointment_type'][0] ?></td>
       <td> 
            
          <a class="btn btn-primary btn-xs set_value" title="View" href="<?php echo base_url('admin/patients/appointment-view/').$value['_id']->{'$id'}.'/'.$value['patient_id']->{'$id'}.'/'.$value['appointment_date'];?>" >
              <i class="fa fa-eye"></i></a>
        <?php /*  <a class="confirm_btn_show btn btn-danger btn-xs"  title="Delete"  data-del-type="<?php echo ($DataListType =='deleted')?'del':'' ?>" data-type="<?php echo $CollectionKey; ?>" data-id="<?php echo $value['_id']->{'$id'} ;?>"  href="javascript:void(0);">
             <i class="fa fa-trash-o"></i></a> */ ?>
        </td>
  
        </tr>
        <?php endforeach; } ?>
         </tbody>
        <tfoot>
       </tfoot>
      </table>
    </div>
            <!-- /.box-body -->
 </div>

 
<script type="text/javascript">
  $(document).ready(function() {

     var table = $('#example1').DataTable({
   'aoColumnDefs': [{
        'bSortable': false,
        'aTargets': ['nosort']
    }]
});



// Check All or Not 
  $('#select_all_check').click(function(){
    var table = $('#example1').DataTable();
    var info  = table.page.info();

    if($('#select_all_check').is(':checked')==true){
      for (var i = info['start']; i < info['end']; i++) {
            $('#check_'+i).removeAttr('checked');
            $('#check_'+i).attr('checked','checked');
            $('#check_'+i).prop('checked', true);
        }
      } else{
        for (var i = info['start']; i < info['end']; i++) {
              $('#check_'+i).prop('checked', false);
              $('#check_'+i).removeAttr('checked');
        }
      }

    });


  })
 
</script>

