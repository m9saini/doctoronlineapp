  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       <?php echo $title; ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/patients'); ?>"><i class="fa fa-dashboard"></i> List </a></li>
        <li class="active">Profile</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="col-md-3">

          <!-- Profile Image -->
          <div class="box box-primary">
            <div class="box-body box-profile">
              <img class="profile-user-img img-responsive img-circle" src="<?php echo ($patientData['image'])?$patientData['image']:base_url('assets/default-50x50.gif') ?>" alt="User profile picture">

              <h3 class="profile-username text-center"><?= $patientData['firstname'].' '.$patientData['lastname'] ?></h3>
              <?php /*
              <ul class="list-group list-group-unbordered">
               <li class="list-group-item">
                  <b>Waiting</b> <a class="pull-right"><?php echo $Waiting ?></a>
                </li>
                <li class="list-group-item">
                  <b>Booked</b> <a class="pull-right"><?php echo $Booked ?></a>
                </li>
                <li class="list-group-item">
                  <b>Canceled</b> <a class="pull-right"><?php echo $Canceled  ?></a>
                </li>
              </ul> */ ?>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->

          <!-- About Me Box -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">About Me</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            <?php if(count($appointment_type)>0){ ?>
              <strong><i class="fa fa-services margin-r-5"></i> Appointment</strong>
              <?php $app_color=['success','info','primary','warning']; ?>
              <p>
              <?php foreach ($appointment_type as $key => $value) { ?>
               <span class="label label-<?php echo $app_color[$key] ?>"><?php echo $value ?></span>
             <?php  } ?>
              </p>
            <?php  } ?>
            <ul class="list-group list-group-unbordered">
            <?php  if($patientData['gender']) {?>
             <li class="list-group-item">
             <strong><i class="" ></i> Gender </strong> <a class="pull-right">
                <?php echo $patientData['gender']; ?>
                </a>
              </li>
              <?php }  ?>
            <?php  if($patientData['dob']) {?>
             <li class="list-group-item">
             <strong><i class="fa fa-birthday-cake" ></i> DOB </strong> <a class="pull-right">
                <?php echo date('d-M-Y',$patientData['dob']); ?>
                </a>
              </li>
              <?php }  ?>
               <?php  if($patientData['mobile']) {?>
             <li class="list-group-item">
             <strong><i class="fa fa-mobile margin-r-5"></i> Mobile </strong> <a class="pull-right">
               <?php echo $patientData['country_code'].$patientData['mobile']; ?>
                </a>
              </li>
              <?php }  ?>
              </ul>
              <strong><i class="fa fa-map-marker margin-r-5"></i> Location</strong>
              <?php if($patientData['street_add']){ ?>
              <p class="text-muted" ><?php echo $patientData['street_add']; ?></p>
              <?php } ?>
              <p class="text-muted"><?= isset($patientData['city'])?$patientData['city'].' , ' . $patientData['state']:'' ?></p>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
        <div class="col-md-9">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#card" data-toggle="tab">Cards</a></li>
             <?php  /* <li><a href="#settings" data-toggle="tab">Settings</a></li> */ ?>
            </ul>
            <div class="tab-content">
              <div class="active tab-pane" id="card">
            
            <div class="box">
            <div class="box-header">
              <h3 class="box-title"></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="example1" class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                   <th>S.No</th>
                  <th>Holder Name</th>
                  <th>Bank</th>
                   <th>Card Type</th>
                  <th>Number</th>
                  <th>Expiry Date</th>
                  <th></th>
                </tr>
                </thead>
                <tbody>
        <?php  if($cardsData) {
        foreach($cardsData as $key=>$value): ?>
                <tr>
          <td><?php echo $key+1; ?></td>
                  <td><?php echo $value['firstname'].' '.$value['lastname']; ?></td>
                  <td><?php echo $value['card_bank']; ?></td>
                  <td><?php echo $value['card_type']; ?></td>
                  <td><?php echo $value['card_number']; ?></td>
                  <td><?php echo $value['card_exp']; ?></td>
                  
                  <td>
                       <div class="input-group-btn">
           <!--<button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">Action
                    <span class="fa fa-caret-down"></span></button> -->
          <ul class="dropdown-menu">
          <li>
              <?php $status=1; ?>
                 <?php
                if($status==1){ ?> 
                            <a class="btnDelete"  href="javascript:void(0);" title="True"  onclick="return change_status(<?php echo $id=1;?>,'type','0')"> <i class="fa fa-check"> </i> Active
                              
                            </a>
                            <?php }else{ ?>
                            <a class="btnDelete"  href="javascript:void(0);" title="False"  onclick="return change_status(<?php echo $id=1;?>,'type','1')" ?> <i class="fa fa-close"> Deactive
                              
                            </a>
                    <?php } ?>
              

          </li>
          <li><a class="" href="<?php  echo base_url('admin/patients-view/').$value['_id']->{'$id'};?>" >
                  <i class="fa fa-eye"></i> View </a>
            </li>
          <li><a class=""  id="confirm_btn_show" href="javascript:void(0);">
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
                  <th>S.NO</th>
                  <th>Holder Name</th>
                  <th>Bank</th>
                   <th>Card Type</th>
                  <th>Number</th>
                  <th>Expiry Date</th>
                  <th></th>
                </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
            </div>

              </div>
              <!-- /.tab-pane -->
            <?php /*  <div class="tab-pane" id="settings">
                <form class="form-horizontal">
                  <div class="form-group">
                    <label for="inputName" class="col-sm-2 control-label">Name</label>

                    <div class="col-sm-10">
                      <input type="email" class="form-control" id="inputName" placeholder="Name">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputEmail" class="col-sm-2 control-label">Email</label>

                    <div class="col-sm-10">
                      <input type="email" class="form-control" id="inputEmail" placeholder="Email">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-2 control-label">Name</label>

                    <div class="col-sm-10">
                      <input type="text" class="form-control" id="inputName" placeholder="Name">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputExperience" class="col-sm-2 control-label">Experience</label>

                    <div class="col-sm-10">
                      <textarea class="form-control" id="inputExperience" placeholder="Experience"></textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputSkills" class="col-sm-2 control-label">Skills</label>

                    <div class="col-sm-10">
                      <input type="text" class="form-control" id="inputSkills" placeholder="Skills">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <div class="checkbox">
                        <label>
                          <input type="checkbox"> I agree to the <a href="#">terms and conditions</a>
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" class="btn btn-danger">Submit</button>
                    </div>
                  </div>
                </form>
              </div>
              <!-- /.tab-pane -->  */ ?>
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->

  

<script>

$(document).ready(function() {
    $
});

</script> 
