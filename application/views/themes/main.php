<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
  <link rel="shortcut icon" type="image/png" href="favicon.jpg"/>
  <title><?php echo SITE_NAME ; ?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  
  <link rel="icon" href="<?=base_url()?>favicon.png">
  <!-- FANCY POPUP DIALOGS SCRIPT AND CSS -->
    <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/alertify/css/alertify.css" />
  <link rel="stylesheet" href="<?php  echo base_url();?>assets/bower_components/alertify/css/themes/default.css" id="toggleCSS" /> 
  <link rel="stylesheet" href="<?php  echo base_url();?>assets/dist/css/admin.css" /> 
  <style>
    .alertify-log-custom {
      background: blue;
    }
  </style>
  <script src="<?php  echo base_url();?>assets/bower_components/alertify/alertify.js"></script>
  <script src="<?php echo base_url();?>assets/bower_components/alertify/alertify.min.js"></script>
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/Ionicons/css/ionicons.min.css">

  <!-- fullCalendar -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/fullcalendar/dist/fullcalendar.min.css">
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/fullcalendar/dist/fullcalendar.print.min.css" media="print">
   <!-- DataTables -->
   <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/dist/css/skins/_all-skins.min.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/morris.js/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/jvectormap/jquery-jvectormap.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">

  <script src="<?php echo base_url();?>assets/bower_components/jquery/dist/jquery.js"></script>
  <!-- jQuery 3 -->
<script src="<?php echo base_url();?>assets/bower_components/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?php echo base_url();?>assets/bower_components/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!---Bootstrap Validation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.js"></script>
<!--End Bootstrap Validation -->
<!-- Bootstrap 3.3.7 -->
<script src="<?php echo base_url();?>assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="<?php echo base_url();?>assets/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url();?>assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<!-- Loader Class -->
<div id="preloader">
       <!-- <div id="status"><img src="<?php  echo base_url().'assets/frontend/loading.svg';?>" alt="loading....."></div> -->
</div>
<!-- End Loader -->

  <div class="wrapper">
     <?php 
		 $data['auth_data'] = '' ; 
		  $this->load->view('layout/header',$data); 
		  $this->load->view('layout/sidebar',$data);
	 ?>
      <div class="content-wrapper">
		<?php
		echo $output;
		?>
      </div>
   
  </div>
  <footer class="main-footer">
        <div class="pull-right hidden-xs">
          <b>Version</b> 1.0
        </div>
        <strong>Copyright &copy; <?php echo date("Y"); ?> <a target="_blank" href="<?php echo SITE_URL; ?>"><?php echo SITE_NAME; ?></a>.</strong> All rights reserved.
  </footer>
<!-- ./wrapper -->


<!-- Morris.js charts -->
<script src="<?php echo base_url();?>assets/bower_components/raphael/raphael.min.js"></script>
<script src="<?php echo base_url();?>assets/bower_components/morris.js/morris.min.js"></script>
<!-- Sparkline -->
<script src="<?php echo base_url();?>assets/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
<!-- jvectormap -->
<script src="<?php echo base_url();?>assets/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="<?php echo base_url();?>assets/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<!-- jQuery Knob Chart -->
<script src="<?php echo base_url();?>assets/bower_components/jquery-knob/dist/jquery.knob.min.js"></script>
<!-- daterangepicker -->

<script src="<?php echo base_url();?>assets/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="<?php echo base_url();?>assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- Bootstrap WYSIHTML5 -->
<script src="<?php echo base_url();?>assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Slimscroll -->
<script src="<?php echo base_url();?>assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="<?php echo base_url();?>assets/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo base_url();?>assets/dist/js/adminlte.min.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="<?php echo base_url();?>assets/dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="<?php echo base_url();?>assets/dist/js/demo.js"></script>

<script src="<?php echo base_url();?>assets/bower_components/moment/min/moment.min.js"></script>
<script src="<?php echo base_url();?>assets/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>

<?php $this->load->view('layout/script'); ?>
<script>
  $(function () {
    $('#example1').DataTable()
    $('#educationtbl').DataTable()
    $('#accounttbl').DataTable()
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })
</script>


<?php 
    if($this->session->flashdata('flashSuccess')){
        $flashSuccess =  $this->session->flashdata('flashSuccess');
      echo "<script>alertify.success('$flashSuccess')</script>";
    } 
    if($this->session->flashdata('flashError')){
        $flashError =  $this->session->flashdata('flashError');
      echo "<script>alertify.error('$flashError')</script>";
    } 
?>

<!-- Model confirm message Sow -->
<div id="ConfirmModalShow" class="modal fade " role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
  <div class="modal-body" id="ConfirmModalContent">

  </div>
  <div class="modal-footer">
    <button type="button" data-dismiss="modal" class="btn btn-default" id="confirm_ok">Ok</button>
    <button type="button" data-dismiss="modal" class="btn btn-primary">Cancel</button>
  </div>
  </div>
  </div>
  
</div>
<!-- confirm Model close -->
</body>
</html>
