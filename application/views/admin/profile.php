 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       Profile
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Profile Update</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
 
  <div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">
    <!-- form start -->
    <?php       $attribute = array('role' => 'form','id'=>'profile');
              echo form_open_multipart(base_url('admin/profile'),$attribute);
    ?>
              <div class="box-body">
              <div class="form-group col-md-6 col-xs-12 col-sm-6">
                <label for="exampleInputEmail1">First Name</label><span style="color:red;">*</span></label>
                <?php
                  $data = array(
                        'name'      => 'firstname',
                        'id'      => 'firstname',
                        'value'     =>$User_Detail['firstname'],
                        'required'    =>"required",
                        'autocomplete'  =>'off',
                        'maxlength'     => 20,
                        'placeholder' => 'Name',
                        'title'     => 'Name',
                        'class'     => "form-control",
                         'autofocus'

                        );
                  echo form_input($data);
                ?>
              </div>
              <div class="form-group col-md-6 col-xs-12 col-sm-6">
                <label for="exampleInputEmail1">Last Name</label><span style="color:red;">*</span></label>
                <?php
                  $data = array(
                        'name'      => 'lastname',
                        'id'      => 'lastname',
                        'value'     =>$User_Detail['lastname'],
                        'required'    =>"required",
                        'autocomplete'  =>'off',
                        'maxlength'     => 20,
                        'placeholder' => 'Name',
                        'title'     => 'Name',
                        'class'     => "form-control",
                         'autofocus'

                        );
                  echo form_input($data);
                ?>
              </div>              

              
              <div class="form-group col-md-6 col-xs-12 col-sm-6">
                <label for="exampleInputEmail1">Email</label><span style="color:red;">*</span></label>
                <?php
                  $data = array(
                        'name'      => 'email',
                        'id'      => 'email',
                        'required'    =>"required",
                        'autocomplete'  =>'off',
                        'value'     =>$User_Detail['email'],
                        'maxlength'     => 50,
                        'placeholder' => 'Email',
                        'title'     => 'Email',
                        'class'     => "form-control",
                        );
                  echo form_input($data);
                ?>
              </div>
                            <div class="form-group col-md-6 col-xs-12 col-sm-6">
                <label for="exampleInputEmail1">Phone</label><span style="color:red;">*</span></label>
                <?php
                  $data = array(
                        'name'      => 'phone',
                        'id'      => 'phone',
                        'value'     =>$User_Detail['phone'],
                        'required'    =>"required",
                        'autocomplete'  =>'off',
                        'maxlength'     => 10,
                        'minlength'     => 10,
                        'type'        =>'number',
                        'placeholder' => '10 Digit Mobile No',
                        'class'     => "form-control scroll_disable",
                        );
                  echo form_input($data);
                ?>
              </div>
 
             <div class="form-group col-md-6 col-xs-12 col-sm-6">
                <label for="exampleInputEmail1">Address</label>
                <?php
                  $data = array(
                        'name'      => 'address',
                        'id'      => 'address',
                        'rows'          => '2',
                        'value'     =>$User_Detail['address'],
                        'cols'          => '4',
                        'class'     => "form-control",
                        );
                  echo form_textarea($data);
                ?>
              </div>
                <div class="form-group col-md-6 col-xs-12 col-sm-6">
                <label for="exampleInputEmail1">Profile Image ( 2048 * 1024)</label>
                                
                <?php
                  $data = array(
                        'name'      => 'image',
                        'id'      => 'image',
                        'title'     => 'Browse',
                        );
                  echo form_upload($data);
                ?>
              <?php if(isset($User_Detail['photo'][0])){ ?>
               <div>
               <img src="<?php echo base_url('assets/').$User_Detail['photo'][0]?>" width="100" height="100" />
               </div>
               <?php } ?>
              </div>
             
          

              <div class="clearfix"></div>
              <div class="box-footer client-spacing">
                <button type="submit" class="btn btn-success">Update</button>  
              <a href="<?php echo base_url('admin/dashboard')?>" class = "btn btn-primary">Cancel</a>
              </div>
            <?php echo form_close(); ?>
    </div><!-- /.box -->

  </div><!-- /.col -->

<script>

$(document).ready(function() {

});

</script> 
    

 


    </section>

