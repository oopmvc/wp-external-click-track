<?php 

function clicktrackSave_cb(){
  
      global $wpdb;  
      $table_name = $wpdb->prefix . "clicktrack_report"; //try not using Uppercase letters or blank spaces when naming db tables
 
      $postname 		= isset($_POST['actualpost']) ? $_POST['actualpost'] : '';
      $postid 			= isset($_POST['actualpostid']) ? $_POST['actualpostid'] : 0;
      $posturl 			= isset($_POST['posturl']) ? $_POST['posturl'] : '';
      $externalurl 		= isset($_POST['externalurl']) ? $_POST['externalurl'] : '';
      $user_id  		= get_current_user_id();

      if(strlen($externalurl) > 3){ 

          $wpdb->insert($table_name, array(
                                    'time' 			=> date('Y-m-d h:i:s'),   
                                    'postname' 		=> $postname,
                                    'postid' 		=> $postid,
                                    'posturl' 		=> $posturl,
                                    'externalurl' 	=> $externalurl,
                                    'userid' 	=> $user_id
                                    )  
            );
        }
        echo 'Click Record Has been Recoded';
        
  wp_die();
}

add_action('wp_ajax_clicktracksave', 'clicktrackSave_cb');
add_action('wp_ajax_nopriv_clicktracksave', 'clicktrackSave_cb');