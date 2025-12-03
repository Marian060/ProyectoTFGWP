(function($) {
const FIELD_VALUES = {
    DP_COUPON_DISCOUNT: 'Coupon Discount',
    DP_NEW_COUPON: 'new_coupon',
    DP_EXISTING_COUPON: 'existing_coupon',
    DP_PERCENT_DISCOUNT: 'Percent Discount',
    DP_FLAT_DISCOUNT: 'Flat Discount',
    DP_FIXED_PRICE: 'Fixed Price',
    DP_QUANTITY: 'Quantity',
    DP_WEIGHT: 'Weight',
    DP_PRICE: 'Price',
	DP_UNITS: 'Units',
	DP_PRODUCTS: 'products',
	DP_CATEGORIES: 'categories',
	DP_SET_CHEAPEST: 'set_cheapest',
    DP_SELECT_PRODUCT: 'select_product',
    DP_CART: 'cart',
    DP_TOTALQUANTITY: 'TotalQuantity',
    DP_PERCENT: 'percent',
    DP_FIXED_CART: 'fixed_cart',
    DP_FIXED_PRODUCT: 'fixed_product',
}

jQuery(document).ready(function($) {
    // bootstrap tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    // var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    //     return new bootstrap.Tooltip(tooltipTriggerEl, { container: ".elex-dynamic-pricing-wrap" });
    // });

    $(".dp-select-search").select2();

    $(".elex-dynamic-pricing-show-details").hide();
    $(".elex-dynamic-pricing-hide-details-btn").hide();

    $(".elex-dynamic-pricing-show-details-btn").click(function(){
        $(this).hide();
        $(this).parents(".elex-dynamic-pricing-more-details").find(".elex-dynamic-pricing-show-details").show();
        $(this).parents(".elex-dynamic-pricing-more-details").find(".elex-dynamic-pricing-hide-details-btn").show();
    });

    $(".elex-dynamic-pricing-hide-details-btn").click(function(){
        $(this).hide();
        $(this).parents(".elex-dynamic-pricing-more-details").find(".elex-dynamic-pricing-show-details").hide();
        $(this).parents(".elex-dynamic-pricing-more-details").find(".elex-dynamic-pricing-show-details-btn").show();
    });


    


    // ---------------------------popup js-------------------------------//
    $(".elex-dynamic-pricing-popup-open-btn").click(function(){
        $(".elex-dynamic-pricing-popup").addClass('active');
    });
    $(".elex-dynamic-pricing-popup-close-btn").click(function(){
        $(".elex-dynamic-pricing-popup").removeClass("active");
    
    });

    // bogo product page rules tab content
    $("#elex-dp-bogo-product-rules-discount-option").hide();
    $("#elex-dynamic-bogo-product-set-discount2, #elex-dynamic-bogo-product-set-discount1").change(function(e){
        if($("#elex-dynamic-bogo-product-set-discount2").is(':checked')) { 
            $(this).parents(".elex-dp-bogo-product-free-discount").find("#elex-dp-bogo-product-rules-discount-option").show();
        }else{
            $(this).parents(".elex-dp-bogo-product-free-discount").find("#elex-dp-bogo-product-rules-discount-option").hide();
        }
    });
    $('.elex-dp-remove-import-input').hide();

    //new script ...............>>

    $('#discount_type').on('change', function () {
        let thisval = $('#discount_type').val();
        if (thisval == FIELD_VALUES.DP_PERCENT_DISCOUNT) {
            $('.elex-adjustment-tab').show();
            $('#max_discount_parent').show();
            //For Outside coupon
            $("#discount_value").html('Discount Percent<span class="text-danger">*</span>');
            $("#discount_desc").show();
            val = document.getElementById("value1");
            $(val).prop("disabled", false);
            $('#value1').attr('required', 'required');
            $('#value1').show();
        } else if (thisval == FIELD_VALUES.DP_FLAT_DISCOUNT) {
            $('.elex-adjustment-tab').show();
            $('#max_discount_parent').hide();
            $('#max_discount').val('');
            //For Outside coupon
            $("#discount_value").html('Flat Discount<span class="text-danger">*</span>');
            $("#discount_desc").show();
            val = document.getElementById("value1");
            $(val).prop("disabled", false);
            $('#value1').attr('required', 'required');
            $('#value1').show();
        } else if (thisval == FIELD_VALUES.DP_FIXED_PRICE) {
            $('.elex-adjustment-tab').show();
            $('#max_discount_parent').show();
            //For Outside coupon
            $("#discount_value").html('Fixed Price<span class="text-danger">*</span>');
            $("#discount_desc").show();
            val = document.getElementById("value1");
            $(val).prop("disabled", false);
            $('#value1').attr('required', 'required');
            $('#value1').show();
        }
    });
    $('#discount_type').trigger('change');

    $('#check_on').on('change', function () {
        let thisval = $(this).val();
        let weightunit = $('#weight_unit').val();
        if(thisval == FIELD_VALUES.DP_QUANTITY){
            $('#minprice').html('Minimum No. of items<span class="text-danger">*</span>');
            $('#maxprice').html('Maximum No. of items');
        }else if(thisval == FIELD_VALUES.DP_WEIGHT){
            $('#minprice').html('Minimum Weight ('+ weightunit +') <span class="text-danger">*</span>');
            $('#maxprice').html('Maximum Weight ('+ weightunit +') ');
        }else if(thisval == FIELD_VALUES.DP_PRICE){
            $('#minprice').html('Minimum Price<span class="text-danger">*</span>');
            $('#maxprice').html('Maximum Price');
        }else if(thisval == FIELD_VALUES.DP_UNITS){
            $('#minprice').html('Minimum No. of Units<span class="text-danger">*</span>');
            $('#maxprice').html('Maximum No. of Units');
        }else if(thisval == FIELD_VALUES.DP_TOTALQUANTITY){
            $('#minprice').html('Minimum No. of Units<span class="text-danger">*</span>');
            $('#maxprice').html('Maximum No. of Units');
        }
    });
    $('#check_on').trigger('change');

    $('#rule_on').on('change', function () {
        let selected = $('#rule_on').val();

        $('#product_id').removeAttr('required');
        if (selected === FIELD_VALUES.DP_PRODUCTS){
            $('#category_rule_parent').hide();
            $('#product_rule_parent').show();
            $('#product_id').attr('required', 'required');
        } else if (selected === FIELD_VALUES.DP_CATEGORIES){
            $("#product_id").empty();
            $('#product_rule_parent').hide();
            $('#category_rule_parent').show();
        } else if (selected === FIELD_VALUES.DP_CART){
            $('#product_rule_parent').hide();
            $('#category_rule_parent').hide();
        }
    });
    $('#rule_on').trigger('change');

    jQuery('.desc-tip').tipTip({
        'attribute': 'data-tip',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200
    }); 

});

})(jQuery);
