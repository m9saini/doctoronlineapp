<div class="box">
    <div class="box-header">
      <h3 class="box-title"><?php echo $title; ?></h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
    <button type="button" id="services_upsert" class="btn btn-info pull-right"> Add </button>
      <table id="example1" class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
         <?php  if(isset($ShowAction) && in_array($ShowAction,[1,2,3])) { ?>
           <th><?php $select_all = array('name' => "select_all_check",'id' => "select_all_check");
         echo form_checkbox($select_all) ?></th>
         <?php } else { ?>
         <th>S.No.</th>
         <?php } ?>
           <?php if(isset($DataHeading) && count($DataHeading>0 )) { 
            foreach($DataHeading as $key=>$heading): ?>
                  <th><?php echo ucwords($heading); ?></th>
          <?php endforeach; } ?>
          <?php  if(isset($ShowAction) && in_array($ShowAction,[1,2,3])) { ?>
          <th></th>
          <?php } ?>
        </tr>
        </thead>
      <tbody class="tbody_tr_count">
      <?php  if(isset($DataList) && count($DataList)>0 && count($DataHeading)>0 ) {
      foreach($DataList as $key=>$value): ?>
          <tr id="row_<?php echo $value['_id']->{'$id'}; ?>">
          <?php  if(isset($ShowAction) && in_array($ShowAction,[1,2,3])) { ?>
         <td><?php $data = array('name' => "check[]",'id' => "check_$key" ,"value"=>$value['_id']->{'$id'});
         echo form_checkbox($data) ?>
         </td>
         <?php } else { ?>
         <td> <?php echo $key+1; ?></td>
         <?php } ?>
      <?php for($i=0;$i< count($DataHeading);$i++) { ?>
       
        <td><span id="<?php echo $DataHeading[$i].'_'.$value['_id']->{'$id'} ?>" >  <?php if($DataHeading[$i]=='name') { 
                    echo (isset($value['name']))?$value['name'].' ':'';
                    } else { 
                      echo _customDate($value[$DataHeading[$i]],$timezone,'Y-m-d');
                    } ?>
        </span></td> <?php } ?>
       <?php  if(isset($ShowAction) && in_array($ShowAction,[1,2,3])) { ?>
       <td> 
        <?php  
        if($ShowAction==3)
          { 
            if($DataListType !='deleted'){
            if($value[$CollectionField]==1){ ?> 
            
              <a id="obj_id<?php echo $value['_id']->{'$id'} ;?>"  class="actived_or_deactived btn btn-success btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id="<?php echo $value['_id']->{'$id'} ;?>"  href="javascript:void(0);" data-status-type="<?php echo $CollectionField; ?>" data-val="0" title="Actived">
               <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"><i class="fa fa-check"></i> </span> </a>
              <?php }else{ ?>
              <a id="obj_id<?php echo $value['_id']->{'$id'} ;?>" class="actived_or_deactived btn btn-danger btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id="<?php echo $value['_id']->{'$id'} ;?>" data-status-type="<?php echo $CollectionField; ?>" data-val="1" href="javascript:void(0);" title="Deactived" > 
              <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"> <i class="fa fa-close"></i>  </span> </a> 
          <?php } } else { ?>

             <a class="restore_document btn btn-primary btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id='<?php echo $value['_id']->{'$id'} ;?>' >
                <i class="fa fa-refresh"></i> Restore</a>

          <?php } ?>
          <?php if(isset($ActionEdit) && !empty($ActionEdit)) {?>
          <a class="btn btn-primary btn-xs set_value" title="View Profile" data-id="<?php echo $value['_id']->{'$id'};?>" data-name="<?php  echo $value['name'];?>" >
              <i class="fa fa-edit"></i></a>
          <?php } ?>
          <a class="confirm_btn_show btn btn-danger btn-xs"  title="Delete"  data-del-type="<?php echo ($DataListType =='deleted')?'del':'' ?>" data-type="<?php echo $CollectionKey; ?>" data-id="<?php echo $value['_id']->{'$id'} ;?>"  href="javascript:void(0);">
             <i class="fa fa-trash-o"></i></a>

            
        <?php   } else if($ShowAction==2) { 
          if($DataListType !='deleted'){
            if($value[$CollectionField]==1){ ?> 
            
              <a id="obj_id<?php echo $value['_id']->{'$id'} ;?>"  class="actived_or_deactived btn btn-success btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id="<?php echo $value['_id']->{'$id'} ;?>"  href="javascript:void(0);" data-status-type="<?php echo $CollectionField; ?>" data-val="0" title="Actived">
               <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"><i class="fa fa-check"></i> </span> </a>
              <?php }else{ ?>
              <a id="obj_id<?php echo $value['_id']->{'$id'} ;?>" class="actived_or_deactived btn btn-danger btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id="<?php echo $value['_id']->{'$id'} ;?>" data-status-type="<?php echo $CollectionField; ?>" data-val="1" href="javascript:void(0);" title="Deactived" > 
              <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"> <i class="fa fa-close"></i>  </span> </a> 
          <?php } } else { ?>

             <a class="restore_document btn btn-primary btn-xs" data-type="<?php echo $CollectionKey; ?>" data-id='<?php echo $value['_id']->{'$id'} ;?>' >
                <i class="fa fa-refresh"></i> Restore</a>
          <?php } ?>
          <?php if(isset($ActionEdit) && !empty($ActionEdit)) { ?>
           <a class="btn btn-primary btn-xs set_value" title="View Profile" data-id="<?php  echo $value['_id']->{'$id'};?>" data-name="<?php  echo $value['name'];?>" >
              <i class="fa fa-edit"></i></a>
          <?php } ?>
              
        <?php  } else if($ShowAction==1) {  ?>
            
           <a class="btn btn-primary btn-xs set_value" title="View Profile" data-id="<?php  echo $value['_id']->{'$id'};?>" data-name="<?php  echo $value['name'];?>" >
              <i class="fa fa-edit"></i></a>
              
        <?php  } ?>

        </td>
        <?php  } ?>
        
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

