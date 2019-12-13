 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       <?php echo ucwords($title); ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Providers List</li>
      </ol>
    </section>

<section class="content">
<div class="box">
            <div class="box-header">
              <h3 class="box-title">Users List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="example1" class="table table-bordered table-striped table-hover tbody-count">
                <thead>
                <tr>
				  <th> S.No</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Gender</th>
                <!--  <th>Created</th> -->
                  <th></th>
                </tr>
                </thead>
                <tbody class="tbody_tr_count">
				<?php  if($DataList) { $session_admin= $this->session->userdata('admin'); $timezone=$session_admin['timezone'];
				foreach($DataList as $key=>$value): ?>
              <tr id="row_<?php echo $value['_id']->{'$id'}; ?>">
					     <td><?php $data = array('name' => "check[]",'id' => "check_$key","value" =>$value['_id']->{'$id'});
                    echo form_checkbox($data) ?> <span><?php echo $key+1; ?></span>
                  </td>
                  <td><?php echo $value['firstname'].' '.$value['lastname']; ?></td>
                  <td><?php echo $value['email']; ?></td>
                  <td><?php echo $value['mobile']; ?></td>
                  <td><?php echo $value['gender']; ?></td>
                <!--  <td><?php echo _customDate($value['created'],$timezone,'Y-m-d'); ?></td> -->
                  <td>
                 <?php if($DataListType !='deleted'){ 
				    if($value['status']==1){ ?> 
				        <a class="actived_or_deactived btn btn-success btn-xs" id="obj_id<?php echo $value['_id']->{'$id'} ;?>" 
				          data-type="PDS" data-id="<?php echo $value['_id']->{'$id'} ;?>"  href="javascript:void(0);" 
				          data-status-type="status" data-val="0" title="Actived">
				         <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>">
				         <i class="fa fa-check"></i> </span> 
				         </a>
				        <?php }else { ?>
				          <a id="obj_id<?php echo $value['_id']->{'$id'} ;?>" class="actived_or_deactived btn btn-danger btn-xs" 
				          data-type="PDS" data-id="<?php echo $value['_id']->{'$id'} ;?>" data-status-type="status" data-val="1" 
				          href="javascript:void(0);" title="Deactive" > 
				           <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"> 
				           <i class="fa fa-close"></i> </span> 
				           </a> 
				        <?php } } else { ?>
				        <?php /*                 ***** Restore  Document *******                  */ ?>
				           <a class="restore_document btn btn-primary btn-xs" data-type="PDS" data-id='<?php echo $value['_id']->{'$id'} ;?>' >
				              <i class="fa fa-refresh"></i> Restore</a>
				        <?php } ?>
				        <?php /*                 ***** View  Document *******                  */ ?>
				        <a class="btn btn-info btn-xs" title="View Profile" 
				            href='<?php  echo base_url("admin/providers-view/").$value['_id']->{'$id'} ;?>' >
				            <i class="fa fa-eye"></i> 
				        </a>
                <?php     /************             Services List   ************/?>
                 <a class="btn btn-primary btn-xs"  title="Schedule List" href="<?php echo base_url('admin/provider/schedule/').$value['_id']->{'$id'} ;?>">
                 <i class="fa fa-user-md"></i></a>

				        <?php /*                 ***** Dellete Document *******                  */ ?>
				        <a class="confirm_btn_show btn btn-danger btn-xs"  title="Delete" href="javascript:void(0);"
				            data-del-type="<?php echo ($DataListType =='deleted')?'del':'' ?>" 
				            data-type="PDS" id="del_id<?php echo $value['_id']->{'$id'} ;?>" data-id="<?php echo $value['_id']->{'$id'} ;?>" >
				            <i class="fa fa-trash-o"></i>
				        </a>
                  </td>
                </tr>
                <?php endforeach; } ?>
                 </tbody>
                <tfoot>
                <tr>
                  <th>S.No</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Gender</th>
                <!--  <th>Created</th> -->
                  <th></th>
                </tr>
                </tfoot>
                </table>
              </div>
            <!-- /.box-body -->
 </div>
 </section>
 <?php $this->load->view('admin/element/table_list_action');?>
 <script>

 $(document).ready(function() {

});

</script>
