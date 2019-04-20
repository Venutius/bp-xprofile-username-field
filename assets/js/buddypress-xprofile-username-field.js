( function(jQ) {



/**
 * Wrapped in a class.
 */
bpxprofile_username = {

	init : function(){

	   if ( jQ("div#poststuff select#fieldtype").html() !== null ) {

			// add username field type on Add/Edit Xprofile field admin screen
			if (

				jQ('div#poststuff select#fieldtype option[value="username"]').html() === undefined ||
				jQ('div#poststuff select#fieldtype option[value="username"]').html() == null

			) {

				var usernameOption = '<option value="username">'+UsernameParams.username+'</option>';
				jQ("div#poststuff select#fieldtype").append(usernameOption);

			}

		}
	}

};



/**
 * On page load...
 */
jQ(document).ready(function(){
	bpxprofile_username.init();
});



})(jQuery);


