 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       User <?php echo $title; ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('users-list'); ?>"><i class="fa fa-dashboard"></i> List </a></li>
        <li class="active">Profile Update</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
 
  <div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">
    <!-- form start -->
    <?php       $attribute = array('role' => 'form','id'=>'user_view');
              echo form_open_multipart(base_url('profile/').base64_encode($User_Detail['id']),$attribute);
    ?>
              <div class="box-body">
              <div class="form-group col-md-6 col-xs-12 col-sm-6">
                <label for="exampleInputEmail1">Name</label><span style="color:red;">*</span></label>
                <?php
                  $data = array(
                        'name'      => 'name',
                        'id'      => 'name',
                        'value'     =>$User_Detail['name'],
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
                <label for="exampleInputEmail1">Mobile</label><span style="color:red;">*</span></label>
                <?php
                  $data = array(
                        'name'      => 'mobile',
                        'id'      => 'mobile',
                        'value'     =>$User_Detail['mobile'],
                        'required'    =>"required",
                        'autocomplete'  =>'off',
                        'maxlength'     => 10,
                        'minlength'     => 10,
                        'type'        =>'number',
                        'placeholder' => '10 Digit Mobile No',
                        'class'     => "form-control",
                        );
                  echo form_input($data);
                ?>
              </div>
           
              
              <div class="form-group col-md-6 col-xs-12 col-sm-6">
                <label for="exampleInputEmail1">City</label>
                <?php
                  $data = array(
                        'name'      => 'city',
                        'id'      => 'city',
                        'autocomplete'  => 'off',
                        'value'     =>$User_Detail['city'],
                        'maxlength'     => 100,
                        'placeholder' => 'Address',
                        'class'     => "form-control",
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
                <label for="exampleInputEmail1">Profile Image</label>
                                
                <?php
                  $data = array(
                        'name'      => 'profile_image',
                        'id'      => 'profile_image',
                        'accept'    => '*/image',
                        'title'     => 'Browse',
                        );
                  echo form_upload($data);
                ?>
                <input type="hidden" name="previous_image" value="<?php echo $User_Detail['profile_image']; ?>" />
              </div>
             
          

              <div class="clearfix"></div>
              <div class="box-footer client-spacing">
              <?php 
                $js = 'class="btn btn-success"' ;
                echo form_submit('Save','Submit',$js);
              ?>
              <a href="<?php echo base_url('users-list')?>" class = "btn btn-primary">Cancel</a>
              </div>
            <?php echo form_close(); ?>
    </div><!-- /.box -->

  </div><!-- /.col -->

<script>

$(document).ready(function() {
    $
});

</script> 
    

 


    </section>

