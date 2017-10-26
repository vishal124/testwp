var files;
jQuery(document).ready(function($) {
	$('#frm input[type=file]').on('change', prepareUpload);
	$('#prepareUpload').on('click', uploadFiles);
	$('#import-csv_prod').click(function(event) {
		div_show();
		return false;
	});
	jQuery('#import-csv').click(function() {
		jQuery('#import-upload-csv').toggleClass('no-display');
		return false;
	});
	if(extractcsv_obj.current_url == "ship_per_product"){
		jQuery(".ced-bar-select2").select2();
	}
});

/**
 * Grab the files and set them to our variable
 * @name prepareUpload(event)
 * @author Cedcommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function prepareUpload(event)
{
  files = event.target.files;
  console.log(files);
}
/**
 * update meta field data
 * @name uploadFiles(event)
 * @param event
 * @author Cedcommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function uploadFiles(event) {
	 event.stopPropagation(); 
	 event.preventDefault(); 
	// START A LOADING SPINNER HERE
	    // Create a formdata object and add the files
	    var data = new FormData();
	    if (typeof files !== 'undefined') {
	    jQuery.each(files, function(key, value)
	    {
	        data.append(key, value);
	    });
	    }
	    data.append('action', 'extractcsv_file');
	    jQuery.ajax({
	    	url: extractcsv_obj.ajax_url,
	    	type: 'POST',
	    	data: data,
	        cache: false,
	        dataType: 'json',
	        processData: false, // Don't process the files
	        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
	        success: function(data, textStatus, jqXHR) {
		           var ship_rate = JSON.parse(JSON.stringify(data));
		           show_rate(ship_rate);
		    },
		    error: function(jqXHR, textStatus, errorThrown) {
		    },
		    complete: function(){
		    	div_hide();
		    },
	    });
}

jQuery('#'+ extractcsv_obj.ID +'_shipping_rate_prod tfoot a.add').live('click', function() {
	var size = jQuery('#'+ extractcsv_obj.ID +'_shipping_rate_prod tbody').find('tr').size();

	jQuery('<tr>\
		<td class="check-column"><input type="checkbox" name="select" ></td>\
		<td><input type="text" name="'+ extractcsv_obj.ID +'_country[' + size + ']" class="country_n" size="2" ></td>\
		<td><input type="text" name="'+ extractcsv_obj.ID +'_state[' + size + ']" class="state_n" ></td>\
		<td><input type="text" name="'+ extractcsv_obj.ID +'_city[' + size + ']" class="city_n" ></td>\
		<td><input type="text" name="'+ extractcsv_obj.ID +'_zip[' + size + ']" class="zip_n" ></td>\
		<td><input type="text" name="'+ extractcsv_obj.ID +'_line[' + size + ']" class="line_n" placeholder="0.00" size="6" ></td>\
		<td><input type="text" name="'+ extractcsv_obj.ID +'_item[' + size + ']" class="item_n" placeholder="0.00" size="6" ></td>\
		</tr>').appendTo('#add-rows_prod');
	
	return false;
});
/**
 * @name show_rate(ship_rate)
 * @param ship_rate
 * @author Cedcommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function show_rate(ship_rate) {
	var size = jQuery('#'+ extractcsv_obj.ID +'_shipping_rate_prod tbody').find('tr').size();
	jQuery.each(ship_rate['file'], function(key, val){
		jQuery('<tr>\
			<td class="check-column"><input type="checkbox" name="select" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_country[' + size + ']" value="'+val[0]+'" class="country_n" size="2" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_state[' + size + ']" value="'+val[1]+'" class="state_n" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_city[' + size + ']" value="'+val[2]+'" class="city_n" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_zip[' + size + ']" value="'+val[3]+'" class="zip_n" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_line[' + size + ']" value="'+val[4]+'" class="line_n" placeholder="0.00" size="6" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_item[' + size + ']" value="'+val[5]+'" class="item_n" placeholder="0.00" size="6" ></td>\
			</tr>').appendTo('#add-rows_prod');
		size++;
	});
}
jQuery('#'+ extractcsv_obj.ID +'_shipping_rate_prod tfoot a.remove').live('click', function() {
	var ans = confirm(extractcsv_obj.del_conf);
	if(ans) {console.log(jQuery('#'+ extractcsv_obj.ID +'_shipping_rate_prod table tbody tr td.check-column input:checked'));
		jQuery('#'+ extractcsv_obj.ID +'_shipping_rate_prod tbody tr td.check-column input:checked').each(function(i, elt){
			jQuery(elt).closest('tr').remove();
		});
	}
	return false;
});

//Function To Display Popup
/**
 * Function To Display Popup
 * @name div_show()
 * @author Cedcommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function div_show() {
document.getElementById('overlay').style.display = "block";
}

/**
 * Function to Hide Popup
 * @name div_hide()
 * @author Cedcommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function div_hide(){
document.getElementById('overlay').style.display = "none";
}

jQuery('#'+ extractcsv_obj.ID +'_shipping_rate tfoot a.add').live('click', function() {
	var size = jQuery('#'+ extractcsv_obj.ID +'_shipping_rate tbody').find('tr').size();
	var products = extractcsv_obj.products
	var options = "";
	for(var name in products){
		var options = options+'<option value="'+name+'" >'+products[name]+'</option>'
	}
	jQuery('<tr>\
			<td class="check-column ced-spp-check-style"><input type="checkbox" name="select" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_country[' + size + ']" class="country_n" Placeholder="Country" size="2" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_state[' + size + ']" Placeholder="State" class="state_n" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_city[' + size + ']" Placeholder="City" class="city_n" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_zip[' + size + ']" Placeholder="Zip" class="zip_n" ></td>\
			<td> <select class="ced-bar-select2" name="'+ extractcsv_obj.ID +'_sku[' + size + ']">\
	    	'+options+'\
    		</select></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_line[' + size + ']" class="line_n" placeholder="0.00" size="6" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_item[' + size + ']" class="item_n" placeholder="0.00" size="6" ></td>\
			</tr>').appendTo('#add-rows');
	jQuery("select.ced-bar-select2").select2();
	return false;
});
jQuery('#'+ extractcsv_obj.ID +'_shipping_rate tfoot a.remove').live('click', function() {
	var ans = confirm(extractcsv_obj.del_conf);
	if(ans) {
		jQuery('#'+ extractcsv_obj.ID +'_shipping_rate table tbody tr td.check-column input:checked').each(function(i, elt){
			jQuery(elt).closest('tr').remove();
		});
	}
	return false;
});
