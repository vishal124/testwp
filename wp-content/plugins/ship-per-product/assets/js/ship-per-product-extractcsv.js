var files;
jQuery(document).ready(function($) {  
	if(extractcsv_obj.ifEdit == 'edit')
	{
		var data = [];
		data['action'] = 'ced_pbs_datable_process_edit';
		data['postID'] = extractcsv_obj.postID;
		data['varids'] = extractcsv_obj.postID;
		jQuery('.ced_pbs_datatable_edit').DataTable( {
			"processing": true,
	        "serverSide": true,
	        "ajax": {
	            "type": "POST",
		        "url": extractcsv_obj.ajax_url,
		        "data": data,
		        "dataType": "json",
		        "processData": true,
		        },
        "order": [[ 1, "asc" ]],
		"aoColumns": [
                       {"sClass": "check-column" },
                       null,
                       null,
                       null,
                       null,
                       null,
                       null
                     ],
       "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0] }],
       
         
		} );
	}
	
	if(extractcsv_obj.current_url == "product_based_shipping"){
		jQuery(".ced-bar-select2").select2();
	}
	if(extractcsv_obj.current_subsection == "product-based-shipping-code"){
		jQuery(".submit").hide();
	}
	jQuery('#ced_pbs_frm input[type=file]').on('change', function(event){
		files = event.target.files;
		var filetype = files[0]['type'];
		var csv_mimetypes = ['text/csv',
							    'application/csv',
							    'text/comma-separated-values',
							    'application/excel',
							    'application/vnd.ms-excel',
							    'application/vnd.msexcel',
							    'application/octet-stream',
								];
		if(jQuery.inArray(filetype, csv_mimetypes) == -1)
		{
			alert(extractcsv_obj.csv_error);
			jQuery('#ced_pbs_frm input[type=file]').val("");
		}
	});
	jQuery('#ced_pbs_import_button').on('click',function(){
		var val = jQuery('.ced_pbs_csv_custom_import').val();
		if(val == "" || val == null)
		{
			alert(extractcsv_obj.csv_error);
			return false;
		}
	})
	
	jQuery('.ced_pbs_csv_custom_import').on('change', function(event){
		files = event.target.files;
		if(files.length >0){
			var filetype = files[0]['type'];
			var csv_mimetypes = ['text/csv',
								    'application/csv',
								    'text/comma-separated-values',
								    'application/excel',
								    'application/vnd.ms-excel',
								    'application/vnd.msexcel',
								    'application/octet-stream',
									];
			if(jQuery.inArray(filetype, csv_mimetypes) == -1)
			{
				alert(extractcsv_obj.csv_error);
				jQuery('.ced_pbs_csv_custom_import').val("");
				return false;
			}
		}
	 });
	jQuery(document.body).on('click','.ced_pbs_edit',function(){
		jQuery('#ced_pbs_additional_hidden_field').remove();
		var country = jQuery(this).attr('data-country');
		var state   = jQuery(this).attr('data-state');
		var city    = jQuery(this).attr('data-city');
		var zip     = jQuery(this).attr('data-zip');
		var sku     = jQuery(this).attr('data-sku');
		var line    = jQuery(this).attr('data-line');
		var item    = jQuery(this).attr('data-item');
		var uID     = jQuery(this).attr('data-uid')
		jQuery('#ced_pps_country').val(country);
		jQuery('#ced_pps_state').val(state);
		jQuery('#ced_pps_city').val(city);
		jQuery('#ced_pps_zip').val(zip);
		jQuery('#ced_pps_sku').val(sku).change();
		jQuery('#ced_pps_line').val(line);
		jQuery('#ced_pps_item').val(item);
		jQuery('#ced_pps_wrapper_tr').append('<input type="hidden" name="ced_pps_hidden_unique_id" value="'+uID+'">')
		jQuery('html,body').animate({
	        scrollTop: jQuery('.ced_pbs_heading_rates').offset().top},'slow');
	})
	
	jQuery(document.body).on('change','#ced_pbs_woo_countries',function(){
		jQuery("#ced_pbs_selected_state_code").html("");
		jQuery("#ced_pbs_woo_states").val("");
		jQuery("#ced_pbs_no_state_found").html('');
		var countryCode = jQuery('#ced_pbs_woo_countries option:selected').val();
		var countryText = jQuery('#ced_pbs_woo_countries option:selected').text();
		var selectedCountryhtml = '<span class="description">Code of '+countryText+' is <b>'+countryCode+'</b></span> ';
		jQuery("#ced_pbs_selected_country_code").html(selectedCountryhtml);
		var data = {
				'action': 'ced_pbs_showing_codes',
				'countryCode': countryCode
			};
		jQuery.post(ajaxurl,data, function(response) {
			var countrycodeArr = jQuery.parseJSON(response);
			var customTr = "";
			var first = 0;
			for(var key in countrycodeArr)
			{
				if(first == 0)
				{
					var firstText = countrycodeArr[key];
					var selectedCountryhtml = '<span class="description">Code of '+countrycodeArr[key]+' is <b>'+key+'</b></span> ';
				}
				customTr += '<option value="'+key+'">'+countrycodeArr[key]+'</optionj>';
				first = first+1;
			}
			jQuery("#ced_pbs_woo_states").html(customTr);
			if(firstText == "" || firstText == null)
			{
				firstText = extractcsv_obj.no_states;
			}
			jQuery("#select2-chosen-2").text(firstText);
			
			jQuery("#ced_pbs_selected_state_code").html(selectedCountryhtml);
		});
	})
	jQuery(document.body).on('click','#ced_pbs_prod_save',function(e){
		var country = jQuery('#ced_pps_country').val();
		var state = jQuery('#ced_pps_state').val();
		var zip = jQuery('#ced_pps_zip').val();
		var line = jQuery('#ced_pps_line').val();
		var item = jQuery('#ced_pps_item').val();
		if(country == "" || country == null ||line == "" || line == null ||item == "" || item == null )
		{
			alert(extractcsv_obj.required); 
			return false;
		}
	});
	
	jQuery(document.body).on('change','#ced_pbs_woo_states',function(){
		var countryCode = jQuery('#ced_pbs_woo_states option:selected').val();
		var countryText = jQuery('#ced_pbs_woo_states option:selected').text();
		var selectedCountryhtml = '<span class="description">Code of '+countryText+' is <b>'+countryCode+'</b></span> ';
		jQuery("#ced_pbs_selected_state_code").html(selectedCountryhtml);
	});
	
});
jQuery('#ced_pbs_import_csv').click(function() {
	
	jQuery('#ced_pbs_import_upload_csv').toggleClass();
	return false;
});
jQuery(document).ready(function($) {
	jQuery(document.body).on('change','.ced_pbs_frm input[type=file]', function(event){
		var ths = jQuery(this);
		files = event.target.files;
		var filetype = files[0]['type'];
		var csv_mimetypes = ['text/csv',
							    'application/csv',
							    'text/comma-separated-values',
							    'application/excel',
							    'application/vnd.ms-excel',
							    'application/vnd.msexcel',
							    'application/octet-stream',
								];
		if(jQuery.inArray(filetype, csv_mimetypes) == -1)
		{
			alert(extractcsv_obj.csv_error);
			jQuery(this).val("");
		}
	});
	
	jQuery(document.body).on('click','.ced_pbs_ced_pbs_import_csv_prod', function(event){
		var ths = jQuery(this).attr('data-id');
		event.stopPropagation(); 
		event.preventDefault(); 
		// Create a formdata object and add the files
		var data = new FormData();
		if (typeof files !== 'undefined') {
			jQuery.each(files, function(key, value){
		        data.append(key, value);
		    });
	    }
		jQuery("#ced_pbs_csv_import_loading").show();
		data.append('action', 'extractcsv_file');
		data.append('productId', ths);
	    jQuery.ajax({
	    	url: extractcsv_obj.ajax_url,
	    	type: 'POST',
	    	data: data,
	        cache: false,
	        dataType: 'json',
	        processData: false, // Don't process the files
	        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
	        success: function(data, textStatus, jqXHR) {
	        	   if(data['success'] == null)
	        	   {
	        		   jQuery("#ced_pbs_csv_import_loading").hide();
	        		   alert(extractcsv_obj.csv_error);
	        	   }
	        	   else
	        	   {	   
	        		   var ship_rate = JSON.parse(JSON.stringify(data));
	        		   if(ship_rate.success == 'sucess')
        			   {
	        			   location.reload();
        			   }
	        	   }	   
		    },
		    error: function(jqXHR, textStatus, errorThrown) {
		    	 alert(extractcsv_obj.csv_error);
		    },
		    complete: function(){
		    	jQuery("#ced_pbs_csv_import_loading").hide();
		    	jQuery(".ced_pbs_overlay_toshow_"+ths).hide();
		    },
	    });
	});
	jQuery('#ced_pbs_import_csv_prod').click(function(event) {
		jQuery('#ced_pbs_frm input[type=file]').val("");
		div_show();
		return false;
	});
	jQuery(document.body).on('click','.ced_pbs_import_csv_var',function(event) {
		var idtoshow = jQuery(this).attr('data-toshow');
		jQuery('.ced_pbs_frm input[type=file]').val("");
		div_show_var(idtoshow);
		return false;
	});
	
	jQuery('#ced_pbs_import_csv').click(function() {
		jQuery('#ced_pbs_import_upload_csv').toggleClass('ced_pbs_no_display');
		return false;
	});
	jQuery(document.body).on('click','.var_ced_pbs_close',function(){
		jQuery('.ced_pbs_overlay').hide();
	 })
});

function show_rate(ship_rate) {//console.log(ship_rate['file']);return false;
	var size = jQuery('#'+ extractcsv_obj.ID +'_shipping_rate_prod tbody').find('tr').size();
	jQuery.each(ship_rate['file'], function(key, val){
		jQuery('<tr>\
			<td class="check-column"><input type="checkbox" name="select" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_country[' + size + ']" value="'+val[0]+'" class="country_n"  placeholder="'+extractcsv_obj.country_code+'" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_state[' + size + ']" value="'+val[1]+'" class="state_n" placeholder="'+extractcsv_obj.state_code+'" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_city[' + size + ']" value="'+val[2]+'" class="city_n" placeholder="'+extractcsv_obj.city_code+'"></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_zip[' + size + ']" value="'+val[3]+'" class="zip_n" placeholder="'+extractcsv_obj.zip_code+'"></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_line[' + size + ']" value="'+val[4]+'" class="line_n" placeholder="'+extractcsv_obj.line_cost+'(0.00)" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_item[' + size + ']" value="'+val[5]+'" class="item_n" placeholder="'+extractcsv_obj.item_cost+'(0.00)"  ></td>\
			</tr>').appendTo('#add-rows_prod');
		size++;
	});
}
function show_rate_var(ship_rate,ths) {//console.log(ship_rate['file']);return false;
	var size = jQuery('#'+ extractcsv_obj.ID +'_shipping_rate_prod tbody').find('tr').size();
	jQuery.each(ship_rate['file'], function(key, val){
		jQuery('<tr>\
			<td class="check-column"><input type="checkbox" name="select" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_var_country[' + size + ']" value="'+val[0]+'" class="country_n"  placeholder="'+extractcsv_obj.country_code+'" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_var_state[' + size + ']" value="'+val[1]+'" class="state_n" placeholder="'+extractcsv_obj.state_code+'" ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_var_city[' + size + ']" value="'+val[2]+'" class="city_n" placeholder="'+extractcsv_obj.city_code+'"></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_var_zip[' + size + ']" value="'+val[3]+'" class="zip_n" placeholder="'+extractcsv_obj.zip_code+'"></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_var_line[' + size + ']" value="'+val[4]+'" class="line_n" placeholder="'+extractcsv_obj.line_cost+'(0.00)"  ></td>\
			<td><input type="text" name="'+ extractcsv_obj.ID +'_var_item[' + size + ']" value="'+val[5]+'" class="item_n" placeholder="'+extractcsv_obj.item_cost+'(0.00)"  ></td>\
			</tr>').appendTo('.var_shipping_table_'+ths);
		size++;
	});
}

//Function To Display Popup
function div_show() {
document.getElementById('ced_pbs_overlay').style.display = "block";
}
function div_show_var(id) {
	jQuery(".ced_pbs_overlay_toshow_"+id).show();
}
function get_files(formid){
	jQuery('#'+formid+' input[type=file]').on('change', function(event){
		var ths = jQuery(this);
		files = event.target.files;
		console.log(files);
		var filetype = files[0]['type'];
		var csv_mimetypes = ['text/csv',
							    'application/csv',
							    'text/comma-separated-values',
							    'application/excel',
							    'application/vnd.ms-excel',
							    'application/vnd.msexcel',
							    'application/octet-stream',
								];
		if(jQuery.inArray(filetype, csv_mimetypes) == -1)
		{
			alert(extractcsv_obj.csv_error);
			jQuery(this).val("");
		}
	});
}
//Function to Hide Popup
function div_hide(){
	
document.getElementById('ced_pbs_overlay').style.display = "none";
}

jQuery('#'+ extractcsv_obj.ID +'_shipping_rate tfoot a.remove').live('click', function() {
	var ans = confirm(extractcsv_obj.del_conf);
	if(ans) {
		var arrTodelte = [];
		jQuery('#'+ extractcsv_obj.ID +'_shipping_rate table tbody tr td input:checked').each(function(i, elt){
			var todeleteID = jQuery(elt).closest('tr').attr('data-uid');
			
			arrTodelte.push(todeleteID);
			jQuery(elt).closest('tr').remove();
		});
		var data = {
				'action': 'ced_pbs_delete_rows',
				'arrTodelte': JSON.stringify(arrTodelte)
			};
		jQuery.post(ajaxurl,data, function(response) {
		});
	}
	return false;
});


jQuery(document).ready(function(){
	jQuery("#ced_pps_shipping_rate .ced_pbs_panel").on('click',function(){
		jQuery(this).next('.ced_pbs_content_sec').slideToggle('slow');
	});
});
