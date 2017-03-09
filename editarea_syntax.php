<?php  
function load_oopmvc_wp_admin_style_scripts($hook) {

        // Load only on ?page=mypluginname
          if($hook == 'post.php' || $hook != 'post-new.php') {
               

        wp_enqueue_style( 'oopmvc_admin_css', plugins_url('admin-style.css', __FILE__) );
        //wp_enqueue_script( 'oopmvc_editarea', plugins_url('edit_area/edit_area_full.js', __FILE__) );
        //wp_enqueue_script( 'oopmvc_admin_script', plugins_url('admin-script.js', __FILE__) );
    }else{
    	return;
          
    }
}
add_action( 'admin_enqueue_scripts', 'load_oopmvc_wp_admin_style_scripts' );
    