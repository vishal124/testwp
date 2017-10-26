jQuery(document).ready( function(){         




        //var rml_post_id = jQuery(this).data( 'id' );   
        jQuery.ajax({
        url:  ajax_object.ajax_url,
        type : 'post',
            data : {
                action : 'my_action',
               // post_id : rml_post_id
            },
          success: function(data) {
            jQuery('.site-title').html(data);
            }
        }); 

  
            
    });    




  function getProgress(){
        jQuery.ajax({
            url : ajax_object2.ajax_url,
            type : 'post',
            data : {
                action : 'my_action2',
               // post_id : rml_post_id
            },
            success : function( response ) {
          
                jQuery('.rml_contents').html(response);
                 if(response<10){
                     getProgress();
                }
             }
        });
    } 

      jQuery(document).ready(function(){  
          jQuery('.button').click(function(e){
          getProgress();
       }); 


     }); 