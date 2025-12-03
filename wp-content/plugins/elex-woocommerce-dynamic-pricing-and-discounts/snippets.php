<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 


Re: #12019 / Dynamic Pricing / Code snippet to change headers of offer table
___________________________________________
// snipper for dynamic pricing plugin text change on Offers and Prcing Table
add_filter('gettext', 'xa_remove_admin_stuff', 20, 3);
function xa_remove_admin_stuff( $translated_text, $untranslated_text, $domain ) {
if($untranslated_text=='Bulk Product Offers')
{ return 'Bulk Product'; }
elseif($untranslated_text=='Fixed Price')
{ return 'FP'; }
return $translated_text;
}


////////////////////////////To Hide Regular Price from all products //////////////////////////////////////////////////////////

add_filter('woocommerce_get_price_html', "xa_only_sale_price", 99, 2);

function xa_only_sale_price($price, $product)
{
		if ($product->is_type('simple') || $product->is_type('variation')) {
			return regularPriceHTML_for_simple_and_variation_product($price, $product);
		} 
		return $price;    
}

function regularPriceHTML_for_simple_and_variation_product($price, $product){
	return wc_price($product->get_price());
}



////////////////////////////////////////////////////////////////////////////////////////


//snippet to round of totals in the cart
add_action('woocommerce_cart_total', 'cart_totals', 99, 1);
function cart_totals($wc_price)
{
	global $woocommerce;  
	$new_price = round($woocommerce->cart->total);
	
	return wc_price($new_price);
}

*/
