 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       Change Password
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Change Password</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
        <div class="box-body">
           <div class="col-md-12">
          <!-- Horizontal Form -->
          <div class="box box-info">
             <!-- form start -->
            <form class="form-horizontal" action="" method="POST" name="change_password" id="change_password">
              <div class="box-body">
                <div class="form-group">
                  <label for="old_password" class="col-sm-2 control-label">Old Password</label>

                  <div class="col-sm-10">
                    <input type="password" class="form-control" name="old_password" id="old_password" placeholder="Old Password">
                  </div>
              </div>
                <div class="form-group">
                  <label for="new_password" class="col-sm-2 control-label">New Password</label>

                  <div class="col-sm-10">
                    <input type="password" class="form-control" name="new_password" id="new_password" placeholder="New Password">
                  </div>
              </div>
              <div class="form-group">
                  <label for="confirm_password" class="col-sm-2 control-label">Confirm Password</label>

                  <div class="col-sm-10">
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password">
                  </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a href="<?php echo base_url('admin/dashboard');?>" class="btn btn-default">Cancel</a>
                <button type="submit" class="btn btn-info pull-right">Update</button>
              </div>
              <!-- /.box-footer -->
            </form>
          </div>
          <!-- /.box -->
          </div>

        
      </div>
      <!-- /.box -->

    </section>

