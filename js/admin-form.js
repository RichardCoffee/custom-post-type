// js/admin-form.js

jQuery( document ).ready( function() {
	if ( tcc_admin_options.showhide ) {
		jQuery.each( tcc_admin_options.showhide, function( counter, item ) {
			if ( targetableElement( item ) ) {
				var origin = '.' + item.origin + ' input:radio';
				jQuery( origin ).change( item, function( e ) {
					targetableElement( e.data );
				});
			}
		});
	}
//	showhideElements( jQuery( '.showhide' ) );
	jQuery( '.form-colorpicker'  ).wpColorPicker();
	jQuery( '.form-image'        ).click( function( e ) { imageUploader( this, e ); });
	jQuery( '.form-image-delete' ).click( function( e ) { imageDelete( this ); });
});

function imageDelete( el ) {
	var ans = confirm( 'Remove this image?' ); // FIXME: localize this
	if ( ans ) {
		var iuField = jQuery( el.parentNode ).data( 'field' );
		var iuInput = document.getElementById( iuField + '_input' );
		var iuImage = document.getElementById( iuField + '_img' );
		iuInput.value = '';
		iuImage.src   = '';
		jQuery( iuImage ).addClass( 'hidden' );
	}
}

function imageUploader( el, e ) {
	e.preventDefault();
	var iuTitle  = jQuery( el.parentNode ).data( 'title' );
	var iuButton = jQuery( el.parentNode ).data( 'button' );
	var iuField  = jQuery( el.parentNode ).data( 'field' );
	var custom_uploader = wp.media({
		title: iuTitle,
		button: { text: iuButton, },
		multiple: false
	});
	custom_uploader.on( 'select', function() {
		var attachment = custom_uploader.state().get( 'selection' ).first().toJSON();
		if ( iuField ) {
			var iuInput = document.getElementById( iuField + '_input' );
			var iuImage = document.getElementById( iuField + '_img' );
			iuInput.value = attachment.url;
			iuImage.src   = attachment.url;
			jQuery( el.parentNode ).children( '.form-image-container' ).removeClass( 'hidden' );
			jQuery( el.parentNode ).children( '.form-image-delete' ).removeClass( 'hidden' );
		}
	});
	custom_uploader.open();
}

function showhideElements( els ) {
	jQuery( els ).each( function( el ) {
		var target = jQuery( el ).attr( 'data-item' );
		var show   = jQuery( el ).attr( 'data-show' );
		if ( show ) {
			showhideAdminElement( el, target, show, null );
		}
		var hide = jQuery( el ).attr( 'data-hide' );
		if ( hide ) {
			showhideAdminElement( el, target, null, hide );
		}
	});
}

function targetableElement( item ) {
	return showhideAdminElement( '.'+item.origin, '.'+item.target, item.show, item.hide );
}

function showhideAdminElement( origin, target, show, hide ) {
	if ( origin && target ) {
		var radio = jQuery( origin + ' input:radio:checked' );
		if ( radio.length ) {
			var state = jQuery( radio ).val();
			if ( state ) {
				if ( show ) {
					if ( state === show ) {
						jQuery( target ).parent().parent().show( 2000 ); //removeClass('hidden');
					} else {
						jQuery( target ).parent().parent().hide( 2000 ); //addClass('hidden');
					}
				} else if ( hide ) {
					if ( state === hide ) {
						jQuery( target ).parent().parent().hide( 2000 ); //addClass('hidden');
					} else {
						jQuery( target ).parent().parent().show( 2000 ); //removeClass('hidden');
					}
				}
			}
			return true;
		}
	}
	return false;
}
