// js/admin-form.js

jQuery( document ).ready( function() {
	if ( tcc_admin_options.showhide ) {
		jQuery.each( tcc_admin_options.showhide, function( counter, item ) {
			if ( targetableElement( item ) ) {
				let origin = '.' + item.origin + ' input:radio';
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
	let ans = confirm( 'Remove this image?' ); // FIXME: localize this
	if ( ans ) {
		let iuField = jQuery( el.parentNode ).data( 'field' );
		let iuInput = document.getElementById( iuField + '_input' );
		let iuImage = document.getElementById( iuField + '_img' );
		iuInput.value = '';
		iuImage.src   = '';
		jQuery( iuImage ).addClass( 'hidden' );
	}
}

function imageUploader( el, e ) {
	e.preventDefault();
	let iuTitle  = jQuery( el.parentNode ).data( 'title' );
	let iuButton = jQuery( el.parentNode ).data( 'button' );
	let iuField  = jQuery( el.parentNode ).data( 'field' );
	const custom_uploader = wp.media({
		title: iuTitle,
		button: { text: iuButton, },
		multiple: false
	});
	custom_uploader.on( 'select', function() {
		let attachment = custom_uploader.state().get( 'selection' ).first().toJSON();
		if ( iuField ) {
			let iuInput = document.getElementById( iuField + '_input' );
			let iuImage = document.getElementById( iuField + '_img' );
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
		let target = jQuery( el ).attr( 'data-item' );
		let show   = jQuery( el ).attr( 'data-show' );
		if ( show ) {
			showhideAdminElement( el, target, show, null );
		}
		let hide = jQuery( el ).attr( 'data-hide' );
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
		let radio = jQuery( origin + ' input:radio:checked' );
		if ( radio.length ) {
			let state = jQuery( radio ).val();
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
