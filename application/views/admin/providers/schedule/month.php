 <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       <?php echo $title; ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <?php if($second_tab_link) { ?>
        <li><a href="<?php echo base_url('admin/providers'); ?>"><i class="fa fa-dashboard"></i> Provider List </a></li>
        <?php } else {?>
        <li><a href="<?php echo base_url('admin/provider/schedule'); ?>"><i class="fa fa-dashboard"></i> Schedule List </a></li>
        <?php } ?>
        <li class="active">View</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-3">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h4 class="box-title">Schedule Type</h4>
            </div>
            <div class="box-body">
              <!-- the events -->
              <div id="external-events">
                <div class="external-event bg-light-blue">Audio</div>
                <div class="external-event bg-green">Video</div>
                <div class="external-event bg-aqua">Chat</div>
                <div class="external-event bg-yellow">Walkin</div>
              <?php /*  <div class="external-event bg-red">Sleep tight</div> 
                <div class="checkbox">
                  <label for="drop-remove">
                    <input type="checkbox" id="drop-remove">
                    remove after drop
                  </label>
                </div> */ ?>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /. box -->
          <div class="box box-solid"> <?php /*
            <div class="box-header with-border">
              <h3 class="box-title">Create Event</h3>
            </div>
            <div class="box-body">
              <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                <!--<button type="button" id="color-chooser-btn" class="btn btn-info btn-block dropdown-toggle" data-toggle="dropdown">Color <span class="caret"></span></button>-->
                <ul class="fc-color-picker" id="color-chooser">
                  <li><a class="text-aqua" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-blue" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-light-blue" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-teal" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-yellow" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-orange" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-green" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-lime" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-red" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-purple" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-fuchsia" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-muted" href="#"><i class="fa fa-square"></i></a></li>
                  <li><a class="text-navy" href="#"><i class="fa fa-square"></i></a></li>
                </ul>
              </div>
              <!-- /btn-group -->
              <div class="input-group">
                <input id="new-event" type="text" class="form-control" placeholder="Event Title">

                <div class="input-group-btn">
                  <button id="add-new-event" type="button" class="btn btn-primary btn-flat">Add</button>
                </div>
                <!-- /btn-group -->
              </div>
              <!-- /input-group -->
            </div> */ ?>
          </div>
        </div>
        <!-- /.col -->
        <div class="col-md-9">
          <div class="box box-primary">
            <div class="box-body no-padding">
              <!-- THE cal_event -->
              <div id="cal_event"></div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /. box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
<!-- Page specific script -->
<script>
  $(function () {
     var monthsEvents  = <?php echo $events; ?>;
    set_event(monthsEvents);
    /* initialize the external events
     -----------------------------------------------------------------*/
   
   
      $('.fc-center').click(function() {
       $('#cal_event').datepicker({
      autoclose: true,

    })
    });
    /* initialize the calendar
     -----------------------------------------------------------------*/
    
    /* ADDING EVENTS */
    var currColor = '#3c8dbc' //Red by default
    //Color chooser button
    var colorChooser = $('#color-chooser-btn')
    $('#color-chooser > li > a').click(function (e) {
      e.preventDefault()
      //Save color
      currColor = $(this).css('color')
      //Add color effect to button
      $('#add-new-event').css({ 'background-color': currColor, 'border-color': currColor })
    })
    $('#add-new-event').click(function (e) {
      e.preventDefault()
      //Get value and make sure it is not null
      var val = $('#new-event').val()
      if (val.length == 0) {
        return
      }

      //Create events
      var event = $('<div />')
      event.css({
        'background-color': currColor,
        'border-color'    : currColor,
        'color'           : '#fff'
      }).addClass('external-event')
      event.html(val)
      $('#external-events').prepend(event)

      //Add draggable funtionality
      init_events(event)

      //Remove event from text input
      $('#new-event').val('')
    })

    $('.fc-prev-button').click(function(){

      var b = $('#cal_event').fullCalendar('getDate');
      var cal_date = b.format();
      var cal_date = new Date(cal_date);
      var date = new Date();
      var current_y     = date.getFullYear();
          cal_y         = cal_date.getFullYear();
          cal_m         = cal_date.getMonth();
      if(cal_m==0 ){
        $('.fc-prev-button').prop('disabled', true);
        $('.fc-next-button').prop('disabled', false);;
      }
          
          
    });

    $('.fc-next-button').click(function(){

      var b = $('#cal_event').fullCalendar('getDate');
      var cal_date = b.format();
      var cal_date = new Date(cal_date);
      var date = new Date();
      var current_y     = date.getFullYear();
          cal_y         = cal_date.getFullYear();
          cal_m         = cal_date.getMonth();
      if(cal_m==11){
         $('.fc-prev-button').prop('disabled', false);
        $('.fc-next-button').prop('disabled', true);
      }
       
      
      /*
      url= "<?php echo base_url().'admin/provider/schedule/months-n/'.$provider_id.'/'?>" + month_int;
        
      $.ajax({
              method: "GET",
              url: url,
              })
              .done(function( result ) {

                set_event(result);
               //var  myCalendar= $('#cal_event').fullCalendar();
                //myCalendar.fullCalendar('removeEvents');
               // myCalendar.fullCalendar();
                //myCalendar.fullCalendar('renderEvent', result );
                //myCalendar.fullCalendar('refetchEvents');
            }); */
     
    });
   
    
    

  })




   function init_events(ele) {
      ele.each(function () {

        // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
        // it doesn't need to have a start or end
        var eventObject = {
          title: $.trim($(this).text()) // use the element's text as the event title
        }

        // store the Event Object in the DOM element so we can get to it later
        $(this).data('eventObject', eventObject)

        // make the event draggable using jQuery UI
        $(this).draggable({
          zIndex        : 1070,
          revert        : true, // will cause the event to go back to its
          revertDuration: 0  //  original position after the drag
        })

      })
    }

   function set_event(events_list){
      //Date for the calendar events (dummy data)
   // $('#cal_event').fullCalendar('removeEvents');
   
    $('#cal_event').fullCalendar({
     header    : {
        left  : 'prev',
        center: 'title',
        right : 'next today,'
      },

      events : events_list ,
      eventClick : function(event) {
      if (event.title) { 
       var url= "<?php echo base_url().'admin/provider/schedule/slot'; ?>" ;


      $.ajax({
              method: "POST",
              url: url,
              data:{schedule_id:event.schedule_id,type:event.title}
              })
              .done(function( result ) {
                if(result){
                  var type= event.title;
                  var slot=JSON.parse(result);
                  var btn_list='';
                  for (var i = 0; i < slot[event.title]['slot']['list'].length; i++) {

                    var booked=0;
                    var drowp_btn ='<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu"><li><a href="#">Action</a></li><li><a href="#">Another action</a></li><li><a href="#">Something else here</a></li><li class="divider"></li><li><a href="#">Separated link</a></li></ul>';
                      drowp_btn='';
                      if(slot[event.title]['slot']['list'][i]['appointment_status']==1)
                            btn_color=" btn-default";
                      else if(slot[event.title]['slot']['list'][i]['appointment_status']==2){
                            btn_color=" btn-success text-lime";
                            booked=(slot[event.title]['slot']['list'][i]['appointment_id'])?1:0;
                          }
                        else{
                            if(type=='Audio')
                              btn_color=" btn-info bg-light-blue";
                            else if(type=='Video')
                              btn_color=" btn-info bg-green";
                            else if(type=='Chat')
                              btn_color=" btn-info bg-aqua";
                            else
                              btn_color=" btn-info bg-yellow";
                          }
                      if(booked){
                        var href="<?php echo base_url().'admin/provider/appointment-view/'?>"+slot[event.title]['slot']['list'][i]['appointment_id']+"<?php echo '/'.$provider_id;?>";
                        var str='<a href="'+ href +'"><div class="btn-group"><button type="button" class="btn'+ btn_color + '">'+slot[event.title]['slot']['list'][i]['appointment_time']+'</button>'+ drowp_btn +'</div></a>';
                       btn_list=btn_list+str;
                      }else{
                      var str='<div class="btn-group"><button type="button" class="btn'+ btn_color + '">'+slot[event.title]['slot']['list'][i]['appointment_time']+'</button>'+ drowp_btn +'</div>';
                       btn_list=btn_list+str;
                     }
                 
                  }
                  $('#modal-slot').modal({backdrop: 'static', keyboard: false , show: true});
                      $('#modal-slot-title').html('<span>'+event.title+'<span>');
                      $('#model-slot-body').html(btn_list);
                }else{
                    $('#modal-slot').modal({backdrop: 'static', keyboard: false , show: true});
                    $('#modal-slot-title').html('<span>'+event.title+'<span>');
                    $('#model-slot-body').html('<p>You have no any schedule </p>');
                }
            }); 
     
      //setTimeout(function(){  $('#AlertMessageShow').modal('hide'); }, 1000);
      }
    }
                        

    });
      init_events($('#external-events div.external-event'))
    }
</script>

 <div class="modal fade" id="modal-slot">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"> <spam id="modal-slot-title"></spam> </h4>
              </div>
              <div class="modal-body" >
                <div class="margin"  id="model-slot-body" >

               </div>
              </div>
              <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-primary">Close</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->