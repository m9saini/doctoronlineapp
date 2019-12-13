 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       Users
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Users List</li>
      </ol>
    </section>

<section class="content">
<div class="box">
            <div class="box-header">
              <h3 class="box-title">Users List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="example1" class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
					<th>S.No</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Gender</th>
                  <th>DOB</th>
                </tr>
                </thead>
                <tbody>
				<?php  if($UserList) {
				foreach($UserList as $key=>$value): ?>
                <tr>
					<td><?php echo $key+1; ?></td>
                  <td><?php echo $value['firstname'].' '.$value['firstname']; ?></td>
                  <td><?php echo $value['email']; ?></td>
                  <td><?php echo $value['mobile']; ?></td>
                  <td><?php echo $value['gender']; ?></td>
                  <td><?php echo $value['dob']; ?></td>
                  <td>
                       <div class="input-group-btn">
           <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">Action
                    <span class="fa fa-caret-down"></span></button>
          <ul class="dropdown-menu">
          <li>
              <div id="change_value<?php $status=1; ?>">
                 <?php
                if($status==1){ ?> 
                            <a class="btn edit"  href="javascript:void(0);" title="True"  onclick="return change_status(<?php echo $id=1;?>,'type','0')"> Active
                              <img src="<?php echo 'verified2.png';?>" width="30px" height="30px;"/>
                            </a>
                            <?php }else{ ?>
                            <a class="btn edit"  href="javascript:void(0);" title="False"  onclick="return change_status(<?php echo $id=1;?>,'type','1')" ?> Deactive
                              <img src="<?php echo 'notverfied.png';?>" width="30px" height="30px;"/>
                            </a>
                    <?php } ?>
              </div> 

          </li>
          <li><a class="btnDelete" href="<?php  echo base_url('users-view/');?>" >
                  <i class="fa fa-edit"></i> Edit</a>
            </li>
          <li><a class="btnDelete"  id="confirm_btn_show" href="javascript:void(0);">
                 <i class="fa fa-trash-o"></i> Delete</a>
            </li>
          </ul>
          </div>
                  </td>
                </tr>
                <?php endforeach; } ?>
                 </tbody>
                <tfoot>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Gender</th>
                  <th>DOB</th>
                </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
 </div>
 </section>

 <script>

 $(document).ready(function() {
   
  $('#confirm_btn_show').click(function(){
  $('#ConfirmModalContent').html('');
  var text_msg='<p>Are you sure confirm delete this user?</p></br>';
    $('#ConfirmModalContent').html(text_msg);
    $('#ConfirmModalShow').modal({backdrop: 'static',keyboard: false})
   .one('click', '#confirm_ok', function(e) {
    var url = '<?php echo base_url('user-delete');?>';
            $.ajax({
            type: "POST",
            url: url,
            data:{token:'',change_value:''},
            success: function(data){
              if(data==909){
                alertify.alert("Sorry, You are not authorized to update status!!");    
              }
              else{
                    alert('deleted');
                              
              }
             }
            });
      });
  });
  //close confirm button 
});

</script>
