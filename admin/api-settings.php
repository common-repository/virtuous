<?php 
$display_message = false;

$virtuous_api_environment = '';
$virtuous_api_login_email = '';
$virtuous_api_login_password = '';
$virtuous_api_include_support_button = '';
$virtuous_api_support_button_text = '';
$virtuous_api_support_form_base_url = '';
$alert = '';

global $VirtuousApi;
global $VirtuousUtilities;

if (! $VirtuousApi->vdrm_virtuous_credentials_loaded ) {
	$display_message = true;
	$alert = 'Let\'s get your Virtuous plugin configured. Please enter the information below so we can load your projects.';
}

$available_project_types = array();
$active_project_types = array();

if ( $_POST ) {

	$virtuous_api_environment = $_POST['virtuous_api_environment'];
	$virtuous_api_login_email = $_POST['virtuous_api_login_email'];
	$virtuous_api_login_password = $VirtuousUtilities->vdrm_encode_string( $_POST['virtuous_api_login_password'] );

	// check to see if these credentials work
	$is_valid = $VirtuousApi->vdrm_verify_credentials( $virtuous_api_environment, $virtuous_api_login_email, $virtuous_api_login_password );
	if ( $is_valid == true ) {
		update_option( 'virtuous_api_environment', $virtuous_api_environment );
		update_option( 'virtuous_api_login_email', $virtuous_api_login_email );
		update_option( 'virtuous_api_login_password', $virtuous_api_login_password );
		$alert = 'Your information has been saved';
	} else {
		$alert = 'We were unable to verify those credentials. Please check your username and password with this environment.';
	}
	$virtuous_api_login_password = $VirtuousUtilities->vdrm_decode_string( $virtuous_api_login_password );		
	$display_message = true;

} else {

	if ( $VirtuousApi->vdrm_virtuous_credentials_loaded ) { // premiere fois
		$virtuous_api_environment = get_option( 'virtuous_api_environment' );
		$virtuous_api_login_email = get_option( 'virtuous_api_login_email' );
		$virtuous_api_login_password = $VirtuousUtilities->vdrm_decode_string( get_option( 'virtuous_api_login_password' ) );
	}
} 
?>

<div class="wrap">
<h1>Virtuous API Settings</h1><br/>

<form method="post">
<?php 
	settings_fields( 'virtuous-settings-group' );
    do_settings_sections( 'virtuous-settings-group' ); 
?>
	<table>
<?php
		if ( $display_message ) {
?>
		<tr>
			<td colspan="2"><p><?php echo $alert; ?></p></td>
		</tr>
<?php			
		}	
?>		
		<tr>
			<td style="width: 180px;">
				<label for="virtuous_api_environment">API Environment</label>
			</td>
			<td>
				<select name="virtuous_api_environment" id="virtuous_api_environment" style="width: 250px;">
<?php
				foreach ($vdrm_environments as $key => $value) {
?>						
					<option value="<?php echo $value; ?>"<?php if ( $virtuous_api_environment == $value ) { echo ' selected'; } ?>><?php echo $key; ?></option>
<?php
				}
?>										
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="virtuous_api_login_email">API Login *</label>
			</td>
			<td>
				<input type="text" style="width: 250px;" name="virtuous_api_login_email" value="<?php echo $virtuous_api_login_email; ?>" />&nbsp;<input type="password" name="virtuous_api_login_password" style="width: 250px;" value="<?php echo $virtuous_api_login_password; ?>" />
			</td>
		</tr>			
		<tr>
			<td>&nbsp;</td>
			<td>	
				<?php submit_button(); ?>
			</td>
		</tr>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td colspan="2" style="font-size: smaller;">* We strongly recommend that you create a login account specifically for the purpose of connecting to the API</td>
		</tr>
	</table>
</form>
	
