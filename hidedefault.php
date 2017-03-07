<?php 


function oopmvc_hide_default_plugins() {
 
  $current_user = wp_get_current_user();
  // Super admin users emails 
  $developer_emails = array(
  								'oopmvc.website@gmail.com', 
  								'shahinbdboy@gmail.com'
  						    );


  if(!  in_array($current_user->user_email , $developer_emails )){
	  echo '<style>
	   #toplevel_page_edit-post_type-acf,
	   #toplevel_page_cptui_main_menu,
	   #menu-posts-vue_block,
	   a[href="options-general.php?page=codepress-admin-columns"],
       a.cpac-edit add-new-h2,
	   { display: none !important;}
	  </style>';

	}
}
add_action('admin_head', 'oopmvc_hide_default_plugins');
