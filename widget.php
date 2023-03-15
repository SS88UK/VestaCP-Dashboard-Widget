<?php
/*
Plugin Name: VestaCP/myVesta Dashboard Widget
Plugin URI: https://blog.ss88.us/vestacp-dashboard-widget-for-wordpress
Description: Adds a widget to the Dashboard showing your VestaCP/myVesta accounts details. Requires an API key from VestaCP.
Version: 1.4
Author: Steven Sullivan
Author URI: https://blog.ss88.us
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
define('SS88VESTA_VERSION', 1.4);
// Add the Widget to WordPress
function ss88_add_vestacp_widget() { wp_add_dashboard_widget('ss88_vestacp_widget', 'VestaCP', 'ss88_show_vestacp_widget');	}

// Output Widget 
function ss88_show_vestacp_widget() {
	
	// Ouput the style and scripts
	wp_enqueue_style('ss88_vestacp_widget_css', plugins_url('css.css', __FILE__), false, SS88VESTA_VERSION);
	wp_enqueue_script('ss88_vestacp_widget_js', plugins_url('js.js', __FILE__), false, SS88VESTA_VERSION);
	
	// Get Vesta Details
	$Vesta = array();
	$Vesta['URL'] = get_option('ss88_vestacp_widget_vesta_url');
	$Vesta['Username'] = get_option('ss88_vestacp_widget_vesta_username');
	$Vesta['Hash'] = get_option('ss88_vestacp_widget_vesta_hash');
	$Vesta['VerifySSL'] = get_option('ss88_vestacp_widget_vesta_verifyssl');
	
	if($Vesta['URL'] != '' && $Vesta['Username'] != '' && $Vesta['Hash'] != '')
	{
		// Show Vesta stuff if the $Vesta variables are all set.
		$Data = ss88_vestacp_widget_api($Vesta);
		echo ss88_vestacp_widget_showbars($Data, $Vesta);
	}
	else
	{
		// Output form if not Vesta details are set
		echo '<div class="ss88_vw_formdiv"><p>You have not yet set your Vesta details. Please set these first.</p>';
		echo ss88_vestacp_widget_showform();
	}
	
	// Show Steven Sullivan Ltd Copy
	echo '<ul class="ss88_vw_keys k2"><li class="red"><a href="https://paypal.me/SS88/3" target="_blank">Beers Please!</a></li><li class="violet" style="background-color:#ff0079;"><a href="https://blog.ss88.us/" target="_blank">SS88 LLC</a></li></ul><div class="ss88_spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>';
}


// Call VestaCP
function ss88_vestacp_widget_api($Vesta)
{
	$postvars = array(
		'hash' => $Vesta['Hash'],
		'cmd' => 'v-list-user',
		'arg1' => $Vesta['Username'],
		'arg2' => 'json'
	);
	
	$answer = wp_remote_post($Vesta['URL'] . '/api/', array('sslverify'=>$Vesta['VerifySSL'], 'body'=>$postvars));
	
	// Return Error
	if (is_wp_error($answer))
		return 'error';

	// Parse JSON output
	$data = json_decode($answer['body'], true);

	return $data;
}

add_action('wp_dashboard_setup', 'ss88_add_vestacp_widget');







function ss88_vestacp_widget_ajax()
{
	if(! wp_verify_nonce($_POST['nonce'], 'ss88_vestacp_widget_ajax' )) die ('busted');
	
	// Get Vesta Details
	$Vesta = array();
	$Vesta['URL'] = sanitize_text_field($_POST['v_url']);
	$Vesta['Username'] = sanitize_text_field($_POST['v_username']);
	$Vesta['Hash'] = sanitize_text_field($_POST['v_hash']);
	$Vesta['VerifySSL'] = sanitize_text_field($_POST['v_verifyssl']);
	
	$Data = ss88_vestacp_widget_api($Vesta);
	
	if(is_array($Data))
	{
		// Set Options
		update_option('ss88_vestacp_widget_vesta_url', $Vesta['URL']);
		update_option('ss88_vestacp_widget_vesta_username', $Vesta['Username']);
		update_option('ss88_vestacp_widget_vesta_hash', $Vesta['Hash']);
		update_option('ss88_vestacp_widget_vesta_verifyssl', $Vesta['VerifySSL']);
	
		echo ss88_vestacp_widget_showbars($Data, $Vesta);
	}
	else
	{
		echo '<p style="color:red;">Sorry, either the login is incorrect, or we could not connect to the Vesta Control Panel. Please try again.</p>';
		echo ss88_vestacp_widget_showform();
	}
	
	wp_die();
}

add_action('wp_ajax_ss88_vestacp_widget_ajax', 'ss88_vestacp_widget_ajax');



function ss88_vestacp_widget_showbars($Data, $Vesta)
{
	
	if($Data=='error' || empty($Data)) return 'There was an error communicating with the VestaCP server.';
	$Data = $Data[$Vesta['Username']];
	
	if($Data['U_DISK']!=0 && $Data['DISK_QUOTA']!=0) {
		
		$DiskPercent = (($Data['U_DISK'] / $Data['DISK_QUOTA']) * 100);
		$DiskPercent = ($DiskPercent>100) ? 100 : $DiskPercent;
		
	}
	
	$DiskPercent = ($Data['DISK_QUOTA']=='unlimited') ? 'U' : 0;
	
	$BWPercent = (($Data['U_BANDWIDTH'] / $Data['BANDWIDTH']) * 100);
	$BWPercent = ($BWPercent>100) ? 100 : $BWPercent;
	$BWPercent = ($Data['BANDWIDTH']=='unlimited') ? 'U' : $BWPercent;
	
	$DBPercent = (($Data['U_DATABASES'] / $Data['DATABASES']) * 100);
	$DBPercent = ($DBPercent>100) ? 100 : $DBPercent;
	$DBPercent = ($Data['DATABASES']=='unlimited') ? 'U' : $DBPercent;
	
	$WDPercent = (($Data['U_WEB_DOMAINS'] / $Data['WEB_DOMAINS']) * 100);
	$WDPercent = ($WDPercent>100) ? 100 : $WDPercent;
	$WDPercent = ($Data['WEB_DOMAINS']=='unlimited') ? 'U' : $WDPercent;
	
	$MPercent = (($Data['U_MAIL_ACCOUNTS'] / $Data['MAIL_ACCOUNTS']) * 100);
	$MPercent = ($MPercent>100) ? 100 : $MPercent;
	$MPercent = ($Data['MAIL_ACCOUNTS']=='unlimited') ? 'U' : $MPercent;
	
	
	return '<ul class="ss88_vw_keys">
		<li class="azure">Disk</li>
		<li class="emerald">Bandwidth</li>
		<li class="violet">Databases</li>
		<li class="yellow">Domains</li>
		<li class="red">Mail Accounts</li>
		</ul>

	<div class="bar-main-container azure ss88_vw_disk" tooltip="'.ss88_add_vestacp_widget_formatBytes($Data['U_DISK']).' / '.ss88_add_vestacp_widget_formatBytes($Data['DISK_QUOTA']).'"><div class="bar-wrap"><div class="bar-percentage" data-percentage="'.$DiskPercent.'"></div><div class="bar-container"><div class="bar"></div></div></div></div>
	<div class="bar-main-container emerald ss88_vw_bw" tooltip="'.ss88_add_vestacp_widget_formatBytes($Data['U_BANDWIDTH']).' / '.ss88_add_vestacp_widget_formatBytes($Data['BANDWIDTH']).'"><div class="bar-wrap"><div class="bar-percentage" data-percentage="'.$BWPercent.'"></div><div class="bar-container"><div class="bar"></div></div></div></div>
	<div class="bar-main-container violet ss88_vw_bw" tooltip="'.($Data['U_DATABASES']).' / '.($Data['DATABASES']).'"><div class="bar-wrap"><div class="bar-percentage" data-percentage="'.$DBPercent.'"></div><div class="bar-container"><div class="bar"></div></div></div></div>
	<div class="bar-main-container yellow ss88_vw_bw" tooltip="'.($Data['U_WEB_DOMAINS']).' / '.($Data['WEB_DOMAINS']).'"><div class="bar-wrap"><div class="bar-percentage" data-percentage="'.$WDPercent.'"></div><div class="bar-container"><div class="bar"></div></div></div></div>
	<div class="bar-main-container red ss88_vw_bw" tooltip="'.($Data['U_MAIL_ACCOUNTS']).' / '.($Data['MAIL_ACCOUNTS']).'"><div class="bar-wrap"><div class="bar-percentage" data-percentage="'.$MPercent.'"></div><div class="bar-container"><div class="bar"></div></div></div></div>
	
	';
}

function ss88_vestacp_widget_showform()
{
	$nonce = wp_create_nonce('ss88_vestacp_widget_ajax');
	
	return '<form autocomplete="off" action="'.admin_url( 'admin-ajax.php') . '" method="post" class="ss88_vw_form">
	
	<input type="text" name="ss88_vestacp_widget_vesta_url" autocomplete="off" placeholder="Vesta URL e.g. https://hostname.com:8083" required />
	<input type="text" name="ss88_vestacp_widget_vesta_username" autocomplete="off" placeholder="Username of account to fetch" required />
	<input type="text" name="ss88_vestacp_widget_vesta_hash" autocomplete="off" placeholder="API key" required />
	<p style="font-size:11px;">To generate an API key, first login to the server as root and run the command <b>/usr/local/vesta/bin/v-generate-api-key</b></p>
    <input type="checkbox" name="ss88_vestacp_widget_vesta_verifyssl" value="Y" style="width:auto;display:inline-block;margin-bottom: 0;" checked /> Verify SSL?
    <p style="font-size:11px;">If you un-check this option, then VestaCP\'s SSL certificate will not be verified. You should only un-check this option if your VestaCP control panel uses a self-signed or invalid certificate that is controlled by you.<b>Do so at your own risk</b>.</p>
	<input type="submit" value="Save Details" class="btn" />
	<input type="hidden" name="ss88_vestacp_widget_nonce" value="'.$nonce.'" />
	</form></div>
	
	<script> jQuery(document).ready(function(){ hookSubmitform(); }); </script>';
}



























function ss88_add_vestacp_widget_formatBytes($mb) { 
	
	$r = intval($mb) / 1024;

    return round($r, 2) . 'GB'; 
} 

function ss88_add_vestacp_widget_remove()
{
	delete_option('ss88_vestacp_widget_vesta_url');
	delete_option('ss88_vestacp_widget_vesta_username');
	delete_option('ss88_vestacp_widget_vesta_hash');
	delete_option('ss88_vestacp_widget_vesta_verifyssl');
}

register_uninstall_hook( __FILE__, 'ss88_add_vestacp_widget_remove' );

?>
