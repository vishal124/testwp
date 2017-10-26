<?php
function cp_all_currencies_shortcode($atts){
  $data_url = 'https://www.cryptocompare.com/api/data/coinlist/';
  //send api request
  $data_json = cp_get_url_data_curl($data_url);
  $data_all_currencies_raw = json_decode($data_json, true);
  $data_all_currencies = $data_all_currencies_raw['Data'];
  
  //sort currencies by order
  usort($data_all_currencies, 'sortByOrder');
  
  $html .= '<table>';
  $html .= '<tr><th>Coin</th><th>Algorithm; Proof type</th><th>Total supply</th></tr>';
  
  foreach ($data_all_currencies as $data_currency){
    $picture = cp_get_currency_image($data_currency['Name']);
    
    if ( isset($data_currency['TotalCoinSupply']) && $data_currency['TotalCoinSupply']!= 0 ){
      $total_supply = htmlspecialchars($data_currency['TotalCoinSupply']);
    } else {
      $total_supply = '-';
    }
    
    $html .=  '<tr>';
    $html .=  '
      <td>
        <img src="'.$picture.'" alt="'.htmlspecialchars($data_currency['FullName']).'" />
        '.htmlspecialchars($data_currency['FullName']).'
      </td>
      <td>'.htmlspecialchars($data_currency['Algorithm']).'; '.htmlspecialchars($data_currency['ProofType']).' </td>
      <td>'.$total_supply.'</td>
    ';
    
    //var_dump($data_currency);
    
    $html .=  '</tr>';  
  }
  
  $html .= '</table>';
  
  $html .= cp_get_plugin_credit('cryptocompare');
  
  return $html;
}

function sortByOrder($a, $b) {
  return $a['SortOrder'] - $b['SortOrder'];
}