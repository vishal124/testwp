<?php
function cp_cryptopayment_shortcode( $atts ) {
  global $wpdb;
  $table_name = $wpdb->prefix.'cp_orders';
  
  if (isset($atts['amount']) and $atts['amount']!=''){
    $amount = (float)$atts['amount'];
    $currency = 'BTC';
    $item = htmlspecialchars($atts['item']);
    
    $html = '<div class="cp_payment">';
    
    if (isset($_POST['cp_name']) and $_POST['cp_name']!=''){
      //ready to accept payment
      
      $payment_address = htmlspecialchars($_POST['cp_payment_address']);
      
      echo $_POST['cp_item'].$_POST['cp_amount'].$_POST['cp_name'].$_POST['cp_email'].$_POST['cp_address'];
      
      //record the payment in the database 
      $insert_result = $wpdb->insert($table_name, array(
          'item' => $_POST['cp_item'],
          'price' => $_POST['cp_amount'],
          'currency' => $currency,
          'payment_address' => $_POST['cp_payment_address'],
          'name' => $_POST['cp_name'],
          'email' => $_POST['cp_email'],
          'address' => $_POST['cp_address'],
          'telephone' => $_POST['cp_telephone'],
          'description' => '',
      ));
      
      //send notification to the administrator
      $to = get_option('cryptocurrency-payment-notifications-email');
      $subject = 'Pending cryptocurrency payment';
      $body = 'A user has submitted an order on '.get_site_url().'. Visit the admin panel for more details about the order and the payment.';
      $headers = array('Content-Type: text/html; charset=UTF-8');
      wp_mail( $to, $subject, $body, $headers );
      
      //payment address
      $html .= '
        <h2>Order submitted. Please make a payment:</h2>
        <strong>To pay '.$amount.' '.$currency.', scan the QR code or copy and paste the '.$currency.' wallet address:</strong> <br /><br />
        <span style="font-size: big;">'.$payment_address.'</span><br /><br />
        <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=bitcoin:'.urlencode($payment_address).'&choe=UTF-8" /><br /><br />
      ';
    } else {
      //get the list of payment addresses
      $payment_addresses = get_option('cryptocurrency-payment-addresses');
      $payment_addresses_arr = explode(" ", $payment_addresses);

      //get the last used payment address
      $last_payment_information = $wpdb->get_row(
        'SELECT payment_address FROM '.$table_name.' ORDER BY id DESC LIMIT 1'
      );
      
      if ($last_payment_information){
        //last payment address found
        //use the next address, or go back to the first in the list
        
        $last_payment_address = $last_payment_information->payment_address;
        $last_payment_address_index = array_search($last_payment_address, $payment_addresses_arr);

        //use the next address from the list
        $new_payment_address_index = $last_payment_address_index + 1;
        if ($new_payment_address_index >= count($payment_addresses_arr)){
          //this is the last address - go back to the first one
          $new_payment_address_index = 0;
        }
        
        //$payment_address_randkey = array_rand($payment_addresses_arr);
        $payment_address = trim($payment_addresses_arr[$new_payment_address_index]);
      } else {
        //no information about the last payment address
        //use the first from the list
        $payment_address = trim($payment_addresses_arr[0]);
      }
      
      //payment details form
      $html .= '
        <h2>Please enter order details:</h2>
        <form action="" method="post">
          <table border="0" class="cp_payment_form_table">
            <tr>
              <td><label>Item name:</label></td>
              <td>
                <input type="hidden" name="cp_payment_address" value="'.$payment_address.'" />
                <input type="text" name="cp_item" value="'.$item.'" readonly />
              </td>
            </tr>
            <tr>
              <td><label>Order amount:</label></td>
              <td>
                <input type="text" name="cp_amount" value="'.$amount.' '.$currency.'" readonly />
              </td>
            </tr>
            <tr>
              <td><label>Name:</label></td>
              <td><input type="text" name="cp_name" required /></td>
            </tr>
            <tr>
              <td><label>Email:</label></td>
              <td><input type="text" name="cp_email" required /></td>
            </tr>
            <tr>
              <td><label>Address:</label></td>
              <td><input type="text" name="cp_address" /></td>
            </tr>
            <tr>
              <td><label>Telephone:</label></td>
              <td><input type="text" name="cp_telephone" /></td>
            </tr>
            <tr>
              <td colspan="2"><input type="submit" value="Proceed to payment" /></td>
            </tr>
          </table>
        </form>
      ';
    }
  }
  
  $html .= '</div><!--.cp_payment-->';
  
  $html .= cp_get_plugin_credit();
  
  
  return $html;
}