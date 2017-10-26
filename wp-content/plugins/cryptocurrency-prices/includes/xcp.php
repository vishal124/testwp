<?php
function cp_xcpasset_shortcode( $atts ) {
  if (isset($atts['holders'])){
    $asset = $atts['holders'];
    $api_url = 'https://counterpartychain.io/api/holders/'.$asset;
    $data = cp_get_url_data_curl($api_url);
    $data_decoded = json_decode($data);

    $html = '';
    $html .= '<h2>'.mb_strtoupper($asset).' Holders</h2>';
    $html .= '<table>';
    $html .= '<tr><th>Address</th><th>Amount</th><th>Percent</th></tr>';
    foreach ($data_decoded->data as $data_row) {
      $html .= '
        <tr>
          <td>'.$data_row->address.'</td>
          <td>'.$data_row->amount.'</td>
          <td>'.$data_row->percent.'</td>
        </tr>
      ';
    }
    $html .= '</table>';

  } elseif (isset($atts['transactions'])){
    $asset = $atts['transactions'];
    $api_url = 'https://counterpartychain.io/api/sends/'.$asset;
    $data = cp_get_url_data_curl($api_url);
    $data_decoded = json_decode($data);

    $html = '';
    $html .= '<h2>'.mb_strtoupper($asset).' Transactions</h2>';
    $html .= '<table style="font-size: large;">';
    $html .= '<tr><th>Time</th><th>Destination</th></tr>';
    foreach ($data_decoded->data as $data_row) {
      $html .= '
        <tr>
          <td>'.date("Y-m-d H:m:s", $data_row->time).'</td>
          <td style="word-wrap: break-word;">'.$data_row->destination.'</td>
        </tr>
        <tr>
          <td rowspan="3">Details</td>
          <td style="word-wrap: break-word;">Source: '.$data_row->source.'</td>
        </tr>
        <tr>
          <td>Amount: '.$data_row->quantity.'</td>
        </tr>
        <tr>
          <td>Block: '.$data_row->block.'</td>        
        </tr>
      ';
    }
    $html .= '</table>';
  
  } elseif (isset($atts['info'])){
    $asset = $atts['info'];
    $api_url = 'https://counterpartychain.io/api/asset/'.$asset;
    $data = cp_get_url_data_curl($api_url);
    $data_decoded = json_decode($data);

    $html = '';
    $html .= '<h2>'.mb_strtoupper($asset).' Asset Information</h2>';
    $html .= '<table style="font-size: large;">';
    $html .= '<tr><th>Asset name</th><td>'.$data_decoded->asset.'</td></tr>';
    $html .= '<tr><th>Asset supply</th><td>'.number_format($data_decoded->supply,2).'</td></tr>';
    $html .= '<tr><th>Supply locked</th><td>'.str_ireplace(array('1', '0'), array('yes', 'no'), $data_decoded->locked).'</td></tr>';
    $html .= '<tr><th>Asset divisible</th><td>'.str_ireplace(array('1', '0'), array('yes', 'no'), $data_decoded->divisible).'</td></tr>';
    $html .= '<tr><th>Number of holders</th><td>'.$data_decoded->holders.'</td></tr>';
    $html .= '<tr><th>Number of transactions</th><td>'.$data_decoded->transactions.'</td></tr>';
    
    $html .= '</table>';
  
  } else {
    $html .= 'Error: No action is set!';
  }
  
  $html .= cp_get_plugin_credit();
  
	return $html;
}