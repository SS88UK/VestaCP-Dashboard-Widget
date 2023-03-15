function ss88_dobars()
{
	jQuery('.bar-percentage[data-percentage]').each(function () {
	var progress = jQuery(this);
	var whatp = jQuery(this).attr('data-percentage');
	
	if(whatp=='U')
	{
		progress.text('UNLIM');
	}
	else
	{
	
		var percentage = Math.ceil(jQuery(this).attr('data-percentage'));
		jQuery({countNum: 0}).animate({countNum: percentage}, {
		duration: 2000,
		easing:'linear',
		step: function() {
							var pct = Math.floor(this.countNum) + '%';
							progress.text(pct) && progress.siblings().children().css('width',pct);
							if(this.countNum>79) { progress.parent().find('.bar').css('background-color', '#d03a3a'); }
						}
		});
	}
	});
}

function hookSubmitform() {
jQuery('.ss88_vw_form').submit(function(e) {
	
	e.preventDefault();
	
	jQuery('.ss88_spinner').show();
	
	var th = jQuery(this);
	
	var cbAns = ( jQuery('input[name=ss88_vestacp_widget_vesta_verifyssl]').is(':checked') ) ? 1 : 0;
	
	var posting = jQuery.post( th.attr('action'), { action: 'ss88_vestacp_widget_ajax', nonce: jQuery('input[name=ss88_vestacp_widget_nonce]').val(), v_url: jQuery('input[name=ss88_vestacp_widget_vesta_url]').val(), v_username: jQuery('input[name=ss88_vestacp_widget_vesta_username]').val(), v_hash: jQuery('input[name=ss88_vestacp_widget_vesta_hash]').val(), v_verifyssl: cbAns } );
	
	jQuery('.ss88_vw_form input[type=submit]').attr('disabled', true);
	
	posting.done(function( data ) {
		
		jQuery('.ss88_vw_formdiv').html(data);
		ss88_dobars();
		jQuery('.ss88_vw_form input[type=submit]').attr('disabled', false);
		jQuery('.ss88_spinner').hide();
		
	});
	
});
}

jQuery(document).ready(function(){

ss88_dobars();
hookSubmitform();

});
