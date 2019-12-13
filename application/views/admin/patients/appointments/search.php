 <section class="content">
  <div class="row">
     <div class="col-md-12">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Actions  </h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form class="form-horizontal" action="" method="POST" >
              <div class="box-body">
                <div class="form-group">
                    <label for="inputaction" class="col-sm-2 control-label">Start Date </label>
                      <div class="col-sm-2">
                      <input type="text" name="startdate" class="form-control pull-right" id="startdate" value="<?php echo $startdate ?>" >
                      </div>

                    <label for="inputaction" class="col-sm-2 control-label">End  Date </label>
                      <div class="col-sm-2">
                       <input type="text" name="enddate" class="form-control pull-right" id="enddate" value="<?php echo $enddate ?>"  >
                      </div>
                </div>
                  <div class="form-group" >
                      <label for="inputaction" class="col-sm-2 control-label">Appointment Type</label>
                        <div class="col-sm-2">
                        <?php 
                          echo form_dropdown("type", $search_type_list, $search_type, "class='small form-control' id='search_type'");
                          ?>
                        </div>
                      <label for="inputaction" class="col-sm-2 control-label">List type </label>
                        <div class="col-sm-2">
                        <?php 
                          $arr = ["All"];
                          echo form_dropdown("search_action", $arr, $search_action, "class='small form-control' id='search_action_type'");
                          ?>
                        </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <button type="submit" id="#schedule_search_action" class="btn btn-info pull-right">Submit</button>
              </div>
              <!-- /.box-footer -->
            </form>
          </div>
       <!-- /.box -->
  </div>
</section>

<script >
$(document).ready(function() {

$('#search_type').change(function(){
  var type =$('#search_type :selected').text();
  if(type=='Home'){
   option='<option value="0" selected="selected">All</option><option value="1">Booking</option><option value="2">Confirmed</option><option value="3">Cancelled</option><option value="4">Waiting</option>';
     } else if(type=='Video' || type=='Walkin' || type=='Chat' || type=='Audio'){
      option='<option value="0" selected="selected">All</option><option value="1">Pending</option><option value="2">Booked</option><option value="3">Cancelled</option>';
    }else{
      option='<option value="0" selected="selected">All</option>';
    }
   $('#search_action_type').html(option);
});

//Date picker
    $('#startdate').datepicker({
      autoclose: true,
    })

    $('#enddate').datepicker({
      autoclose: true,
    })

});
</script>