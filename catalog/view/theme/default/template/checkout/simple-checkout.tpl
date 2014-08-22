<?php echo $header ?>

<form method="post" action="">

	<label>Customer info</label>
	<hr>
	<input type="text" value="" name="firstname" placeholder="firstname"> <br>
	<input type="text" value="" name="lastname" placeholder="lastname"> <br>
	<input type="text" value="" name="email" placeholder="email"> <br>
	<input type="text" value="" name="telephone" placeholder="telephone"> <br>
	<input type="text" value="" name="fax" placeholder="fax"> <br>
	<textarea name="comment" placeholder="comment"></textarea> <br>
	

	<label>Payment</label>
	<hr>
	<input type="text" value="" name="payment_firstname" placeholder="payment_firstname"> <br>
	<input type="text" value="" name="payment_lastname" placeholder="payment_lastname"> <br>
	<input type="text" value="" name="payment_company" placeholder="payment_company"> <br>
	<input type="text" value="" name="payment_company_id" placeholder="payment_company_id"> <br>
	<input type="text" value="" name="payment_tax_id" placeholder="payment_tax_id"> <br>
	<input type="text" value="" name="payment_address_1" placeholder="payment_address_1"> <br>
	<input type="text" value="" name="payment_address_2" placeholder="payment_address_2"> <br>
	<input type="text" value="" name="payment_city" placeholder="payment_city"> <br>
	<input type="text" value="" name="payment_postcode" placeholder="payment_postcode"> <br>
	<input type="text" value="" name="payment_zone" placeholder="payment_zone"> <br>
	<input type="text" value="" name="payment_zone_id" placeholder="payment_zone_id"> <br>
	<input type="text" value="" name="payment_country" placeholder="payment_country"> <br>
	<input type="text" value="" name="payment_country_id" placeholder="payment_country_id"> <br>
	<input type="text" value="" name="payment_address_format" placeholder="payment_address_format"> <br>
	<input type="text" value="" name="payment_method" placeholder="payment_method"> <br>
	<input type="text" value="" name="payment_code" placeholder="payment_code"> <br>

	<label>Shipping</label>
	<hr>
	<input type="text" value="" name="shipping_firstname" placeholder="shipping_firstname"> <br>
	<input type="text" value="" name="shipping_lastname" placeholder="shipping_lastname"> <br>
	<input type="text" value="" name="shipping_company" placeholder="shipping_company"> <br>
	<input type="text" value="" name="shipping_address_1" placeholder="shipping_address_1"> <br>
	<input type="text" value="" name="shipping_address_2" placeholder="shipping_address_2"> <br>
	<input type="text" value="" name="shipping_city" placeholder="shipping_city"> <br>
	<input type="text" value="" name="shipping_postcode" placeholder="shipping_postcode"> <br>
	<input type="text" value="" name="shipping_zone" placeholder="shipping_zone"> <br>
	<input type="text" value="" name="shipping_zone_id" placeholder="shipping_zone_id"> <br>
	<input type="text" value="" name="shipping_country" placeholder="shipping_country"> <br>
	<input type="text" value="" name="shipping_country_id" placeholder="shipping_country_id"> <br>
	<input type="text" value="" name="shipping_address_format" placeholder="shipping_address_format"> <br>
	<input type="text" value="" name="shipping_method" placeholder="shipping_method"> <br>
	<input type="text" value="" name="shipping_code" placeholder="shipping_code"> <br>

	<input type="submit" value="Test value" placeholder="Test placeholder">
</form>


<?php echo $footer ?>