<?php
class CPAdmin {
  const NONCE = 'cp-admin-settings';

	private static $initiated = false;
	
  public static function init() {
    if ( ! self::$initiated ) {
			self::init_hooks();
		}
  }
  
	public static function init_hooks() {
    self::$initiated = true;
    
    //add admin menu
    add_action('admin_menu', array( 'CPAdmin', 'register_menu_page' ));
	}
  
  public static function register_menu_page() {
    add_menu_page(
      __( 'Cyptocurrency All-in-One', 'cryptocurrency' ),
      __( 'Cyptocurrency', 'cryptocurrency' ),
      'manage_options',
      'cryptocurrency-prices',
      array('CPAdmin', 'cryptocurrency_prices_admin'),
      CP_URL.'images/btc.png',
      81
    );
    
    add_submenu_page( 
      'cryptocurrency-prices', 
      __( 'Help', 'cryptocurrency' ), 
      __( 'Help', 'cryptocurrency' ), 
      'manage_options', 
      'cryptocurrency-prices', 
      array('CPAdmin', 'cryptocurrency_prices_admin_help')
    );
    
    add_submenu_page( 
      'cryptocurrency-prices', 
      __( 'Settings', 'cryptocurrency' ), 
      __( 'Settings', 'cryptocurrency' ), 
      'manage_options', 
      'settings', 
      array('CPAdmin', 'cryptocurrency_prices_admin_settings')
    );
  
    add_submenu_page( 
      'cryptocurrency-prices', 
      __( 'Orders List', 'cryptocurrency' ), 
      __( 'Orders List', 'cryptocurrency' ), 
      'manage_options', 
      'orders-list', 
      array('CPAdmin', 'cryptocurrency_prices_admin_orders_list')
    );
    
    add_submenu_page( 
      'cryptocurrency-prices', 
      __( 'Payment Settings', 'cryptocurrency' ), 
      __( 'Payment Settings', 'cryptocurrency' ), 
      'manage_options', 
      'payment-settings',
      array('CPAdmin', 'cryptocurrency_prices_admin_payment_settings') 
    );
    
    add_submenu_page( 
      'cryptocurrency-prices', 
      __( 'Support', 'cryptocurrency' ), 
      __( 'Support', 'cryptocurrency' ), 
      'manage_options', 
      'support',
      array('CPAdmin', 'cryptocurrency_prices_admin_support') 
    );
  }
  
  public static function cryptocurrency_prices_admin(){
    self::cryptocurrency_prices_admin_help();
  }
  
  public static function cryptocurrency_prices_admin_settings(){
    //check if user has admin capability
    if (current_user_can( 'manage_options' )){ 
      
      if (isset($_POST['cryptocurrency-prices-hide-credit']) and $_POST['cryptocurrency-prices-hide-credit']!=''){
        //check nonce
        check_admin_referer( self::NONCE );
      
        $sanitized_cryptocurrency_prices_hide_credit = (int)$_POST['cryptocurrency-prices-hide-credit'];
        update_option('cryptocurrency-prices-hide-credit', $sanitized_cryptocurrency_prices_hide_credit);
        $admin_message_html = '<div class="notice notice-success"><p>Plugin settings have been updated!</p></div>';
      }
      
      if (isset($_POST['cryptocurrency-prices-file-get-contents']) and $_POST['cryptocurrency-prices-file-get-contents']!=''){
        //check nonce
        check_admin_referer( self::NONCE );
        
        $sanitized_cryptocurrency_prices_file_get_contents = (int)$_POST['cryptocurrency-prices-file-get-contents'];
        update_option('cryptocurrency-prices-file-get-contents', $sanitized_cryptocurrency_prices_file_get_contents);
        $admin_message_html = '<div class="notice notice-success"><p>Plugin settings have been updated!</p></div>';
      }
      
      if (isset($_POST['cryptocurrency-prices-css'])){
        //check nonce
        check_admin_referer( self::NONCE );
        
        $sanitized_cryptocurrency_prices_css = sanitize_text_field($_POST['cryptocurrency-prices-css']);
        update_option('cryptocurrency-prices-css', $sanitized_cryptocurrency_prices_css);
        $admin_message_html = '<div class="notice notice-success"><p>Plugin settings have been updated!</p></div>';
      }    
    
      if (get_option('cryptocurrency-prices-hide-credit') == 1){
        $credit_selected = 'selected="selected"';
      }
      
      if (get_option('cryptocurrency-prices-file-get-contents') == 1){
        $file_get_contents_selected = 'selected="selected"';
      }
      
      echo '
      <div class="wrap cryptocurrency-admin">
        '.$admin_message_html.'
        <h1>Cyptocurrency All-in-One Settings:</h1>
        
        
        <form action="" method="post">
          
          <h2>Compatibility:</h2>
          <p>Activate if the plugin can not load data because of a problem with CURL library.</p>
          <label>Use file_get_contents instead of CURL:</label>
          <select name="cryptocurrency-prices-file-get-contents">
            <option value="0">no</option>
            <option value="1" '.$file_get_contents_selected.'>yes</option>
          </select>
  
          <h2>Custom design:</h2>
          <p>Write your custom CSS code here to style the plugin.</p>
          <textarea name="cryptocurrency-prices-css" rows="5" cols="50">'.get_option('cryptocurrency-prices-css').'</textarea>
  
          <h2>Provide credit to plugin:</h2>
          <p>By providing credit to the plugin you help more people install this plugin and make cryptocurrencies more popular.</p>
          <label>Hide credit on plugin pages: </label>
          <select name="cryptocurrency-prices-hide-credit">
            <option value="0">no</option>
            <option value="1" '.$credit_selected.'>yes</option>
          </select>
          
          <br /><br />
          '.wp_nonce_field( self::NONCE ).'        
          <input type="submit" value="Save options" />
        </form>
      </div>
      ';
    
    }
  }
  
  public static function cryptocurrency_prices_admin_orders_list(){
    global $wpdb;
    $table_name = $wpdb->prefix.'cp_orders';
    
    //check if user has admin capability
    if (current_user_can( 'manage_options' )){ 
    
      $orders_html = '';
      $orders = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC;"); 
      if ($orders){
        $orders_html .= '<table class="wp-list-table widefat fixed">';
        $orders_html .= '
          <tr>
            <th>Date</th>
            <th>Item</th>
            <th>Price</th>
            <th>Payment</th>
          </tr>
        ';
        foreach( $orders as $order_key => $order ) {
          $orders_html .= '
            <tr>
              <td>'.htmlspecialchars($order->time).'</td>
              <td>'.htmlspecialchars($order->item).'</td>
              <td>'.htmlspecialchars($order->price).'</td>
              <td>
                '.htmlspecialchars($order->payment_address).'
                <a href="https://blockchain.info/address/'.htmlspecialchars($order->payment_address).'" target="_blank">Track payment</a>
              </td>
            <tr>
            <tr>
              <td colspan="4">
                Ordered by: '.htmlspecialchars($order->name).' 
                Telephone: '.htmlspecialchars($order->telephone).' 
                Email: '.htmlspecialchars($order->email).' 
                Address: '.htmlspecialchars($order->address).'
                '.$order->description.'
              </td>
            </tr>
          ';
        }
        $orders_html .= '</table>';
      } else {
        //no orders received yet
        $orders_html .= 'There are no payments yet!';
      }
          
      echo '
      <div class="wrap cryptocurrency-admin">
        '.$admin_message_html.'
        <h1>Cyptocurrency All-in-One List of Orders Received:</h1>
        '.$orders_html.'     
      ';
  
    }
  }
  
  public static function cryptocurrency_prices_admin_payment_settings(){
    //check if user has admin capability
    if (current_user_can( 'manage_options' )){ 
          
      if (isset($_POST['cryptocurrency-payment-addresses'])){
        //check nonce
        check_admin_referer( self::NONCE );
        
        $sanitized_cryptocurrency_payment_addresses = sanitize_text_field($_POST['cryptocurrency-payment-addresses']);
        update_option('cryptocurrency-payment-addresses', $sanitized_cryptocurrency_payment_addresses);
        $admin_message_html = '<div class="notice notice-success"><p>Plugin settings have been updated!</p></div>';
      }
      
      if (isset($_POST['cryptocurrency-payment-notifications-email'])){
        //check nonce
        check_admin_referer( self::NONCE );
        
        $sanitized_cryptocurrency_payment_notifications_email = sanitize_text_field($_POST['cryptocurrency-payment-notifications-email']);
        update_option('cryptocurrency-payment-notifications-email', $sanitized_cryptocurrency_payment_notifications_email);
        $admin_message_html = '<div class="notice notice-success"><p>Plugin settings have been updated!</p></div>';
      }    
      
      echo '
      <div class="wrap cryptocurrency-admin">
        '.$admin_message_html.'
        <h1>Cyptocurrency All-in-One Payment Settings:</h1>
        <h2>Set these if you want to receive payments!</h2>
        
        <form action="" method="post">
          <h2>BTC payment addresses:</h2>
          <p>Write 1 BTC address per line (create the addresses in your wallet). The more addresses - the better. Each transaction uses 1 random address from the list.</p>
          <textarea name="cryptocurrency-payment-addresses" rows="10" cols="50">'.get_option('cryptocurrency-payment-addresses').'</textarea>
          
          <h2>Payment notification email:</h2>
          <p>You will receive payment notifications on this email. Leave blank if you do not want enail notifications.</p>
          <input type="text" name="cryptocurrency-payment-notifications-email" value="'.get_option('cryptocurrency-payment-notifications-email').'" />
              
          <br /><br />
          '.wp_nonce_field( self::NONCE ).'        
          <input type="submit" value="Save options" />
        </form>
      </div>
      ';
    
    }
  }
  
  public static function cryptocurrency_prices_admin_support(){
    echo '
    <div class="wrap cryptocurrency-admin">
    <h1>Cyptocurrency All-in-One Support:</h1>
    '; 
    
    echo '
    <h2>Get support:</h2>
    <p>If have troubles running the plugin, please use the support forum: https://wordpress.org/support/plugin/cryptocurrency-prices.</p>
    <p>If you need paid support with customizing the plugin or with plugin development, send me an email at boian_iankov@abv.bg.</p>
    ';
    
    echo '
    <h2>Your donations help</h2>
    <p>Thank you so much for considering supporting my work. If you have benefited from this WordPress plugin, and feel led to send me a donation, please follow the donation options below. I am truly thankful for your hard earned giving.</p>
    '.do_shortcode('[cryptodonation address="1EMA2fGRyX5UuA4azcVjmTkc1Bkpq3UBXP"]').'
    <p>You can also <a href="http://creditstocks.com/donate/" target="_blank">visit our donations page</a>.</p>
    ';
    
    echo ' 
    </div>
    ';
  }
  
  public static function cryptocurrency_prices_admin_help(){
    echo '
    <div class="wrap cryptocurrency-admin">
      <h1>Cyptocurrency All-in-One Help:</h1>
    ';
    
    echo '
      <h2>To display cryptocurrency calculator and exchange rates:</h2>
      <p>To show cryptocurrency prices, add a shortcode to the text of the pages or posts where you want the cryptocurrency prices to apperar. Exapmle shortcodes:</p>
      <pre>
      [currencyprice currency1="btc" currency2="usd,eur,ltc,eth,jpy,gbp,chf,aud,cad,bgn"]
      [currencyprice currency1="ltc" currency2="usd,eur,btc" feature="all"]
      [currencyprice currency1="eth" currency2="usd,btc" feature="prices"]
      [currencyprice currency1="eth" currency2="usd,btc" feature="calculator"]
      </pre>
      <p>You can also call the prices from the theme like this:</p>
      <pre>
      '.htmlspecialchars('<?php echo do_shortcode(\'[currencyprice currency1="btc" currency2="usd,eur"]\'); ?>').'
      </pre>
      <p>Major cryptocurrencies are fully supported with icons: Bitcoin BTC, Ethereum ETH, XRP, DASH, LTC, ETC, XMR, XEM, REP, MAID, PIVX, GNT, DCR, ZEC, STRAT, BCCOIN, FCT, STEEM, WAVES, GAME, DOGE, ROUND, DGD, LISK, SNGLS, ICN, BCN, XLM, BTS, ARDR, 1ST, PPC, NAV, XCP, NXT, LANA. Partial suport for over 1000 cryptocurrencies. Flat currencies conversion supported: AUD, USD, CAD, GBP, EUR, CHF, JPY, CNY.</p>
    ';
  
    echo '
      <h2>To display cryptocurrency candlestick price chart:</h2>
      <p>To show cryptocurrency candlestick chart graphic, add a shortcode to the text of the pages or posts where you want the chart to apperar. Exapmle shortcodes:</p>
      <pre>
      [currencygraph currency1="btc" currency2="usd"]
      [currencygraph currency1="dash" currency2="btc"]
      </pre>
      <p>You can also call the chart from the theme like this:</p>
      <pre>
      '.htmlspecialchars('<?php echo do_shortcode(\'[currencygraph currency1="btc" currency2="usd"]\'); ?>').' 
      </pre>
    ';
  
    echo '
      <h2>To display a list of all cryptocurrencies:</h2>
      <p>Shortcodes:</p>
      <pre>
      [allcurrencies]
      </pre>
      <p>You can also call the list from the theme like this:</p>
      <pre> 
      '.htmlspecialchars('<?php echo do_shortcode(\'[allcurrencies]\'); ?>').'
      </pre>
    ';
  
    echo '
      <h2>To accept orders and bitcoin payments:</h2>
      <p>
        Open the plugin settings and under "Payment settings" fill in your BTC wallet addresses to receive payments and an email for receiving payment notifications.<br /> 
        The plugin does not store your wallet\'s private keys. It uses one of the addresses from the provided list for every payment, by rotating all addresses and starting over from the first one. The different addresses are used to idenfiry if a specific payment has been made. You must provide enough addresses - more than the number of payments you will receive a day.<br /> 
        Add a shortcode to the text of the pages or posts where you want to accept payments (typically these pages would contain a product or service that you are offering). The amount must be in BTC.<br />
        Exapmle shortcodes:
      </p>
      <pre>
      [cryptopayment item="Advertising services" amount="0.01"]
      </pre>
    ';
    
    echo '
      <h2>To accept donations:</h2>
      <p>To accept bitcoin donations on your web site, add a shortcode to the text of the pages or posts where you want to accept donations. Exapmle shortcodes (do not forget to put your bitcoin address):</p>
      <pre>
      [cryptodonation address="1EMA2fGRyX5UuA4azcVjmTkc1Bkpq3UBXP"]
      </pre>
      <p>You can also call the donations from the theme like this:</p>
      <pre>
      '.htmlspecialchars('<?php echo do_shortcode(\'[cryptodonation address="1EMA2fGRyX5UuA4azcVjmTkc1Bkpq3UBXP"]\'); ?>').'
      </pre>
    ';
    
    echo '
      <h2>Counterparty asset explorer:</h2>
      <p>The counterparty asset explorer supports all cointerparty assets, such as IFIT. Add a shortcode to the text of the pages or posts where you want the information. Exapmle shortcodes:</p>
      <pre>
      [xcpasset info="ifit"]
      [xcpasset transactions="ifit"]
      [xcpasset holders="ifit"]
      </pre>
      <p>You can also call the explorer from the theme like this:</p>
      <pre> 
      '.htmlspecialchars('<?php echo do_shortcode(\'[xcpasset info="ifit"]\'); ?>').'
      </pre>
    ';
    
    echo '
      <h2>Instructions to use the plugin in a widget:</h2>
      <p>To use the plugin in a widget, use the provided "CP Shortcode Widget" and put the shortcode in the "Content" section, for example:</p>
      <pre>
      [currencyprice currency1="btc" currency2="usd,eur"]
      </pre>
    ';
    
    echo '    
    </div>
    ';
  }
}