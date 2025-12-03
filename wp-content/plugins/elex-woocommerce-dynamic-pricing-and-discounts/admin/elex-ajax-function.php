<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

add_action( 'wp_ajax_xa_get_attributes_value_for_taxonomy', 'elex_dp_get_attributes_value_for_taxonomy' );

function elex_dp_get_attributes_value_for_taxonomy() {
	$taxonomy =$_POST['taxonomy'];
	$taxonomy_selected_value =$_POST['taxonomy_selected_value'];
	$options  = elex_dp_get_attributes_values_selectoptions($taxonomy, $taxonomy_selected_value);
	wp_die($options);
}

