 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       <?php echo ucwords($title); ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/providers'); ?>"><i class="fa fa-dashboard"></i> Provider</a></li>
        <li class="active">Schedule List</li>
      </ol>
    </section>
<?php $this->load->view('admin/providers/schedule/search');?>
<section class="content">
 <div class="box">
    <div class="box-header">
      <h3 class="box-title">List</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
	    <div id="ajax_table" >
	    <?php $this->load->view('admin/providers/schedule/list_body'); ?>
	    </div>
    </div>
    <!-- /.box-body -->
 	</div>
 </section>
 

