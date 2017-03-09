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

    		$fromdate = date('Y-m-d', strtotime( '-30 days' ) );
    		$todate   = date('Y-m-d', strtotime( '+1 days' ));
    		$externalurloption = '';
    		$reportaction   = 'generatereport';

    	    if(isset($_POST['fromdate']) && isset($_POST['todate'])){
                $fromdate 			= isset($_POST['fromdate']) ? $_POST['fromdate'] : $fromdate;
    			$todate   			= isset($_POST['todate']) ? $_POST['todate'] : $todate;   
    			$reportaction   	= isset($_POST['reportaction']) ? $_POST['reportaction'] : 'generatereport';  
    			$externalurloption  = isset($_POST['externalurloption']) ? $_POST['externalurloption'] : '';  

    	    }


    	    if( $reportaction == 'generatereport'){ 
	    			$report_results = oopmvc_generate_report($fromdate, $todate, $reportaction, $externalurloption);  
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

                &nbsp;&nbsp;Report for External Page :  <select name="externalurloption" style="max-width:300px;">
			    	<option value="" > All External Link </option> 

                <?php  
				global $wpdb;
				$table_name = $wpdb->prefix.'clicktrack_report';

 	 			$externalurls  = $wpdb->get_results("SELECT externalurl FROM ".$table_name." group by externalurl order by externalurl ASC;");
                foreach($externalurls as $row){ 

								   	?>
			   
			    	<option value="<?php echo $row->externalurl;?>" <?php echo $row->externalurl == $externalurloption ? 'selected' : '';?>>  <?php echo $row->externalurl;?> </option> 
			    <?php } ?>

			    </select>
			    <input type="submit" value="Submit" name="submit" style="border: solid 1px #ffffff;border-radius: 9px;moz-border-radius: 3px;font-size: 20px;line-height: 32px;color: #ffffff;padding: 1px 17px 3px;background-color: #0a82c7;"> 

			</form> <br /> <hr > <br />
 
			<div id="reportresults"><?php 

			if($externalurloption != '' && $reportaction == 'generatereport') echo '<h3> Report For Page : '. $externalurloption. '</h3> '; 

			if( $reportaction == 'generatereport'){  
					echo $report_results; 
			}else{ $filename = date('Y-m-d h:i:s').'_report.csv' ; ?>

			<script type="text/javascript">
                 jQuery(document).ready(function($) {
                 	    jQuery.ajax({ 
                 	   		method: "POST",
							url: '<?php echo admin_url( 'admin-ajax.php' );?>',
							data: {action: 'downloadreportcsv', fromdate: '<?php echo $fromdate;?>', todate: '<?php echo $todate;?>', reportaction : 'downloadreport', externalurloption: '<?php echo $externalurloption;?>'}
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
	$externalurloption   	= isset($_POST['externalurloption'] ) 	? $_POST['externalurloption'] : ''; 



    if( $reportaction == 'downloadreport'){ 
	    			$report_results = oopmvc_generate_report($fromdate, $todate, $reportaction, $externalurloption);  
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


 


function oopmvc_generate_report($fromdate, $todate , $reportaction =  'generatereport', $externalurloption = ''){
				
				global $wpdb;
				$table_name = $wpdb->prefix.'clicktrack_report';

				if($externalurloption  != ''){
					$reports  = $wpdb->get_results("SELECT  postname, posturl, externalurl, count(id) as count_total   FROM ".$table_name." WHERE `time` between '".$fromdate."' and '".$todate."' and  externalurl = '".$externalurloption."' group by postname order by time DESC;");
				}else{ 
					$reports  = $wpdb->get_results("SELECT  postname, posturl, externalurl, count(id) as count_total   FROM ".$table_name." WHERE `time` between '".$fromdate."' and '".$todate."'  group by externalurl, postname order by time DESC;");
				}


				$report_results = '';
				if($reportaction == 'generatereport'){
					$report_results .=  '<table class="widefat fixed striped" cellspacing="0">
					    <thead>
					    <tr>
					            <th id="posttitlecol" class="manage-column column-post-title" scope="col">Post Title</th>
					            <th id="urlcol" class="manage-column column-url" scope="col"> Post URL </th>';
					 if($externalurloption == '')   $report_results .=  '<th id="externalurlcol" class="manage-column column-external" scope="col"> External URL </th>  ';
					 $report_results .=  '<th id="click-count-col" class="manage-column column-click-count" scope="col" style="text-align:center;"> 	Click Count </th> 

					    </tr>
					    </thead> 

					    <tbody id="the-list">';
					}else{ $report_results .=   "post_title, posturl , externalurl, click_count\n"; }



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
					            									<td class="column-columnname">'. $posturl . '</td>';
					            			     if($externalurloption == '') 
					            			     	$report_results .= ' <td class="column-columnname">'. $externalurl . '</td>';
					            			        $report_results .= '<td class="column-columnname" style="text-align:center;">'. $clickcount .'</td></tr>';  
								    	}else{
					                         $report_results .=  $posttitle.",".$posturl.",".$externalurl.",".$clickcount."\n" ;
								    	}

								    }

								    $report_results .= ($reportaction == 'generatereport') ? '</tbody></table>' : '';
 

								    return $report_results;
} 