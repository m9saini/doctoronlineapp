  <!-- Main content -->
    <section class="invoice">
      <!-- title row -->
      <div class="row">
        <div class="col-xs-12">
          <h2 class="page-header">
            <i class="fa fa-globe"></i> Online Appointment, Info.
            <small class="pull-right">Date: <?php  date_default_timezone_set($timezone);
            echo date('m/d/Y',$appointment_view['appointment_date'])?></small>
          </h2>
        </div>
        <!-- /.col -->
      </div>
      <!-- info row -->
      <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
          Patient
          <address>
            <strong><?php echo $appointment_view['firstname'].' '.$appointment_view['lastname'] ?> </strong><br>
           <?php if(in_array($appointment_view['appointment_type'][0], ['Audio','Video','Chat'])){ ?>
            <?php echo (isset($provider_info['street_add']))?$provider_info['street_add'].'<br>':''; ?>
           <?php echo (isset($provider_info['city']))?$provider_info['city'].' ,':'';?> <?php echo (isset($provider_info['state']))?$provider_info['state'].', ':'' ?>  <?php echo (isset($provider_info['pincode']))?'Pincode: '.$provider_info['pincode'].'<br>':''; ?>
           <?php } else if($appointment_view['appointment_type'][0]=='Walkin'){ echo $appointment_view['complete_address'];?>

           <?php }else{ ?>
            <?php echo(isset($appointment_view['address1']))?$appointment_view['address1'].'<br>':''; ?>
            <?php echo $appointment_view['address2']; ?><br>
            <?php echo $appointment_view['city']; ?>, <?php echo $appointment_view['state'] ?>, Pincode: <?php echo $appointment_view['pincode'] ?><br>
           <?php }?>
            Phone: <?php echo (in_array($appointment_view['appointment_type'][0], ['Audio','Vedio','Chat']))?$patient_info['country_code'].$patient_info['mobile']:$appointment_view['country_code'].$appointment_view['mobile'] ?><br>
            Email: <?php echo $patient_info['email'] ?>
          </address>
        </div>
        <!-- /.col -->
        <?php if(count($provider_info)>0){ ?>
        <div class="col-sm-4 invoice-col">
          Provider
          <address>
            <strong><?php echo $provider_info['sufix'].' '.$provider_info['firstname'].' '.$provider_info['lastname']?></strong><br>
            <?php echo $provider_info['street_add']; ?><br>
            <?php echo $provider_info['city']; ?>, <?php echo $provider_info['state'] ?>, <?php echo (isset($provider_info['pincode']))?'Pincode: '.$provider_info['pincode'].'<br>':'' ?>
            Phone: <?php $provider_info['country_code'].$provider_info['mobile'] ?><br>
            Email: <?php echo $provider_info['email']; ?>
          </address>
        </div>
        <?php } ?>
        <!-- /.col -->
        <div class="col-sm-4 invoice-col">
          <b>Time # <?php  echo (isset($appointment_view['appointment_time']))?date('h:i a',$appointment_view['appointment_time']):'';?></b><br>
          <br>
          <b>Order ID:</b> <br>
          <b>Payment Due:</b> <br>
          <b>Account:</b> 
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
      <?php /*
      <!-- Table row -->
      <div class="row">
        <div class="col-xs-12 table-responsive">
          <table class="table table-striped">
            <thead>
            <tr>
              <th>Qty</th>
              <th>Product</th>
              <th>Serial #</th>
              <th>Description</th>
              <th>Subtotal</th>
            </tr>
            </thead>
            <tbody>
            
            <tr>
              <td>1</td>
              <td>Grown Ups Blue Ray</td>
              <td>422-568-642</td>
              <td>Tousled lomo letterpress</td>
              <td>$25.99</td>
            </tr>
            </tbody>
          </table>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
      */ ?>
      <?php /*
      <div class="row">
        <!-- accepted payments column -->
        <div class="col-xs-6">
          <p class="lead">Payment Methods:</p>
          <img src="../../dist/img/credit/visa.png" alt="Visa">
          <img src="../../dist/img/credit/mastercard.png" alt="Mastercard">
          <img src="../../dist/img/credit/american-express.png" alt="American Express">
          <img src="../../dist/img/credit/paypal2.png" alt="Paypal">

          <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
            Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles, weebly ning heekya handango imeem plugg
            dopplr jibjab, movity jajah plickers sifteo edmodo ifttt zimbra.
          </p>
        </div>
        <!-- /.col -->
        <div class="col-xs-6">
          <p class="lead">Amount Due 2/22/2014</p>

          <div class="table-responsive">
            <table class="table">
              <tr>
                <th style="width:50%">Subtotal:</th>
                <td>$250.30</td>
              </tr>
              <tr>
                <th>Tax (9.3%)</th>
                <td>$10.34</td>
              </tr>
              <tr>
                <th>Shipping:</th>
                <td>$5.80</td>
              </tr>
              <tr>
                <th>Total:</th>
                <td>$265.24</td>
              </tr>
            </table>
          </div>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
      */ ?>

      <!-- this row will not appear when printing -->
   <?php /*   <div class="row no-print">
        <div class="col-xs-12">
          <a href="invoice-print.html" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
          <button type="button" class="btn btn-success pull-right"><i class="fa fa-credit-card"></i> Submit Payment
          </button>
          <button type="button" class="btn btn-primary pull-right" style="margin-right: 5px;">
            <i class="fa fa-download"></i> Generate PDF
          </button>
        </div>
      </div> */ ?>
    </section>
    <!-- /.content -->

