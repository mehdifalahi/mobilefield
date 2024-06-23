$(document).ready(function() {
	
	$("<div class='field-mobile'></div>").insertAfter( "input[name=mobile]" );
	var mobile_val = $("input[name=mobile]").val();
	var mobile_number = '';
	var mobile_country = '';
	if(mobile_val){
		var mobs = mobile_val.split('_');
		mobile_country = mobs[0];
		mobile_number = mobs[1];
	}
	
	
	$("input[name=mobile]").remove();
	setTimeout(function(){
		$('.field-mobile').intlInputPhone({
			preferred_country: mobile_country ? [mobile_country] : ['fr'],
			fieldvalue: mobile_number,
			display_error : 'on',
			error_message: {"INVALID_PH_N": "Invalid phone number","INVALID_CC": "Invalid country code","TOO_SHORT": "The phone number supplier is too short","TOO_LONG": "The phone number supplier is too long","UNKNOWN": "Unknow phone number"},
		});
	},100);
	//$('#field-mobile').intlInputPhone();

});	