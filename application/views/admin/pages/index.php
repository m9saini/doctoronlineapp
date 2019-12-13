<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?php echo $title;?></h1>
</section>


<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header"> </div>
        <!-- /.box-header -->
        <div class="box-body">
          <div role="grid" class="dataTables_wrapper form-inline" id="example2_wrapper">
            <div class="row">
              <div class="col-xs-6">
                
              </div>
              <div class="col-xs-6"> </div>
              <div class="col-xs-12">
                    <a href="<?php echo base_url('admin/pages/create');?>"><button  class="btn btn-block-new btn-primary" style="margin-bottom:20px;">Add New Page</button></a>
               </div>
            </div>

            <table id="example1" class="table table-bordered table-striped table-hover tbody-count" cellspacing="0" width="100%">
            <thead>
                <tr>
    				<th>S.NO.</th>
    				<th>Title</th>
            <th>Heading</th>
    			<?php /*	<th>Show-On</th>
    				<th>Created Date</th>
    				<th>Modify Date</th> */ ?>
    				<th>Status</th>
    				<th>Action</th>
                </tr>
            </thead>
            <tbody>
		        <?php $i=1; if(count($page_list)>0){ foreach($page_list as $key=>$value){ $id = (string)$value['_id']; ?>
            <tr id="row_<?php echo $value['_id']->{'$id'}; ?>">
               <td><?php $data = array('name' => "check[]",'id' => "check_$key","value" =>$value['_id']->{'$id'});
                    echo form_checkbox($data) ?> <span><?php echo $key+1; ?></span>
               </td>
               <td><?php echo $value['title'];?></td>
               <td> <?php echo $value['heading'];	?> </td>
            	 <td>
        						<?php if($value['status']==1) {  ?> 
                          <a class="btn btn-success btn-xs" id="uobj_id<?php echo $value['_id']->{'$id'} ;?>" 
                  data-type="SPS" data-id="<?php echo $value['_id']->{'$id'} ;?>"  href="javascript:void(0);" 
                  data-status-type="status" data-val="0" title="Actived">
                 <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>">
                 <i class="fa fa-check"></i> </span> 
                 </a>                   
                    <?php }else{ ?>
                            <a id="obj_id<?php echo $value['_id']->{'$id'} ;?>" class="actived_or_deactived btn btn-danger btn-xs" 
                  data-type="SPS" data-id="<?php echo $value['_id']->{'$id'} ;?>" data-status-type="status" data-val="1" 
                  href="javascript:void(0);" title="Deactive" > 
                   <span id="set_update_value_<?php echo $value['_id']->{'$id'} ;?>"> 
                   <i class="fa fa-close"></i> </span> 
                   </a>           
                    <?php } ?>
                </td>
                 <td>
                  <a id="page_edit" class="btn btn-success btn-xs" href="<?php echo base_url('admin/pages/edit/').$value['slug'] ?>" title="Edit" > 
                   <i class="fa fa-edit"></i> </a>           
				  <?php  if($value['status']!=1){?>
                  <a class="confirm_btn_show btn btn-danger btn-xs"  title="Delete" href="javascript:void(0);" data-del-type="del"       data-type="SPS" id="del_id<?php echo $id  ;?>" data-id="<?php echo $id  ;?>" >
                    <i class="fa fa-trash-o"></i>
                </a>
				    <?php } ?>
                 </td>
            </tr>
		  <?php $i++;} } ?>
        </tbody>
    </table>
           </div>
        </div>
        <!-- /.box-body --> 
      </div>
      <!-- /.box --> 
    </div>
    <!-- /.col --> 
  </div>
  <!-- /.row --> 
  
</section>
 <?php $this->load->view('admin/element/table_list_action');?>