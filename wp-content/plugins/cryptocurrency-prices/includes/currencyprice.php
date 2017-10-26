<?php
function cp_currencyprice_shortcode( $atts ) {

  if (isset($atts['currency1']) and $atts['currency1']!=''){
    //set first currency
    $currency1 = $atts['currency1'];
    //set default data for first currency
    $currency1_data = cp_prepare_currency_data($currency1, 1); 
    
    //set second currency
    if (isset($atts['currency2']) and $atts['currency2']!=''){
      $currency2_arr = explode(',', $atts['currency2']);
    } else {
      $currency2_arr = array('usd');
    }
    
    //set active shortcode feature
    if (isset($atts['feature']) and $atts['feature']!=''){
      $feature = $atts['feature'];
    } else {
      $feature = 'all';
    }
    
    $html_prices = '';
    $html_calc = '';
    
    //get data about cryptocurrencies
    //prepate api url
    $data_url_currency_1 = trim(mb_strtoupper($currency1_data['name']));
    $data_url_currency_2 = '';
    foreach ($currency2_arr as $currency2) {
      if ($data_url_currency_2 != ''){
        $data_url_currency_2 .= ',';
      }
      $data_url_currency_2 .= trim(mb_strtoupper($currency2));
    }    
    $data_url = 'https://min-api.cryptocompare.com/data/price?fsym='.$data_url_currency_1.'&tsyms='.$data_url_currency_2;
    //send api request
    $data_json = cp_get_url_data_curl($data_url);
  	
    if (isset($data_json) and $data_json!=''){
      $data_all_currencies_raw = json_decode($data_json, true);
      $data_all_currencies = array();
      //prepare data for easy search
      foreach ($data_all_currencies_raw as $data_all_currencies_raw_key => $data_all_currencies_raw_value){
        $key_lower = trim(mb_strtolower($data_all_currencies_raw_key));
        $data_all_currencies[$key_lower] = $data_all_currencies_raw_value;
      }
      
      $html_calc_id = $currency1_data['name'].'_calc';
      
      $html_prices .= '<h2>'.mb_strtoupper($currency1_data['name']).' price:</h2>';  
      $html_prices .= '<table style="font-size: large;">';
  
      $html_calc .= '
        <h2>'.mb_strtoupper($currency1_data['name']).' calculator:</h2>
        <form id="'.$html_calc_id.'">
        <input type="text" class="currency1value" value="1" /> '.$currency1_data['name'].' 
        = 
        <input type="text" class="currency2value" value="?" />  
      ';
      $html_calc .= '<select class="currency_switcher">';
      
      foreach ($currency2_arr as $currency2) {  
        $currency2_filtered = trim(mb_strtolower($currency2));
        $currency2_data = cp_prepare_currency_data($currency2_filtered, $data_all_currencies[$currency2_filtered]);
        
        $html_prices .= cp_render_price($currency1_data, $currency2_data);
  
        $html_calc .= cp_render_calc_option($currency1_data, $currency2_data);
      }
      $html_prices .= '</table>';
      
      $html_calc .= '</select>';
      $html_calc .= '</form>';
  
      //generate javascript for the calculator      
      $html_calc .= '
        <script type="text/javascript">
          function setCalculatorValue'.$html_calc_id.'(){
            var currency1valueold = jQuery("#'.$html_calc_id.' .currency1value").val();
            var currency2valueunit = jQuery("#'.$html_calc_id.' .currency_switcher").val();
            jQuery("#'.$html_calc_id.' .currency2value").val(currency1valueold*currency2valueunit);
          }
          function setCalculatorValue2'.$html_calc_id.'(){
            var currency2valueold = jQuery("#'.$html_calc_id.' .currency2value").val();
            var currency2valueunit = jQuery("#'.$html_calc_id.' .currency_switcher").val();
            jQuery("#'.$html_calc_id.' .currency1value").val(currency2valueold/currency2valueunit);
          }
          setCalculatorValue'.$html_calc_id.'(); //call at start
          jQuery("#'.$html_calc_id.' .currency1value").keyup(setCalculatorValue'.$html_calc_id.');
          jQuery("#'.$html_calc_id.' .currency_switcher").change(setCalculatorValue'.$html_calc_id.');
          jQuery("#'.$html_calc_id.' .currency2value").keyup(setCalculatorValue2'.$html_calc_id.');
        </script>
      ';
      
    } else {
      $error = 'Error: No data from the server!';
    }
    
  } else {
    $error = 'Error: No currency is set!';
  }
  
  //prepate final data
  $html = '';
  if (!$error){
    if ($feature == 'calculator' or $feature == 'all'){
      $html .= $html_calc;
    }
    if ($feature == 'prices' or $feature == 'all'){
      $html .= $html_prices;
    }
  } else {
    $html = $error;
  }
  
  $html .= cp_get_plugin_credit('cryptocompare');
  
	return $html;
}

function cp_render_calc_option($currency1, $currency2) {
  //select options for the calculator
  
  $price_per_unit = cp_calculate_price_per_unit($currency1['price'], $currency2['price']);
  
  $result .= '<option value="'.$price_per_unit.'">'.$currency2['name'].'</option>';
  
  return $result;
}

function cp_render_price($currency1, $currency2) {  
  //draws the actual ticker prices for table

  $picture1 = cp_get_currency_image($currency1['name']);
  $picture2 = cp_get_currency_image($currency2['name']);
  
  //calculate the price
  $price_per_unit = cp_calculate_price_per_unit($currency1['price'], $currency2['price']);
  if ($price_per_unit >= 10000){
    $price_per_unit_string = number_format(round($price_per_unit, 4), 4, '.', '');
  } elseif ($price_per_unit >= 1000){
    $price_per_unit_string = number_format(round($price_per_unit, 5), 5, '.', '');
  } elseif ($price_per_unit >= 100){
    $price_per_unit_string = number_format(round($price_per_unit, 6), 6, '.', '');
  } elseif ($price_per_unit >= 10){
    $price_per_unit_string = number_format(round($price_per_unit, 7), 7, '.', '');  
  } else {
    $price_per_unit_string = number_format(round($price_per_unit, 8), 8, '.', '');
  }
  
  $result = '
    <tr>
  		<td>
  			<img src="'.$picture1.'" title="'.$currency1['name'].'" class="crypto-ticker-icon" />
        1 '.mb_strtoupper($currency1['name']).' = 
  		</td>
  		<td>
        <img src="'.$picture2.'" title="'.$currency2['name'].'" class="crypto-ticker-icon" />
  			'.$price_per_unit_string.' '.mb_strtoupper($currency2['name']).'
  		</td>
    </tr>
  ';

  return $result;
}

function cp_prepare_currency_data($currency, $currency_price){
  $currency_data = array(
    'name' => trim(mb_strtolower($currency)),
  );
  
  if (!isset($currency_price) or $currency_price == 0 or $currency_price == null){
    //fix null price value
    $currency_data['price'] = 0;
  } else {
    //price is ok
    $currency_data['price'] = 1/$currency_price;
  }
  
  return $currency_data;
}

function cp_calculate_price_per_unit($currency1, $currency2){
  //calculate the price
  if ($currency2 != 0){
    $price_per_unit = $currency1 / $currency2;
  } else {
    //error in the data, avoid diviion by zero 
    $price_per_unit = 0;
  }
  
  return $price_per_unit; 
}