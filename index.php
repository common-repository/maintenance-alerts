<?php
session_start();
	/**
	 * Plugin Name:       Maintenance alerts
	 * Plugin URI:        https://chnsoftwaredevelopers.com/maintenance-alerts
	 * Description:       You can use this plugin to show the website maintenance scheduled information to the visitors of your website or put your site into full maintenance mode.
	 * Version:           1.3.0
	 * Requires at least: 5.2
	 * Requires PHP:      7.2
	 * Author:            Himashana
	 * Author URI:        https://chnsoftwaredevelopers.com
	 * License:           GPL v2 or later
	 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
	 * Text Domain:       Maintenance_alerts
	 * Domain Path:       /languages
	 * 
	 *
 */
 
 /*
    You can use this plugin to show the website maintenance scheduled information to the visitors of your website or put your site into full maintenance mode.
    Copyright (C) 2021-2023  Himashana (Email : Himashana@chnsoftwaredevelopers.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
 
// Include the js scripts that are in the js folder.
wp_enqueue_script( 'actions', plugins_url( '/js/actions.js', __FILE__ ), array(), "1.2");
wp_enqueue_style( 'font-awesome', plugin_dir_url(__FILE__).'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

if(isset($_GET['maintenance_alerts_action'])){
	$GLOBALS['maintenance_alerts_action'] = $_GET['maintenance_alerts_action'];
}else{
	$GLOBALS['maintenance_alerts_action'] = "";
}

//Configure Maintenance alert page template.
function maintenance_alert_template_array(){
	//Maintenance alert template array
	$ma_templates = [];

	//Maintenance alert template - blank
	$ma_templates['Maintenance-Alerts-pt.php'] = 'Maintenance Alerts(blank)';

	//Return all the templates
	return $ma_templates;
}

function maintenance_alert_template_register($page_templates,$theme,$post){
	$plugin_templates = maintenance_alert_template_array();

	foreach($plugin_templates as $templkey=>$templval){
		$p_templates[$templkey] = $templval;
	}

	return $p_templates;
}

add_filter('theme_page_templates', 'maintenance_alert_template_register', 10, 3);

function maintenance_alert_template_select($template){
	global $post, $wp_query, $wpdb;

	$maintenance_alert_template_slug = get_page_template_slug($post->ID);
	$plugin_templates = maintenance_alert_template_array();

	if(isset($plugin_templates[$maintenance_alert_template_slug])){
		$template = plugin_dir_path(__FILE__).'templates/'.$maintenance_alert_template_slug;
	}

	return $template;
}

add_filter('template_include', 'maintenance_alert_template_select', 99);


 //Create the menu
 add_action("admin_menu", "addMenu");
 function addMenu(){
	 add_menu_page( "Maintenance alerts", "Maintenance alerts", 4, "Maintenance-Alerts", "MaintenanceAlertsMenu", "dashicons-warning", 25);
	 add_submenu_page( "Maintenance-Alerts", "Configuration", "Configuration", 4, "Maintenance-Alerts-Configuration", "MaintenanceAlertsMenuConfiguration");
	 add_submenu_page( "Maintenance-Alerts", "Compatibility", "Compatibility", 4, "Maintenance-Alerts-Compatibility", "MaintenanceAlertsMenuCompatibility");
	 add_submenu_page( "Maintenance-Alerts", "About", "About", 4, "Maintenance-Alerts-About", "MaintenanceAlertsMenuAbout");	 
 }
 

 add_action("wp_body_open", "add_alert");
 
 register_activation_hook( __FILE__, 'plugin_activate' );
 register_deactivation_hook( __FILE__, 'plugin_deactivate' );

 add_action('admin_init', 'plugin_redirect');

function plugin_activate() {
    add_option('plugin_do_activation_redirect', true);
}

function plugin_redirect() {
    if (get_option('plugin_do_activation_redirect', false)) {
        delete_option('plugin_do_activation_redirect');
         exit( wp_redirect("admin.php?page=Maintenance-Alerts") );
    }
}

function plugin_deactivate(){
	update_option('first_user_config', '');
}

 //Display Maintenance Alert
 function add_alert(){
	 //If the user select 'Enabled', a maintenance alert/mode will display on the website.
	 $is_show_maintenance = true;
	 
	 //Force disable maintenance alert/mode when user logged in
	 if(get_option('Force_maintenance_when_loggedin') == "Yes"){
		if(is_user_logged_in()){
			$is_show_maintenance = false;
		}
	 }

	 //Display only if enabled and $is_show_maintenance variable set to true.
	 if(get_option('Action_select') == "Enabled" && $is_show_maintenance == true){
		//Display maintenance mode.
		if(get_option('Maintenance_type') == "Maintenance_Mode"){
				include_once dirname( __FILE__ ) . '/maintenance_mode.php';	
		}else{ //Display maintenance alert.
			//If the header text is empty, it will show the default text.
			if(get_option('field') <> ""){
				?> <a href="<?php echo get_option('Onclick_event'); ?>" style="text-decoration:none;"><div style="color:<?php echo get_option('textcolor'); ?>; background-color:<?php echo get_option('backgroundcolor'); ?>; padding:<?php echo get_option('alertPadding'); ?>px;"><center><h4 style="font-size:<?php echo get_option('fontSize'); ?>px; color:<?php echo get_option('textcolor'); ?>"> <?php echo get_option('field'); ?> </h4></center></div></a> <?php
			}else{
				?> <a href="<?php echo get_option('Onclick_event'); ?>" style="text-decoration:none;"><div style="color:<?php echo get_option('textcolor'); ?>; background-color:<?php echo get_option('backgroundcolor'); ?>; padding:<?php echo get_option('alertPadding'); ?>px;"><center><h4 style="font-size:<?php echo get_option('fontSize'); ?>px; color:<?php echo get_option('textcolor'); ?>;"> <?php echo'This site will be unavailable on Wednesday, 26 January 2022 from 6.30AM to 12.00PM due to planned maintenance.'; ?> </h4></center></div></a> <?php
			}
		}
	 }
	 
 }
 
 function alert_register_settings(){
	 register_setting('Maintenance_type_group', 'Maintenance_type');
	 register_setting('option_group', 'field');
	 register_setting('option_group', 'Action_select');
	 register_setting('option_group', 'textcolor');
	 register_setting('option_group', 'backgroundcolor');
	 register_setting('option_group', 'fontSize');
	 register_setting('option_group', 'alertPadding');
	 register_setting('option_group', 'Onclick_event');
	 register_setting('option_group', 'Force_maintenance_when_loggedin');
	 register_setting('option_group', 'custom_maintenance_page');
	 register_setting('Activation_option_group', 'CHN_Account_User');
	 register_setting('configuration', 'first_user_config');
	 register_setting('configuration', 'current_configuration');
	 register_setting('configuration', 'config_restore_sequence');
	 register_setting('Agreement', 'is_License_accepted');
	 register_setting('Agreement', 'is_terms_and_conditions_accepted');
	 register_setting('notifications', 'is_review_done');
 }
 
 add_action('admin_init', 'alert_register_settings');
 
 //If Click Maintenance Alerts Menu, it will show all the settings of the Maintenance Alerts.
 function MaintenanceAlertsMenu(){
	 //Variables
 	 $Message_text_color = "";
 	 $Message_background_color = "";
	 $Message_font_size = "";
	 $Message_alert_padding = "";

	// This variable use to define in which version the Terms and 
	// Conditions and the License agreement need to display again to the user.
	$License_agreement_and_TandC_frompluginversion = "1.3.0";

	 ?>
	
	<h1>Maintenance Alerts</h1><br>
	<?php settings_errors(); ?>

	<!-- License agreement and terms and conditions -->
	<?php
		if(get_option('is_License_accepted') != "true".$License_agreement_and_TandC_frompluginversion && get_option('is_terms_and_conditions_accepted') != "true".$License_agreement_and_TandC_frompluginversion){
			?>
				<div class="wrap top-bar-wrapper" style="background-color:white; padding:10px;">
					<b><h3>License agreement</h3></b>
					<div style="width:700px; padding:3px; border:1px solid gray;">
						You can use this plugin to show the website maintenance scheduled information to the visitors of your website or put your site into full maintenance mode.<br> 
						Copyright (C) 2021-2023  Himashana (Email : Himashana@chnsoftwaredevelopers.com)
						<br><br>
						This program is free software: you can redistribute it and/or modify<br>
						it under the terms of the GNU General Public License as published by<br>
						the Free Software Foundation, either version 2 of the License, or<br>
						any later version.
						<br><br>
						This program is distributed in the hope that it will be useful,<br>
						but WITHOUT ANY WARRANTY; without even the implied warranty of<br>
						MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the<br>
						GNU General Public License for more details.<br>
						<br><br>
						You should have received a copy of the GNU General Public License<br>
						along with this program.  If not, see &lt;https://www.gnu.org/licenses/&gt;.<br>
					</div>
					<form method="post" action="options.php">
						<?php settings_fields('Agreement'); ?>
						<input type="text" name="is_License_accepted" value="true<?php echo $License_agreement_and_TandC_frompluginversion; ?>" style="width:30%; display:none;">
						<?php submit_button('Continue'); ?>
					</form>
				</div>
			<?php
		}elseif(get_option('is_License_accepted') == "true".$License_agreement_and_TandC_frompluginversion && get_option('is_terms_and_conditions_accepted') != "true".$License_agreement_and_TandC_frompluginversion){
			?>
				<div class="wrap top-bar-wrapper" style="background-color:white; padding:10px;">
				
				<!-- Toggle license agreement and terms and conditions -->
				<b><h3 onclick="showTandCiframe();" id="showTandCbtn" style="color:blue;">TERMS AND CONDITIONS</h3>
				<h3 onclick="showPrivacyPolicyiframe();" id="showPrivacyPolicybtn">PRIVACY POLICY</h3></b>
					
					<div style="width:700px; padding:3px; border:1px solid gray;">
						<p style="background-color:#D0F4B2; width:680px; margin:0px; padding:10px;">This plugin is connected to a secure HTTPS page at https://chnsoftwaredevelopers.com</p>
						<div id="TandCiframe">
							<iframe src="https://chnsoftwaredevelopers.com/Terms-And-Conditions/#fullview" style="width:100%; height:800px;"></iframe>
						</div>
						<div id="privacyPolicyiframe" style="display:none;">
							<iframe src="https://chnsoftwaredevelopers.com/Privacy-Policy/#fullview" style="width:100%; height:800px;"></iframe>
						</div>
					</div>
					<br>
					<form method="post" action="options.php">
						<?php settings_fields('Agreement'); ?>
						<input type="text" name="is_terms_and_conditions_accepted" value="true<?php echo $License_agreement_and_TandC_frompluginversion; ?>" style="width:30%; display:none;">
						<?php submit_button('Continue'); ?>
					</form>
				</div>
			<?php
		}else{
	?>

	<!-- Get the user experiences -->
	<?php
		if(get_option('is_review_done') != "yes"){
			?>
				<div class="notice notice-info is-dismissible">
				<p>Hi...! We would like to hear about your user experience with maintenance alerts. Please take a few minutes from your valuable time to give a small review to us.</p>
					<?php if($GLOBALS['maintenance_alerts_action'] == "display_to_close_review"){ ?>
						<form method="post" action="options.php">
							<?php settings_fields('notifications'); ?>
							<input type="text" name="is_review_done" value="yes" style="width:30%; display:none;">
							<?php submit_button('Close notification'); ?>
						</form>
					<?php }else{ ?>
						<a href="https://wordpress.org/support/plugin/maintenance-alerts/reviews/" target="_BLANK">Add my quick review</a>
						<br><a href="admin.php?page=Maintenance-Alerts&maintenance_alerts_action=display_to_close_review" >No, maybe later</a>
						<br><a href="admin.php?page=Maintenance-Alerts&maintenance_alerts_action=display_to_close_review" >I already did</a>
					<?php } ?>
				</div>
			<?php
		}
	?>

	 <!--Connect CHN Account-->
	 <div class="wrap top-bar-wrapper" style="background-color:white; padding:10px;">
		 <?php
			 if(get_option('CHN_Account_User') != ""){

				$auth_email = get_option('CHN_Account_User');
				
				$auth_api = "https://chnsoftwaredevelopers.com/app_authentication/check_account.php?email=" . $auth_email;
							
				$response = json_decode(wp_remote_retrieve_body(wp_remote_get($auth_api)));

				if($response == true){ // If the API connection successful,
					if($response->user == "none" || $response->status != "Active"){
						echo '<div style="padding:10px;" class="notice notice-error is-dismissible">Sorry, your account is not active. Please use another account.</div>';
						$auth_email = "Unknown user";
						update_option('CHN_Account_User', '');
					}
				}
				?>
					<div class="notice notice-success is-dismissible">
						<h3>Plugin connected to : <?php echo $auth_email; ?> (CHN Account)</h3>
						<form method="post" action="options.php">
							<?php settings_fields('Activation_option_group'); ?>
							<input type="text" name="CHN_Account_User" value="" style="width:30%; display:none;">
							<?php submit_button('Disconnect account'); ?>
						</form>
					</div>
				<?php
			 }else{
				 ?>
				 
				 <div class="notice notice-warning is-dismissible">
					 <?php
						// Once receive the required parameters for the authentication process read the JSON response by passing the data along with the token to the API.

						if($GLOBALS['maintenance_alerts_action'] == "runregistration" && isset($_GET['CHNACCOUNTEMAIL']) && isset($_GET['token']) && isset($_SESSION["chnsdauthonce"])){
							$auth_email = $_GET['CHNACCOUNTEMAIL'];
							$auth_token = $_GET['token'];
							$auth_string = $_SESSION["chnsdauthonce"];
							
							$_SESSION["chnsdauthonce"] = NULL;

							$auth_api = "https://chnsoftwaredevelopers.com/app_authentication/auth_with_key3.php?email=" . $auth_email . "&token=" . $auth_token . "&string=" . $auth_string;
							
							$response = json_decode(wp_remote_retrieve_body(wp_remote_get($auth_api)));
							

							if($response == true){ // If the API connection successful,
								if($response->email != "none"){
									?>
									<form method="post" action="options.php">
										<?php settings_fields('Activation_option_group'); ?>
										<br>CHN Account belongs to <?php echo $response->user; ?><input type="text" name="CHN_Account_User" value="<?php echo $response->email; ?>" style="width:30%; display:none;">
										<?php
											submit_button('Connect account');
										?>
									</form>
									<?php
								}else{
									echo '<div style="padding:10px;" class="notice notice-error is-dismissible">Sorry...! The authentication process was interrupted due to incorrect login credentials.</div>';
								}
							}else{
								echo '<div style="padding:10px;" class="notice notice-error is-dismissible">Sorry...! An unexpected error occurred.</div>';
							}
							
						}else{
							// Generate a unique id to pass it to the CHN Account login page.
							$_SESSION["chnsdauthonce"] = rand(111111111,999999999);
							
							if(isset($_SESSION["chnsdauthonce"])){
								?>
									<p>Connect your CHN account to get new information and updates.</p>
									<!-- Pass the necessary parameters to the CHN Account login page with the callback URL. -->
									<a href="https://chnsoftwaredevelopers.com/v2.0/login/?action=registerplugin&productname=maintenance-alerts&callback_url=<?php echo home_url() ?>&string=<?php echo $_SESSION["chnsdauthonce"]; ?>"><button>Connect my CHN Account</button></a><br>
									<br>
								<?php
							}
							
						}
						?>		
				</div>
			<?php	 
			 }
		    ?>
	 </div><br>

	 <!-- If first user config not done -->
	 <?php
		if(get_option('first_user_config') != "done"){
		 ?>
			<div class="wrap top-bar-wrapper" style="background-color:white; padding:10px;"><div class="notice notice-warning">
			<form method="post" action="options.php">
				<?php settings_fields('configuration'); ?>
				<br><br><h3>Maintenance Alerts - configuration</h3><hr>
				<input type="text" value="done" style="display:none;" name="first_user_config" placeholder="none">
				<?php submit_button('Start now'); ?>
			</form></div></div>
		<?php
		}
	  ?>
	
	<!-- If first user config done -->
	 <?php if(get_option('first_user_config') == "done"){ ?>

	 <!--Maintenance Alerts settings box-->
	 <div class="wrap top-bar-wrapper" style="background-color:white; padding:10px;">
		 <div style="background-color:#E2E7EC; padding-left:20px; padding-top:1px; padding-bottom:30px;">
		 
		 <!-- toggle maintenance display type. -->
		 <form method="post" action="options.php">
			<?php settings_fields('Maintenance_type_group'); ?>
			<?php
				if(get_option('Maintenance_type') == "Maintenance_Mode"){
					submit_button('Switch to maintenance alert');
					?>
						<input type="text" value="Maintenance_Alert" style="display:none;" name="Maintenance_type" placeholder="none">
					<?php
				}else{
					submit_button('Switch to maintenance mode');
					?>
						<input type="text" value="Maintenance_Mode" style="display:none;" name="Maintenance_type" placeholder="none">
					<?php
				}
			?>
		</form>


		 <form method="post" action="options.php">
			 <?php settings_fields('option_group'); ?>
			 <!-- Enable and disable maintenance mode/alert-->
			 Action : 
			 <?php
				 if(get_option('Action_select') == "Enabled"){
					?>
					<select id="Action_select" name="Action_select">
					  <option value="Enabled">Enabled</option>
					  <option value="Disabled">Disabled</option>
			 		</select>
			  		<?php
				 }else{
					?>
					<select id="Action_select" name="Action_select">
					  <option value="Disabled">Disabled</option>
					  <option value="Enabled">Enabled</option>
			 		</select>
			  		<?php 
				 }
				 ?>
			</div>
			<!-- General settings for alert -->
			<?php
				if(get_option('Maintenance_type') == "Maintenance_Alert"){
			?>
			<h3>General settings</h3><hr>	 
			 <br>
			 <label for="alert_field_txt">Maintenance alert text :</label><br>
			 <input type="text" name="field" class="large-text" value="<?php 
				 //If the input field is empty, it will save the default text.
				 if(get_option('field') <> ""){
					 echo get_option('field'); 
				 }else{
					 echo'This site will be unavailable on Wednesday, 26 January 2022 from 6.30AM to 12.00PM due to planned maintenance.';
				 }
				 
				 ?>">
			<?php
				}
			?>	 
				
	
				<!-- Title : Advance settings for alert/General settings for maintenance mode -->
				<?php
				if(get_option('Maintenance_type') == "Maintenance_Alert"){
					echo'<br><br><h3>Advanced settings</h3><hr><br>';
				}else{
					echo'<br><br><h3>General settings</h3><hr><br>';
				}
			 	?>	
				 
					  
				<!-- Change text color -->
				<label for="textcolor">Text color : </label>
				<input type="text" id="textcolor" name="textcolor" onkeydown="showColorCombiningWindow()" onkeyup="displayInputColors();" value="<?php
				 if(get_option('textcolor') <> ""){
				 	echo get_option('textcolor'); 
					$Message_text_color = "";
				 }else{
					echo '#FFFFFF';
					$Message_text_color = "Some settings are not saved correctly!";
				 }
				?>">&nbsp;<label id="textcolorDisplayBox" style="border:2px solid black; padding-left:20px; padding-top:5px; background-color:<?php echo get_option('textcolor'); ?>;"></label>&nbsp;
				
				<!-- Change background color -->
				<label for="backgroundcolor">Background color :</label>
				<input type="text" id="backgroundcolor" name="backgroundcolor" onkeydown="showColorCombiningWindow()" onkeyup="displayInputColors();" value="<?php
				 if(get_option('backgroundcolor') <> ""){
				 	echo get_option('backgroundcolor'); 
					$Message_background_color = "";
				 }else{
					echo '#E39C19';
					$Message_background_color = "Some settings are not saved correctly!";
				 }
				?>">&nbsp;<label id="backgroundcolorDisplayBox" style="border:2px solid black; padding-left:20px; padding-top:5px; background-color:<?php echo get_option('backgroundcolor'); ?>;"></label>
				
				 <br><br>
				 <div id="colorCombiningWindow" style="display:none;">Loading...<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i></div><br>
				 <div style="width:90%; height:200px; background-color:gray;">
				 	<div id="backgroundcolorPreview" style="padding-top:10px; padding-bottom:10px; background-color:<?php echo get_option('backgroundcolor'); ?>;">
				 		<center><h3 id="textcolorPreview" style="color:<?php echo get_option('textcolor'); ?>;">Alert text</h3></center>
					</div>
				</div>

				<br><br>
				
				<!-- Change font size -->
				<label for="fontSize">Font size :</label>
				<input type="text" id="fontSize" name="fontSize" value="<?php
				 if(get_option('fontSize') <> ""){
				 	echo get_option('fontSize'); 
					$Message_font_size = "";
				 }else{
					echo '20';
					$Message_font_size = "Some settings are not saved correctly!";
				 }
				?>"> px
				
				<br><br>
				
				<!-- Change padding -->
				<label for="alertPadding">Padding :</label>
				<input type="text" id="alertPadding" name="alertPadding" value="<?php
				 if(get_option('alertPadding') <> ""){
				 	echo get_option('alertPadding'); 
					$Message_alert_padding = "";
				 }else{
					echo '10';
					$Message_alert_padding = "Some settings are not saved correctly!";
				 }
				?>"> px
				
				<br><br>
					 
				 <!-- redirect when user click on the alert  -->
				 <label for="Onclick_event">On click :</label>
				 <input type="text" id="Onclick_event" name="Onclick_event" value="<?php echo get_option('Onclick_event'); ?>" placeholder="https://">
				 
				 <!-- Selection to force disable when user logged in  -->
				 <br><br><label for="Onclick_event">Force disable when user logged in : </label>
				 <!-- <label style="background-color:blue; color:white; font-weight: bold;">&nbsp;New&nbsp;</label> -->
				 <?php
					if(get_option('Force_maintenance_when_loggedin') == "Yes" || get_option('Force_maintenance_when_loggedin') == ""){
						?>
						<select id="Force_maintenance_when_loggedin" name="Force_maintenance_when_loggedin">
						  <option value="Yes">Yes</option>
						  <option value="No">No</option>
						 </select>
						  <?php
					 }else{
						?>
						<select id="Force_maintenance_when_loggedin" name="Force_maintenance_when_loggedin">
						  <option value="No">No</option>
						  <option value="Yes">Yes</option>
						 </select>
						  <?php 
					 }

					 //Maintenance mode advanced settings
					 if(get_option('Maintenance_type') == "Maintenance_Mode"){
						 ?>
							<br><br><h3>Advanced settings</h3><hr><br>

								<!-- Custom page-->	
								<div class="notice notice-warning is-dismissible">
								<b><p>
										Please note that the following settings will disable the general settings because you can set up these settings on the custom page.<br>
										Please remove '/' before '?' when you enter the page URL.<br>
										E.g. <br>
										http://mydomain.com/Maintenance-Alerts/wordpress?page_id=13 (Correct)<br>
										http://mydomain.com/Maintenance-Alerts/wordpress/?page_id=13 (Incorrect)
								</p></b>
						 		</div>							
								 <label>Display custom page : </label>
								<input type="text" id="custom_maintenance_page" name="custom_maintenance_page" placeholder="URL here. Leave blank to configure only the general settings." class="large-text" style="width:50%" value="<?php
									echo get_option('custom_maintenance_page');
								?>">
							 <?php
						 } 
					 ?>
					 
					 <!-- Error messages -->
					 <br><p style="color:red;"><?php echo $Message_text_color ?></p>
					 <p style="color:red;"><?php echo $Message_background_color ?></p>
					 <p style="color:red;"><?php echo $Message_font_size ?></p>
					 <p style="color:red;"><?php echo $Message_alert_padding ?></p>
				 <?php submit_button(); ?>
			 </form>
	 
	 </div>

	 <!--Show new informations about the plugin.-->
	 <div class="wrap top-bar-wrapper" style="background-color:white; padding:10px;"><p style="background-color:#D0F4B2; width:480px; margin:0px; padding:10px;">This plugin is connected to a secure HTTPS page at <br>https://chnsoftwaredevelopers.com</p><iframe src="https://chnsoftwaredevelopers.com/Himashana/WP-Plugins/Maintenance_alerts/wp-plugin-new-info.php?request={688D0D1F-3298-4d27-B6A8-647A2B332399}" width="500px" height="200px"></div>	 
	
	 <?php 
	}
 }
}
 //Theme Compatibility
 function MaintenanceAlertsMenuCompatibility(){
	 include_once dirname( __FILE__ ) . '/compatibility_check.php';
 }
 
 //Plugin configuration(export/import)
 function MaintenanceAlertsMenuConfiguration(){
	include_once dirname( __FILE__ ) . '/Configuration.php';
}

 //About the plugin
  function MaintenanceAlertsMenuAbout(){
	echo'<h1>Maintenance Alerts - About</h1>';
    ?>
         <div class="wrap top-bar-wrapper" style="background-color:white; padding:10px;">
            * Plugin Name:       Maintenance alerts
            <br>
            * Plugin URI:        https://chnsoftwaredevelopers.com/maintenance-alerts
            <br>
            * Description:       You can use this plugin to show the website maintenance scheduled information to the visitors of your website or put your site into full maintenance mode.
            <br>
            * Version:           1.3.0
            <br>
            * Requires at least: 5.2
            <br>
            * Requires PHP:      7.2
            <br>
            * Author:            Himashana
            <br>
            * Author URI:        https://chnsoftwaredevelopers.com
            <br>
            * License:           GPL v2 or later
            <br>
            * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
       </div>
       
    <?php
 }
