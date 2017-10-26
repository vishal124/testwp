<?php
function cp_currencygraph_shortcode( $atts ) {
  global $cp_loader_js_loaded;

  if (isset($atts['currency1']) and $atts['currency1']!=''){
    $currency1 = $atts['currency1'];
    
    if (isset($atts['currency2']) and $atts['currency2']!=''){
      $currency2 = $atts['currency2'];
    } else {
      $currency2 = array('btc');
    }

    //generate random chart id
    $chart_id = rand(1000,9999);

    //check if library is loaded
    if (!$cp_loader_js_loaded){   
      
      //load javascript library
      $html .= '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
      
      //load javascript functions
      $html .= '
      <script type="text/javascript">
        function setCandlestickPeriod(candlestickChartDataOptions, period){
          if (period == "1hour"){
            candlestickChartDataOptions.group_by = "minute";
            candlestickChartDataOptions.data_points = 20;
            candlestickChartDataOptions.aggregate = 3;
          } else if (period == "24hours"){
            candlestickChartDataOptions.group_by = "hour";
            candlestickChartDataOptions.data_points = 24;
            candlestickChartDataOptions.aggregate = 1;
          } else if  (period == "30days"){
            candlestickChartDataOptions.group_by = "day";
            candlestickChartDataOptions.data_points = 30;
            candlestickChartDataOptions.aggregate = 1;        
          } else if  (period == "1year"){
            candlestickChartDataOptions.group_by = "day";
            candlestickChartDataOptions.data_points = 73;
            candlestickChartDataOptions.aggregate = 5;        
          }
        }
        
        function candlestickLoadData(candlestickChartDataOptions, chart_id){ 
          var candlestickDataUrl = "https://min-api.cryptocompare.com/data/"+
            "histo"+candlestickChartDataOptions.group_by+
            "?fsym="+candlestickChartDataOptions.currency1+
            "&tsym="+candlestickChartDataOptions.currency2+
            "&limit="+candlestickChartDataOptions.data_points+
            "&aggregate="+candlestickChartDataOptions.aggregate+
            "&e=CCCAGG";
          jQuery.get(candlestickDataUrl, function( rawData ) {
            console.log("Data loaded");
            
            //reset any old data
            var candlestickChartData = [];
            rawData.Data.forEach(function(rawDataSingle) {
              var singleDateTime = convertCandlestickTime(candlestickChartDataOptions, rawDataSingle.time);
              candlestickChartData.push([singleDateTime, rawDataSingle.low, rawDataSingle.open, rawDataSingle.close, rawDataSingle.high]);
            });
          
            google.charts.load("current", {"packages":["corechart"]});
            google.charts.setOnLoadCallback( function(){drawChart(candlestickChartDataOptions, candlestickChartData, chart_id);} );
          });
        }
        
        function drawChart(candlestickChartDataOptions, candlestickChartData, chart_id) {
          var data = google.visualization.arrayToDataTable(candlestickChartData, true);
          var options = {
            legend:"none",
            title:candlestickChartDataOptions.currency1+" price in "+candlestickChartDataOptions.currency2,
            bar: { groupWidth: "70%" }, // sets space between bars
            candlestick: {
              fallingColor: { strokeWidth: 0, fill: "#a52714" }, // red
              risingColor: { strokeWidth: 0, fill: "#0f9d58" }   // green
            }
          };
          var chart = new google.visualization.CandlestickChart(document.getElementById(chart_id));
          
          chart.draw(data, options);
        }
        
        function convertCandlestickTime(candlestickChartDataOptions, UNIX_timestamp){
          var a = new Date(UNIX_timestamp * 1000);
          var year = a.getFullYear();
          var month = dateFormatNumber(a.getMonth()+1, 2);
          var date = dateFormatNumber(a.getDate(), 2);
          var hour = dateFormatNumber(a.getHours(), 2);
          var min = dateFormatNumber(a.getMinutes(), 2);
          var sec = dateFormatNumber(a.getSeconds(), 2);
  
          if (candlestickChartDataOptions.group_by == "minute"){
            var time = hour+":"+min;
          } else if (candlestickChartDataOptions.group_by == "hour"){
            var time = hour+":"+min+" "+date+"."+month;
          } else {
            var time = date+"."+month+"."+year;
          }
          
          return time;
        }
        
        function dateFormatNumber(n, p, c) {
          var pad_char = typeof c !== "undefined" ? c : "0";
          var pad = new Array(1 + p).join(pad_char);
          return (pad + n).slice(-pad.length);
        }
      </script>      
      ';
      
      //set flag - library is loaded
      $cp_loader_js_loaded = 1;
    }
    
    //generate javascript for the graphic      
    $html .= '
      <script type="text/javascript">
        candlestickChartDataOptions_'.$chart_id.' = {
          currency1 : "'.mb_strtoupper($currency1).'", 
          currency2 : "'.mb_strtoupper($currency2).'", 
          group_by: "day", 
          data_points: 30, 
          aggregate: 1
        };
        setCandlestickPeriod(candlestickChartDataOptions_'.$chart_id.', "30days");
        candlestickLoadData(candlestickChartDataOptions_'.$chart_id.', "'.$chart_id.'");
        
        jQuery( document ).ready(function() {
          jQuery( "select#chart_period_'.$chart_id.'" ).change(function() {
            setCandlestickPeriod(candlestickChartDataOptions_'.$chart_id.', jQuery(this).val());
            candlestickLoadData(candlestickChartDataOptions_'.$chart_id.', "'.$chart_id.'");
          });
        });
      </script>
    ';

    //generate html for the graphic    
    $html .= '
      <div class="chart_wrap">
        <div class="chart_options">
          <form>
            <label>Select interval:</label>
            <select name="chart_period" id="chart_period_'.$chart_id.'">
              <option value="1hour">1 hour</option>
              <option value="24hours">24 hours</option>
              <option value="30days" selected="selected">30 days</option>
              <option value="1year">1 year</option>
            </select>
          </form>
        </div>
        <div id="'.$chart_id.'"></div>
      </div>
    ';
    
    //discard old data
    unset($data_json);
  } else {
    $html .= 'Error: No currency is set!';
  }
  
  $html .= cp_get_plugin_credit('cryptocompare');
  
	return $html;
}