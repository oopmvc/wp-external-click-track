<?php  

global $ClickTrack_db_version;
global $ClickTrack_tablename;
$ClickTrack_db_version = '1.0.0';


function report_table_install(){
	global $wpdb;
	global $ClickTrack_db_version;


	$installed_version = get_option('ClickTrack_db_version');

	$table_name = $wpdb->prefix.'clicktrack_report';

	$charset_collate = $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
 
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name ." (
				  `id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
				  `time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `postname` tinytext NOT NULL,
				  `postid` MEDIUMINT(9) NOT NULL DEFAULT '0',
				  `posturl` VARCHAR(255) NOT NULL DEFAULT '',
				  `externalurl` VARCHAR(255) NOT NULL DEFAULT '',
				  `userid` MEDIUMINT(9) NOT NULL DEFAULT '0',
				   PRIMARY KEY (`id`)
				)  $charset_collate;
		";

	    
	    dbDelta( $sql );
        update_option('ClickTrack_db_version', $ClickTrack_db_version);
	 
     

}

register_activation_hook(__FILE__, 'report_table_install');

function clicktrack_update_db_check() {
    global $ClickTrack_db_version;
    if ( get_site_option( 'ClickTrack_db_version' ) != $ClickTrack_db_version ) {
        report_table_install();
    }
}
add_action( 'plugins_loaded', 'clicktrack_update_db_check' );


 add_action('admin_menu', 'oopmvc_add_record_menu');


    function oopmvc_add_record_menu() {
	    add_submenu_page('ClickTrack', __('Report','menu-report'), __('Report','menu-report'), 'manage_options', 'reportpage', 'oopmvc_report_cb'); 

    }


    function oopmvc_report_cb() {

    		$fromdate = date('Y-m-d', strtotime( '-7 days' ) );
    		$todate   = date('Y-m-d', strtotime( '+1 days' ));
    		$reportaction   = 'generatereport';

    	    if(isset($_POST['fromdate']) && isset($_POST['todate'])){
                $fromdate 			= $_POST['fromdate'];
    			$todate   			= $_POST['todate'];   
    			$reportaction   	= $_POST['reportaction'];  

    	    }


    	    if( $reportaction == 'generatereport'){ 
	    			$report_results = oopmvc_generate_report($fromdate, $todate, $reportaction);  
	        }


    		echo "<h2>" . __( 'Click Track Report', 'menu-report' ) . "</h2>"; ?>


    		<form id="" name="" action="admin.php?page=reportpage" method="post">
			    From Date:
			    <input type="text" id="fromdate" name="fromdate" value="<?php echo $fromdate;?>"/>
			    To Date: <input type="text" id="todate" name="todate" value="<?php echo $todate;?>"/>
			    <select name="reportaction">
			    	<option value="generatereport" <?php echo $reportaction == 'generatereport' ? 'selected' : '';?>> Make Report </option> 
			    	<option value="downloadreport" <?php echo $reportaction == 'downloadreport' ? 'selected' : '';?>> Download CSV Report </option> 
			    </select>
			    <input type="submit" value="Submit" name="submit"> 

			</form>
 
			<div id="reportresults"><?php 

			if( $reportaction == 'generatereport'){  
					echo $report_results; 
			}else{ $filename = date('Y-m-d h:i:s').'_report.csv' ; ?>

			<script type="text/javascript">
                 jQuery(document).ready(function($) {
                 	    jQuery.ajax({ 
                 	   		method: "POST",
							url: '<?php echo admin_url( 'admin-ajax.php' );?>',
							data: {action: 'downloadreportcsv', fromdate: '<?php echo $fromdate;?>', todate: '<?php echo $todate;?>', reportaction : 'downloadreport'}
						})
						.done(function( response ) {
								var uri = 'data:text/csv;charset=UTF-8,' + encodeURIComponent(response);
								var downloadLink = document.createElement("a");
								downloadLink.href = uri;
								downloadLink.download = "<?php echo $filename;?>";
								document.body.appendChild(downloadLink);
							    downloadLink.click();
							    document.body.removeChild(downloadLink);  
						});



                 });		
			</script>

		<?php } ?></div> 
		    
			<script type="text/javascript">
			    jQuery(document).ready(function() {
			        jQuery('#fromdate').datepicker({
			            dateFormat : 'yy-mm-dd'
			        });

			        jQuery('#todate').datepicker({
			            dateFormat : 'yy-mm-dd'
			        });
			    });
			</script>  

		 


<?php  

}


add_action( 'wp_ajax_downloadreportcsv', 'prefix_downloadreportcsv' ); 
add_action( 'wp_ajax_nopriv_downloadreportcsv', 'prefix_downloadreportcsv' ); 
function prefix_downloadreportcsv() {
     
 
	$fromdate 			= isset($_POST['fromdate'] ) 		? $_POST['fromdate'] : date('Y-m-d', strtotime( '-7 days' ) );
	$todate   			= isset($_POST['todate'] ) 			? $_POST['todate'] : date('Y-m-d');   
	$reportaction   	= isset($_POST['reportaction'] ) 	? $_POST['reportaction'] : 'downloadreport'; 



    if( $reportaction == 'downloadreport'){ 
	    			$report_results = oopmvc_generate_report($fromdate, $todate, $reportaction);  
	    			echo $report_results;
 	}

    wp_die();
}


 
add_action( 'admin_enqueue_scripts', 'enqueue_date_picker' ); 
  
function enqueue_date_picker(){
        wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		 

}


 


function oopmvc_generate_report($fromdate, $todate , $reportaction =  'generatereport'){
				
				global $wpdb;
				$table_name = $wpdb->prefix.'clicktrack_report';

 	 			$reports  = $wpdb->get_results("SELECT  postname, posturl, externalurl, count(id) as count_total   FROM ".$table_name." WHERE `time` between '".$fromdate."' and '".$todate."'  group by externalurl, postname order by time DESC;");

				$report_results = ($reportaction == 'generatereport') ? '<table class="widefat fixed striped" cellspacing="0">
					    <thead>
					    <tr>
					            <th id="posttitlecol" class="manage-column column-post-title" scope="col">Post Title</th>
					            <th id="urlcol" class="manage-column column-url" scope="col"> Internal URL </th>  
					            <th id="externalurlcol" class="manage-column column-external" scope="col"> External URL </th>  
					            <th id="click-count-col" class="manage-column column-click-count" scope="col" style="text-align:center;"> 	Click Count </th> 

					    </tr>
					    </thead> 

					    <tbody id="the-list">' : "post_title, posturl , externalurl, click_count\n";

					    $i=0;
 

								   foreach($reports as $report){ 
												$posttitle 	 = $report->postname;
								     			$posturl     = $report->posturl;
								     			$externalurl = $report->externalurl; 
								     			$clickcount  = $report->count_total; 

										if( $reportaction == 'generatereport'){  

											   
											    $trclass = ($i++ % 2 == 0) ? 'alternate' : '';


								        		$report_results .= '<tr class="'.$trclass.'">
					            						            <td class="column-columnname"><a href='.$posturl.' target="_blank">'. $posttitle.  '</a></td>
					            									<td class="column-columnname">'. $posturl . '</td>
					            									<td class="column-columnname">'. $externalurl . '</td>
					            									<td class="column-columnname" style="text-align:center;">'. $clickcount .'</td></tr>'; 
								    	}else{
					                         $report_results .=  $posttitle.",".$posturl.",".$externalurl.",".$clickcount."\n" ;
								    	}

								    }

								    $report_results .= ($reportaction == 'generatereport') ? '</tbody></table>' : '';
 

								    return $report_results;
} 