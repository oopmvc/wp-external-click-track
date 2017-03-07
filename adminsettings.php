<?php  
/**
 * custom option and settings
 */
function oopmvcwp_settings_init()
{
    // register a new setting for "oopmvcwp" page
  register_setting('oopmvcwp', 'oopmvcwp_options'); 

     
	add_settings_section('general_section', 'General Settings', 'general_section_cb', __FILE__); 
	add_settings_field('general_wp_head',  'WP HEAD(  wp_head ) ', 'general_wp_head_cb', __FILE__, 'general_section' ); 
	add_settings_field('general_wp_footer',  'WP FOOTER(  wp_footer ) ', 'general_wp_footer_cb', __FILE__, 'general_section' ); 
 

} 
add_action('admin_init', 'oopmvcwp_settings_init');

function general_section_cb(){
  echo '<hr />';

}

function general_wp_head_cb(){
	
  $options = get_option('oopmvcwp_options'); 
  echo "</td></tr><tr><td colspan='2'><textarea id='general_wp_head' name='oopmvcwp_options[general_wp_head]' rows='7' cols='50' type='textarea' class='large-text code'>{$options['general_wp_head']}</textarea>";

  echo '<small>&lt;script type="text/javascript"><br />
		&nbsp;&nbsp;&nbsp;base_url = "'.get_bloginfo('url').'";<br />
		&nbsp;&nbsp;&nbsp;clicktrackajaxurl  = "'.admin_url( 'admin-ajax.php' ).'";<br />
		&lt;/script></small>';
 
}
function general_wp_footer_cb(){
	
  $options = get_option('oopmvcwp_options'); 
  echo "</td></tr><tr><td colspan='2'><textarea id='general_wp_footer' name='oopmvcwp_options[general_wp_footer]' rows='7' cols='50' type='textarea' class='large-text code'>{$options['general_wp_footer']}</textarea>";

  echo '<small>examples:<br /> <br />
        &lt;script src="https://unpkg.com/axios/dist/axios.min.js">&lt;/script></small>';
 
}

function oopmvcwp_options_page()
{
    // add top level menu page
    add_menu_page(
        'ClickTrack',
        'ClickTrack',
        'manage_options',
        'ClickTrack',
        'oopmvcwp_options_page_html'
    );
}
  
add_action('admin_menu', 'oopmvcwp_options_page'); 


function oopmvcwp_options_page_html()
{
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
  
       settings_errors(); 
 ?>
    	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2 style="border-bottom: 2px solid #666;">Click Track - Settings</h2> 

		<form action="options.php" method="post">
	    <?php
				if ( function_exists('wp_nonce_field') ) 
					wp_nonce_field('oopmvcwp_nonce'); 
		?>
		<?php settings_fields('oopmvcwp'); ?>
		<?php do_settings_sections(__FILE__); ?>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
	</div>


    <?php
}
