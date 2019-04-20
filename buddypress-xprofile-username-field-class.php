<?php

/**
 * Username xprofile field type.
 *
 * @since BuddyPress (2.0.0)
 */
class BP_XProfile_Field_Type_Username extends BP_XProfile_Field_Type {



	/**
	 * Constructor for the Rich Text field type.
	 *
	 * @since 0.2
 	 */
	public function __construct() {

		parent::__construct();

		$this->category = _x( 'Single Fields', 'xprofile field type category', 'bp-xprofile-username-field' );
		$this->name     = _x( 'Username Selector', 'xprofile field type', 'bp-xprofile-username-field' );
		$this->type     = 'username';

		// allow all values to pass validation
		$this->set_format( '/.*/', 'replace' );

		do_action( 'bp_xprofile_field_type_username', $this );

	}



	/**
	 * Output the edit field HTML for this field type.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @since 0.2
	 *
	 * @param array $raw_properties Optional key/value array
	 */
	public function edit_field_html( array $raw_properties = array() ) {

		// user_id is a special optional parameter that certain other fields types pass to {@link bp_the_profile_field_options()}.
		if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$users = get_users();
		foreach ( $users as $user ) {
			$user_list[$user->ID] = $user->user_login;
		}
		$user_list['-1'] = sanitaize_text_field( __( 'Non Selected', 'bp-xprofile-username-field' ) );
		
		$data = bp_get_the_profile_field_edit_value();
		if ( ! isset( $data ) || $data == '' ) $data = '-1';

		?>
		<?php do_action( bp_get_the_profile_field_errors_action() ); ?>
			<div class="input-username">
				<legend class="label-form <?php if ( bp_get_the_profile_field_is_required() ) { ?>required<?php } ?>" for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bpxprofileunf'); } ?></legend>
				
				<select name="<?php bp_the_profile_field_input_name(); ?>" id="<?php bp_the_profile_field_input_name(); ?>">
					<?php foreach ( $user_list as $user_id => $username ) : ?>
						<option name="" value="<?php echo $user_id; ?>" <?php if ( $data == $user_id ) echo 'selected'; ?>><?php echo $username; ?></option> 
					<?php endforeach; ?>
				</select>
			</div>
			<?php


	}



	/**
	 * Output HTML for this field type on the wp-admin Profile Fields screen.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @since 0.2
	 *
	 * @param array $raw_properties Optional key/value array of permitted attributes that you want to add.
	 */
	public function admin_field_html( array $raw_properties = array() ) {

			$users = get_users();
			foreach ( $users as $user ) {
				$user_list[$user->ID] = $user->user_login;
			}
			$user_list['-1'] = sanitaize_text_field( __( 'Non Selected', 'bp-xprofile-username-field' ) );
			
			?>
			<div class="input-username">
				<legend class="label-form <?php if ( bp_get_the_profile_field_is_required() ) { ?>required<?php } ?>" for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bpxprofileunf'); } ?></legend>
				
				<select name="<?php bp_the_profile_field_input_name(); ?>" id="<?php bp_the_profile_field_input_name(); ?>">
					<?php foreach ( $user_list as $user_id => $username ) : ?>
						<option name="" value="<?php echo $user_id; ?>" ><?php echo $username; ?></option> 
					<?php endforeach; ?>
				</select>
			</div>
			<?php


	}



	/**
	 * This method usually outputs HTML for this field type's children options
	 * on the wp-admin Profile Fields "Add Field" and "Edit Field" screens, but
	 * for this field type, we don't want it, so it's stubbed out.
	 *
	 * @since 0.2
	 *
	 * @param BP_XProfile_Field $current_field The current profile field on the add/edit screen.
	 * @param string $control_type Optional. HTML input type used to render the current field's child options.
	 */
	public function admin_new_field_html( BP_XProfile_Field $current_field, $control_type = '' ) {}



} // class ends



