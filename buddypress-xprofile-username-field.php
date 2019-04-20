<?php
/*
--------------------------------------------------------------------------------
Plugin Name: BP xProfile Username Field
Description: Add a Username selector custom field type to Extended Profiles in BuddyPress.
Version: 0.2.5
Author: Venutius
Author URI: http://buddyuser.com
Plugin URI: http://buddyuser.com/plugin/bp-xprofile-username-field
--------------------------------------------------------------------------------
Forked from: Buddypress Xprofile Richtext Field
--------------------------------------------------------------------------------
*/



// set our version here - bumping this will cause CSS and JS files to be reloaded
define( 'BP_XPROFILE_USERNAME_FIELD_VERSION', '0.2.5' );


/**
 * BP xProfile Username Field Class.
 *
 * A class that encapsulates plugin functionality.
 *
 * @since 3.0
 */
class BP_XProfile_Username_Field {



	/**
	 * Initialises this object.
	 *
	 * @since 0.1
	 */
	function __construct() {

		// use translation files
		$this->enable_translation();

		// there's a new API in BuddyPress 2.0
		if ( function_exists( 'bp_xprofile_get_field_types' ) ) {

			// include class
			require_once( 'buddypress-xprofile-username-field-class.php' );

			// register with BP the 2.0 way...
			add_filter( 'bp_xprofile_get_field_types', array( $this, 'add_field_type' ) );

			// we need to parse the edit value in BP 2.0
			add_filter( 'bp_get_the_profile_field_edit_value', array( $this, 'get_field_value' ), 30, 3 );

		} else {

			// register field type
			add_filter( 'xprofile_field_types', array( $this, 'register_field_type' ) );

			// preview field type
			add_filter( 'xprofile_admin_field', array( $this, 'preview_admin_field'), 9, 2 );

			// test for a function in BP 1.7+
			if ( function_exists( 'bp_is_network_activated' ) ) {

				// in BP 1.7+ show our field type in edit mode via pre_visibility hook
				add_action( 'bp_custom_profile_edit_fields_pre_visibility', array( $this, 'edit_field' ) );

			} else {

				// show our field type in edit mode via the previous hook
				add_action( 'bp_custom_profile_edit_fields', array( $this, 'edit_field' ) );

			}

			// enqueue javascript on admin screens
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_js' ) );

		}

		// show our field type in read mode after all BuddyPress filters
		add_filter( 'bp_get_the_profile_field_value', array( $this, 'get_field_value' ), 30, 3 );

		// filter for those who use xprofile_get_field_data instead of get_field_value
		add_filter( 'xprofile_get_field_data', array( $this, 'get_field_data' ), 15, 3 );

		// enqueue basic stylesheet on public-facing pages
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ) );

		// add BP Profile Search compatibility
		$this->bps_compat();

	}



	/**
	 * Load translation files.
	 *
	 * A good reference on how to implement translation in WordPress:
	 * http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/
	 *
	 * @since 0.1
	 */
	public function enable_translation() {

		// not used, as there are no translations as yet
		load_plugin_textdomain(

			// unique name
			'bp-xprofile-username-field',

			// deprecated argument
			false,

			// relative path to directory containing translation files
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'

		);

	}



	//##########################################################################



	/**
	 * Add details of our xProfile field type. (BuddyPress 2.0)
	 *
	 * @since 0.2
	 *
	 * @param array Key/value pairs (field type => class name).
	 * @return array Key/value pairs (field type => class name).
	 */
	function add_field_type( $fields ) {

		// make sure we get an array
		if ( is_array( $fields ) ) {

			// add our field to the array
			$fields['username'] = 'BP_XProfile_Field_Type_Username';

		} else {

			// create array with our item
			$fields = array( 'username' => 'BP_XProfile_Field_Type_Username' );

		}

		// --<
		return $fields;

	}


	//##########################################################################



	/**
	 * Register our field type.
	 *
	 * @since 0.1
	 *
	 * @param array $field_types The existing array of field types
	 * @return array $field_types The modified array of field types
	 */
	function register_field_type( $field_types ) {

		// make sure we get an array
		if ( is_array( $field_types ) ) {

			// append our item
			$field_types[] = 'username';

		} else {

			// set array with our item
			$field_types = array( 'username' );

		}

		// --<
		return $field_types;

	}



	/**
	 * Preview our field type.
	 *
	 * @since 0.1
	 *
	 * @param object $field The field object
	 * @param boolean $echo When true, echoes the output, otherwise returns it
	 * @return string $html The admin field preview markup
	 */
	function preview_admin_field( $field, $echo = true ) {

		// is it our type?
		if ( $field->type == 'username' ) {

			// init
			$html = '';

			$users = get_users();
			foreach ( $users as $user ) {
				$user_list[$user->ID] = $user->user_login;
			}
			$user_list['-1'] = sanitaize_text_field( __( 'Non Selected', 'bp-xprofile-username-field' ) );

			// start buffering
			ob_start();

			// get data and show
			$data = BP_XProfile_ProfileData::get_value_byid( $field->id );
			if ( ! isset( $data ) || $data == '' ) {
				$data = '-1';
			}
			?>
			<div class="input-username">
				<legend class="label-form <?php if ( bp_get_the_profile_field_is_required() ) { ?>required<?php } ?>" for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bpxprofileunf'); } ?></legend>
				
				<select name="<?php bp_the_profile_field_input_name(); ?>" id="<?php bp_the_profile_field_input_name(); ?>">
					<?php foreach ( $user_list as $user_id => $username ) : ?>
						<option name="" value="<?php echo $user_id; ?>" <?php if ( $data == $user_id ) echo 'selected'; ?>><?php echo $username; ?></option> 
					<?php endforeach; ?>
				</select>
			</div>
			<?php

			// clean up
			$html = ob_get_contents();
			ob_end_clean();

			if ( $echo ) {
				echo $html;
				return;
			} else {
				return $html;
			}

		}

	}



	/**
	 * Show our field type in edit mode.
	 *
	 * @since 0.1
	 */
	function edit_field() {

		// only for our filed type...
		if ( bp_get_the_profile_field_type() == 'username' ) {

			global $field;

			// init data
			$data = '';

			// do we have data yet?
			if ( isset( $field->data->value ) && $field->data->value !== '' ) {

				// yes, grab it
				$data = $field->data->value;

			} else {
				
				$data = -1;
				
			}
			
			$users = get_users();
			foreach ( $users as $user ) {
				$user_list[$user->ID] = $user->user_login;
			}
			$user_list['-1'] = sanitaize_text_field( __( 'Non Selected', 'bp-xprofile-username-field' ) );

			// start buffering
			ob_start();

			?>
			<div class="input-username">
				<legend class="label-form <?php if ( bp_get_the_profile_field_is_required() ) { ?>required<?php } ?>" for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bpxprofileunf'); } ?></legend>
				
				<select name="<?php bp_the_profile_field_input_name(); ?>" id="<?php bp_the_profile_field_input_name(); ?>">
					<?php foreach ( $user_list as $user_id => $username ) : ?>
						<option name="" value="<?php echo $user_id; ?>" <?php if ( $data == $user_id ) echo 'selected'; ?>><?php echo $username; ?></option> 
					<?php endforeach; ?>
				</select>
			</div>
			<?php

			// clean up
			$output = ob_get_contents();
			ob_end_clean();

			// print to screen
			echo $output;

		}

	}



	/**
	 * Show our field type in read mode.
	 *
	 * @since 0.1
	 *
	 * @param string $value The existing value of the field
	 * @param string $type The type of field
	 * @param integer $user_id The numeric ID of the WordPress user
	 * @return string $value The modified value of the field
	 */
	function get_field_value( $value = '', $type = '', $user_id = '' ) {

		// is it our field type?
		if ( $type == 'username' ) {

			// we want the raw data, unfiltered
			global $field;
			$value = $field->data->value;
			
			//convert user_id to username
			if ( isset( $value ) && $value != '-1' ) {
				$user = get_userdata( $value );
				$value = $user->user_login;
			} else {
				$value = sanitaize_text_field( __( 'Non Selected', 'bp-xprofile-username-field' ) );
			}
			// apply content filter
			$value = apply_filters( 'bp_xprofile_field_type_username_content', stripslashes( $value ) );

			// return filtered value
			return apply_filters( 'bp_xprofile_field_type_username_value', $value );

		}

		// fallback
		return $value;

	}



	/**
	 * Filter for those who use xprofile_get_field_data instead of get_field_value.
	 *
	 * @since 0.1
	 *
	 * @param string $value The existing value of the field
	 * @param string $type The type of field
	 * @param integer $user_id The numeric ID of the WordPress user
	 * @return string $value The modified value of the field
	 */
	function get_field_data( $value = '', $field_id = '', $user_id = '' ) {

		// check we get a field ID
		if ( $field_id === '' ) { return $value; }

		// get field object
		$field = new BP_XProfile_Field( $field_id );

		// is it ours?
		if ( $field->type == 'username' ) {

			// apply content filter
			$value = apply_filters( 'bp_xprofile_field_type_username_content', stripslashes( $value ) );

			// return filtered value
			return apply_filters( 'bp_xprofile_field_type_username_value', $value );

		}

		// fallback
		return $value;

	}



	/**
	 * Enqueue JS files.
	 *
	 * @since 0.1
	 *
	 * @param str $hook The identifier for the current admin page
	 */
	function enqueue_js( $hook ) {

		// only enqueue scripts on appropriate BP pages
		if ( 'users_page_bp-profile-setup' != $hook AND 'buddypress_page_bp-profile-setup' != $hook ) {
			return;
		}

		// enqueue it
		wp_enqueue_script(
			'bpxprofileunf-js',
			plugins_url( 'assets/js/bp-xprofile-username-field.js', __FILE__ ),
			array( 'jquery' ), // deps
			BP_XPROFILE_USERNAME_FIELD_VERSION // version
		);

		// define translatable strings
		$params = array(
			'username' => __( 'Username', 'bpxprofileunf' )
		);

		// localise
		wp_localize_script(
			'bpxprofileunf-js',
			'UsernameParams',
			$params
		);

	}



	/**
	 * Enqueue CSS files.
	 *
	 * @since 0.1
	 */
	function enqueue_css() {

		// enqueue
		wp_enqueue_style(
			'bpxprofileunf-css',
			plugins_url( 'assets/css/bp-xprofile-username-field.css', __FILE__ ),
			array(), // deps
			BP_XPROFILE_USERNAME_FIELD_VERSION, // version
			'all' // media
		);

	}



	/**
	 * BP Profile Search compatibility.
	 *
	 * @see http://dontdream.it/bp-profile-search/custom-profile-field-types/
	 *
	 * @since 0.2.3
	 */
	public function bps_compat() {

		// bail unless BP Profile Search present
		if ( ! defined( 'BPS_VERSION' ) ) return;

		// add filters
		add_filter( 'bps_field_validation_type', array( $this, 'bps_field_compat' ), 10, 2 );
		add_filter( 'bps_field_html_type', array( $this, 'bps_field_compat' ), 10, 2 );
		add_filter( 'bps_field_criteria_type', array( $this, 'bps_field_compat' ), 10, 2 );
		add_filter( 'bps_field_query_type', array( $this, 'bps_field_compat' ), 10, 2 );

	}



	/**
	 * BP Profile Search field compatibility.
	 *
	 * @since 0.2.3
	 *
	 * @param string $field_type The existing xProfile field type
	 * @param object $field The xProfile field object
	 * @return string $field_type The modified xProfile field type
	 */
	public function bps_field_compat( $field_type, $field ) {

		// cast our field type as 'textbox'
		switch ( $field->type ) {
			case 'username':
				$field_type = 'select';
				break;
		}

		// --<
		return $field_type;

	}



} // class ends



/**
 * Initialise our plugin after BuddyPress initialises.
 *
 * @since 0.1
 */
function bp_xprofile_username_field() {

	// make global in scope
	global $bp_xprofile_username_field;

	// init plugin
	$bp_xprofile_username_field = new BP_XProfile_Username_Field;

}

// add action for plugin loaded
add_action( 'bp_loaded', 'bp_xprofile_username_field' );



