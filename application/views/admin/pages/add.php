   
   <section class="content-header">
      <h1><?php echo $title;?></h1>
    </section>
 
<!-- Main content -->
<section class="content">
  <div class="row">
	<div class="col-md-12">
	  <!-- general form elements -->
	  <div class="box box-primary">
		<!-- form start -->
		<?php
				$attribute = array('role' => 'form','id'=>'add_page','method'=>"POST");
				echo form_open_multipart('',$attribute);
							
		?>
			<div class="box-body">

				<?php $this->load->view('admin/element/pages/upsert');?>

			
							<div class="clearfix"></div>
                            
                            
							<div class="box-footer client-spacing">
							<?php 
								$js = 'class="btn btn-success"' ;
								echo form_submit('update','Submit',$js);
							?>
							<a href="<?php echo base_url('admin/pages');?>" class = "btn btn-primary">Cancel</a>
							</div>
						<?php echo form_close(); ?>
			  </div><!-- /.box -->

			</div><!-- /.col -->
		  </div><!-- /.row -->
		    
		</section>
<?php $this->load->view('admin/element/pages/script');?>