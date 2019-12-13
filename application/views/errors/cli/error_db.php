
<?php
defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style type="text/css">

	::selection { background-color: #f07746; color: #fff; }
	::-moz-selection { background-color: #f07746; color: #fff; }

		
	#h1 {
		color: #<?php echo ($heading=='Error')?'fff':'f07746';?>;
		background-color: #<?php echo ($heading=='Error')?'dd4814':'d0d0d0';?>;
		border-bottom: 1px solid #d0d0d0;
		font-size: 22px;
		font-weight: bold;
		margin: 0 0 14px 0;
		padding: 5px 15px;
		line-height: 40px;
	}

	#container {
		margin: 10px;
		border: 1px solid #d0d0d0;
		box-shadow: 0 0 8px #d0d0d0;
		border-radius: 4px;
	}

	#body {
		margin: 0 0 10px;
		padding:0;
	}


	</style>
<div id="container">
		<h1 id="h1"><?php echo $heading; ?></h1>
		<div id="body">
			<?php echo $message; ?>
		</div>
	</div>
