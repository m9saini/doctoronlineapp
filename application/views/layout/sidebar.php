 <?php 
   $active_class  = $this->uri->segment(2); 
   $active_class2  = $this->uri->segment(3); 
?>
 <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
     <?php /* <div class="user-panel">
        <div class="pull-left image">
          <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>Alexander Pierce</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <!-- search form -->
      <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search...">
          <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form>
      <!-- /.search form --> */ ?>
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN NAVIGATION</li>
       <!--  Providers  Managment -->
        <li class="treeview <?php if(($active_class == 'providers') || ($active_class == 'providers-view')){echo 'active';} ?>"> <a href="<?php echo base_url('admin/providers');?>">
            <i class="fa fa-user-o"></i> <span>Providers</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
          <li class="<?php echo ($active_class =='providers')?'active':''; ?>">
              <a href="<?php echo base_url('admin/providers')?>"><i class="fa fa-table"></i> Provider List</a></li>
          <?php /*<li class="<?php echo ($active_class2 =='deactive')?'active':''; ?>"">
              <a href="<?php echo base_url('admin/providers/deactive')?>"><i class="fa fa-close"></i>Activated Deactivated Provider </a></li> */ ?>
          <li class="<?php echo ($active_class2 =='deleted')?'active':''; ?>"">
              <a href="<?php echo base_url('admin/providers/deleted')?>"><i class="fa fa-refresh"></i>Suspense Provider </a></li>
            
          </ul>
        </li>
        <!--  Patient Manggment   -->
         <li class="treeview <?php if(($active_class == 'patients') || ($active_class == 'patients-view')){echo 'active';} ?>">
          <a href="<?php echo base_url('admin/patients');?>">
            <i class="fa fa-user-o"></i> <span>Patients</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo ($active_class =='patients')?'active':''; ?>">
              <a href="<?php echo base_url('admin/patients')?>"><i class="fa fa-table"></i> Patients List</a></li>
            <?php /*<li class="<?php echo ($active_class2 =='deactive')?'active':''; ?>">
              <a href="<?php echo base_url('admin/patients/deactive')?>"><i class="fa fa-close"></i>Deactivated Patients</a></li> */?>
            <li class="<?php echo ($active_class2 =='deleted')?'active':''; ?>">
              <a href="<?php echo base_url('admin/patients/deleted')?>"><i class="fa fa-refresh"></i>Suspense Patients</a></li> 
          </ul>
        </li>
        <!-- Appointments  -->
          <li class="">
            <a href="<?php echo base_url('admin/patients/appointments-all-list'); ?>">
              <i class="fa fa-circle-o"></i>
              <span>Appointments</span>
            </a>
          </li>

          <!-- Schedule Listing  -->
          <li class="">
            <a href="<?php echo base_url('admin/provider/schedule'); ?>">
              <i class="fa fa-circle-o"></i>
              <span>Schedules</span>
            </a>
          </li>

          <li class="">
          <a href="<?php echo base_url('admin/providers/services-list'); ?>">
            <i class="fa fa-circle-o"></i>
            <span>Services</span>
          </a>
          </li>
        <!-- Static Pages -->
        <li class="treeview <?php if(($active_class == 'pages') || ($active_class == 'create') || ($active_class == 'edit')){echo 'active';} ?>">
          <a href="<?php echo base_url('admin/pages');?>">
            <i class="fa fa-files-o"></i>
            <span>Pages</span>
            <span class="pull-right-container">
              <span class="label label-primary pull-right"></span>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="<?php echo base_url('admin/pages');?>"><i class="fa fa-table"></i> List</a></li>
            <li><a href="<?php echo base_url('admin/pages/create');?>"><i class="fa fa-edit"></i> Add</a></li>
           
          </ul>
        </li>
        
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>