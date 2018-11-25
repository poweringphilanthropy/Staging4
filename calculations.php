<form action="" method="post">
	
	<label>Donation amount</label><input type="text" id="donation-amount" name="donation-amount" value="<?php echo $_POST['donation-amount'];?>">
	<label>Stripe percentage</label><input type="text" id="stripe-percent" name="stripe-percent" value="<?php echo $_POST['stripe-percent'];?>">
	<label>Platform percentage</label><input type="text" id="platform-percent" name="platform-percent" value="<?php echo $_POST['platform-percent'];?>">
	<label>Cover fees</label><input type="checkbox" id="update_donor_value" name="donor-covers-fee" value="yes">
		
	<input type="submit" value="calculate">
</form>

<?php 
echo  "<pre>";
if( isset($_POST)){
	$_zero_decimal_donation =  round( $_POST['donation-amount'] * 100, 0 ); 
	$_stripe_percent_decimal =  round(floatval($_POST['stripe-percent'] / 100) , 3);
	$_platform_percent_decimal = round(floatval($_POST['platform-percent'] / 100) ,3 );
	echo $_zero_decimal_charge_to_donor = $_zero_decimal_donation;
	$donor_covers_fee = isset($_POST['donor-covers-fee']) && ($_POST['donor-covers-fee'] == 'yes');
	if($donor_covers_fee){
		$_zero_decimal_charge_to_donor = ($_zero_decimal_donation + 30) / ( 1 - $_stripe_percent_decimal - $_platform_percent_decimal);
	}
	$_raw_stripe_fee = ($_zero_decimal_charge_to_donor * $_stripe_percent_decimal) + 30;

    	// echo "Charge to donor ".$charge_to_donor = round($_zero_decimal_charge_to_donor) / 100;
    	echo "Charge to donor ".$charge_to_donor = bcdiv($_zero_decimal_charge_to_donor	, 1, 0) / 100;
		echo "<br>";
    	echo "Stripe Fess ".$stripe_fee = $_raw_stripe_fee / 100;
		echo "<br>";
    	echo "Platform fees ".$platform_fee = $_zero_decimal_charge_to_donor * $_platform_percent_decimal  / 100;
		echo "<br>";
    	echo "Total Fees ".$total_fee = bcdiv($stripe_fee + $platform_fee , 1 , 2);
    	echo "Total Fees ".  round($stripe_fee + $platform_fee , 1);
		echo "<br>";
    	echo "Gross amount ".$gross_amount = $donation + $total_fee;
		echo "<br>";
    	echo "Net amount ".$net_amount = $charge_to_donor - $total_fee;
		echo "<br>";
		
		echo "-------------------------";
		echo "<br>";
		
		if($donor_covers_fee){
			$donation = $_POST['donation-amount']  + ($_POST['donation-amount']*$_POST['platform-percent'])/100;
		}
		
		echo "New stripe fees ".$stripe_fees = ($donation*$_POST['stripe-percent'])/100 + 	0.30;
		echo "<br>";
		echo "Charge to donor ".$donation;
		echo "<br>";
		echo "Charge to donor "; echo $donation + $stripe_fees;
		echo "<br>";
		// echo "New platform fees ".$stripe_fees = ($_POST['donation-amount']*$_POST['platform-percent'])/100;
		
}