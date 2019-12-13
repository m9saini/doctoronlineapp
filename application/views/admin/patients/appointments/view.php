 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       <?php echo $title; 
       $p_id=(string)$appointment_view['patient_id'];
       $appoint_date=(string)$appointment_view['appointment_date'];
       ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <?php if($user_type=='patients'){?>
        <li><a href="<?php echo base_url('admin/patients/appointments-all-list/').$p_id.'/'.$appoint_date; ?>"><i class="fa fa-dashboard"></i> Appointments List </a></li>
        <?php }else { ?>
        <li><a href="<?php echo base_url('admin/provider/schedule/').$userid.'/'.$app_date; ?>"><i class="fa fa-dashboard"></i>Schedule List </a></li>
        <?php } ?>
        <li class="active">View</li>
      </ol>
    </section>
<?php $this->load->view('admin/element/appointments/view');?>