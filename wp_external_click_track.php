<?php
/**
* Plugin Name: Wordpress External Link Click Tracking 
* Plugin URI: http://www.oopmvc.com/portfolios/wordpress/plugins/oopmvc-wp
* Description: Wordpress External Link Click Tracking with Report 
* Version: 1.0 
* Author: Shahinul Islam
* Author URI: http://www.oopmvc.com/team/shahinul-islam
* License: MIT 
*/

include dirname(__FILE__).'/adminsettings.php';
include dirname(__FILE__).'/hidedefault.php';  
include dirname(__FILE__).'/editarea_syntax.php'; 
include dirname(__FILE__).'/report.php';
include dirname(__FILE__).'/clicktrack.php';

$oopmvc_all_js_scripts = array();

// adding wp_head settings in front end 
function oopmvc_frontend_head(){ 
global $wp_query;  
	$options = get_option('oopmvcwp_options');  echo $options['general_wp_head'];  
	$clicktrackpostid = 0;
	$clicktrackposturl = $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$clicktrackposttitle = wp_title(":", false);




 
		    if ( is_page()  || is_single() ) {
		    	$clicktrackpostid 		= get_the_ID();
				$clicktrackposttitle 	= get_the_title($clicktrackpostid);
				$clicktrackposturl 		= get_permalink($clicktrackpostid);
		    } 

		    if( is_category() ) {
		            $cat = $wp_query->get_queried_object();
		            
		            if ( isset( $cat->term_id ) ) {
		                
		                $clicktrackpostid 		= $cat->term_id;
						$clicktrackposttitle 	= get_cat_name($clicktrackpostid);
						$clicktrackposturl 		= get_category_link($clicktrackpostid);
					}

		   }

		   if( is_tag() ) {
		            $tag = $wp_query->get_queried_object();
		             
		            if ( isset( $tag->term_id ) ) {
		                   
		                    $clicktrackpostid 		= $tag->term_id; 
		                    $clicktrackposttitle 	= $tag->name;
						    $clicktrackposturl 		= get_tag_link($clicktrackpostid);
							 
		            }
		   }
		   
		   if( is_tax() ) {
		            $term = $wp_query->get_queried_object();
		             
		            if ( isset( $term->term_id ) ) {

		            		$clicktrackpostid 		= $term->term_id; 
		                    $clicktrackposttitle 	= $term->name;
						    $clicktrackposturl 		= get_term_link($clicktrackpostid);
		                
		            }
		        }
		      

 
    ?><script type="text/javascript">
    	var clicktrackposttitle = '<?php echo $clicktrackposttitle;?>';
		var clicktrackpostid 	= '<?php echo $clicktrackpostid;?>';
		var clicktrackposturl 	= '<?php echo $clicktrackposturl;?>';
    </script><?php 
		
}
add_action('wp_head', 'oopmvc_frontend_head');

// adding wp_footer settings in front end 
function oopmvc_frontend_footer(){ 

	/*
		<script src="https://unpkg.com/vue/dist/vue.js"></script>
		<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
	*/

	$options = get_option('oopmvcwp_options');   echo $options['general_wp_footer']; 
	global $oopmvc_all_js_scripts;
	foreach ($oopmvc_all_js_scripts as $key => $value) {
		echo $value;
	}
	?>
	<style>
           .clicktrack-ajax-loader{ 
	           	position: fixed; background: #000; 
	           	opacity: 0.5; z-index: 100; width: 100%; 
	           	height: 100%; text-align: center;
	           	left: 0;
	           	top: 0;
	            visibility: hidden;

	        }
	        .clicktrack-ajax-loader img{ width: 24px; height: 24px; z-index: 1001; position: absolute; top: 50%; left: 50%;}
	</style>
	<div class="clicktrack-ajax-loader">
	  <img src="<?php echo  plugins_url( 'images/loading.gif', __FILE__ ) ;?>" class="img-responsive" />  
	</div>
	<script type="text/javascript"> 
           jQuery(document).ready(function($) {
           	 var comp = new RegExp(location.host); 

				$('a').each(function(){
				   if(comp.test($(this).attr('href'))){
				       // a link that contains the current host           
				       $(this).addClass('local');
				   }
				   else{
				       // a link that does not contain the current host
				       $(this).addClass('external');
				        

				       $(this).on('click', function(){ 
				       		  event.preventDefault();
				       		  this_url = $(this).attr('href'); 

							  $.ajax({
						          method: "POST",
						          url: clicktrackajaxurl,
						          data: {
						            action: 'clicktracksave',
		                          	externalurl: this_url,
		                          	actualpost: clicktrackposttitle,
		                          	actualpostid: clicktrackpostid,
		                          	posturl: clicktrackposturl,
						          }, 
						          beforeSend: function(){
						          	 $('.clicktrack-ajax-loader').css("visibility", "visible");
								  },
						          success : function( response ) {
						          	   $('.clicktrack-ajax-loader').css("visibility", "hidden");
								       console.log( response ); 
						               var win = window.open(this_url, '_blank');
  									   if (win) {
										    //Browser has allowed it to be opened
										    win.focus();
										} else {
										    //Browser has blocked it
										    alert('Please allow popups for this website');
										}
									 

						          },
						          fail : function( response ) {
						              console.log( response ); 
						              $('.clicktrack-ajax-loader').css("visibility", "hidden");
						          }
						      });


				       });

				   }
				});
           });
	</script> 
	<?php 
	
}
add_action('wp_footer', 'oopmvc_frontend_footer'); 

function clicktrack_add_ajax_script() { 
    wp_localize_script( 'clicktrackajax', 'ajax_params', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );  
}
add_action( 'wp_enqueue_scripts', 'clicktrack_add_ajax_script' );