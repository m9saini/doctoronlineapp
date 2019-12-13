 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       <?php echo $title; ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/providers'); ?>"><i class="fa fa-dashboard"></i> List </a></li>
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
              <img class="profile-user-img img-responsive img-circle" src="<?php echo (isset($providersData['image']) && !empty($providersData['image']))?$providersData['image']:base_url('/assets/default-50x50.gif') ?>" alt="User profile picture">

              <h3 class="profile-username text-center"><?= $providersData['firstname'].' '.$providersData['lastname'] ?></h3>
              <?php if(isset($providersData['email'])) { ?> <p class="text-center" ><?php echo $providersData['email']; ?></p> <?php } ?>
               <ul class="list-group list-group-unbordered">
               <li class="list-group-item">
                  <b>Gender</b> <a class="pull-right"><?php echo $providersData['gender']; ?></a>
                </li>
                <li class="list-group-item">
                  <spam class=" fa fa-phone "></spam> <a class="pull-right"><?php echo '+'.$providersData['country_code']. $providersData['mobile']?></a>
                </li>
                <?php /*
                <li class="list-group-item">
                  <b>Patients</b> <a class="pull-right">13,287</a>
                </li> */ ?>
              </ul>

           <?php //   <a href="#" class="btn btn-primary btn-block"><b>Follow</b></a>  ?>
            </div>
            <!-- /.box-body -->
          </div>  </div>
          <!-- /.box -->

          
       
          <!-- About Me Box -->
<?php /// ?>
        <div class="col-md-9">

          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">About Me</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            <strong><i class="fa fa-map-marker margin-r-5"></i> Location</strong>

              <p class="text-muted"><?= isset($providersData['street_add'])?$providersData['street_add'].',':'' ?>
              <?= isset($providersData['city'])?$providersData['city'].' , ' . $providersData['state']:'' ?>
              <?= isset($providersData['zipcode'])?','.$providersData['zipcode']:'' ?>
              </p>

              <?php if(isset($specialities) ){ ?>
              <hr>
            
              <strong><i class="fa fa-pencil margin-r-5"></i> Specialities</strong>

              <p>
              <?php $colors=['success','info','primary','warning','danger'];

              foreach ($specialities as $key => $value) { ?>
                <span class="label label-<?php echo $colors[$key] ?>"><?php echo $value['name']; ?></span>
              <?php } ?>
              </p>
              <?php } ?>
              <?php if(isset($providersData['about']) && !empty($providersData['about'])) {?>
              <hr>  

              <strong><i class="fa fa-file-text-o margin-r-5"></i> Notes</strong>

              <p><?php echo $providersData['about']; ?></p>
              <?php } ?>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        <?php // close  ?>

       </div>
     

        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#works" data-toggle="tab">Work</a></li>
              <li><a href="#educations" data-toggle="tab">Education</a></li>
              <li><a href="#accounts" data-toggle="tab">Account</a></li>
            </ul>
            <div class="tab-content">
            <div class="active tab-pane" id="works">
            
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
                  <th>Name</th>
                  <th>Type</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Currently Work</th>
                  <th></th>
                </tr>
                </thead>
                <tbody>
                <?php  if($worksData) {
                foreach($worksData as $key=>$value): ?>
                        <tr>
                  <td><?php echo $key+1; ?></td>
                  <td><?php echo $value['name']; ?></td>
                  <td><?php echo $value['type']; ?></td>
                  <td><?php echo _customDate($value['from'],$timezone,'Y-m-d'); ?></td>
                  <td><?php echo _customDate($value['to'],$timezone,'Y-m-d'); ?></td>
                  <td><?php echo $value['is_currently']; ?></td>
                  
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
                              <th>Name</th>
                              <th>Type</th>
                              <th>From</th>
                              <th>To</th>
                              <th>Currently Work</th>
                              <th></th>
                            </tr>
                            </tfoot>
                          </table>
                        </div>
                        <!-- /.box-body -->
                        </div>
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="educations">
                  
                <div class="box">
            <div class="box-header">
              <h3 class="box-title"></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="educationtbl" class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                  <th>S.No</th>
                  <th>Name</th>
                  <th>Degree</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Completed</th>
                  <th></th>
                </tr>
                </thead>
                <tbody>
                <?php  if($educatoinsData) {
                foreach($educatoinsData as $key=>$value): ?>
                        <tr>
                  <td><?php echo $key+1; ?></td>
                  <td><?php echo $value['name']; ?></td>
                  <td><?php echo $value['degree']; ?></td>
                 <td><?php echo _customDate($value['from'],$timezone,'Y-m-d'); ?></td>
                  <td><?php echo _customDate($value['to'],$timezone,'Y-m-d'); ?></td>
                  <td><?php echo ($value['completed'])?'Yes':'No'; ?></td>
                  
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
                              <th>Name</th>
                              <th>Type</th>
                              <th>From</th>
                              <th>To</th>
                              <th>Completed</th>
                              <th></th>
                            </tr>
                            </tfoot>
                          </table>
                        </div>
                        <!-- /.box-body -->
                        </div>

              </div>
              <!-- /.tab-pane -->

              <div class="tab-pane" id="accounts">
                
                <div class="box">
            <div class="box-header">
              <h3 class="box-title"></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="accounttbl" class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                   <th>S.No</th>
                  <th>Acc. Name</th>
                  <th>Bank Name</th>
                  <th>Acc. Number</th>
                  <th>BSB</th>
                  <th>Paypal Email</th>
                  <th></th>
                </tr>
                </thead>
                <tbody>
                <?php  if($accountsData) {
                foreach($accountsData as $key=>$value): ?>
                        <tr>
                  <td><?php echo $key+1; ?></td>
                  <td><?php echo $value['account_name']; ?></td>
                  <td><?php echo $value['bank_name']; ?></td>
                  <td><?php echo $value['account_number']; ?></td>
                  <td><?php echo $value['bsb']; ?></td>
                  <td><?php echo $value['paypal_email']; ?></td>
                  
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
                              <th>Acc. Name</th>
                              <th>Bank Name</th>
                              <th>Acc. Number</th>
                              <th>BSB</th>
                              <th>Paypal Email</th>

                              <th></th>
                            </tr>
                            </tfoot>
                          </table>
                        </div>
                        <!-- /.box-body -->
                        </div>

              </div>
              <!-- /.tab-pane -->
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
   
});

</script> 
    

 


    </section>

