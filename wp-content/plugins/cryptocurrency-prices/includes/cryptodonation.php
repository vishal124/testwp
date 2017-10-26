<?php
function cp_cryptodonation_shortcode( $atts ) {
  if (isset($atts['address']) and $atts['address']!=''){
    $donation_address = $atts['address'];
    
    $html = '
      <p>
        <strong>To donate, scan the QR code or copy and paste the bitcoin wallet address:</strong> <br /><br />
        <span style="font-size: big;">'.$donation_address.'</span><br /><br />
        <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=bitcoin:'.urlencode($donation_address).'&choe=UTF-8" /><br /><br />
        <strong>Thank you!</strong>
      </p>
    ';
  }
  
  $html .= cp_get_plugin_credit();
  
  return $html;
}