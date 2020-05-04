// js/admin-form.js

jQuery( document ).ready( function() {
	if ( tcc_admin_options.showhide ) {
		jQuery.each( tcc_admin_options.showhide, function( counter, item ) {
			if ( targetableElement( item ) ) {
				let origin = '.' + item.origin + ( ( item.render === 'select' ) ? ' select' : ' input:radio' );
				jQuery( origin ).change( item, function( e ) {
					targetableElement( e.data );
				});
			}
		});
	}
	jQuery( '.form-colorpicker'  ).wpColorPicker();
	jQuery( '.form-image'        ).click( function( e ) { imageUploader( this, e ); });
	jQuery( '.form-image-delete' ).click( function( e ) { imageDelete( this ); });
});

function imageDelete( el ) {
	let ans = confirm( tcc_admin_options.media['delete'] );
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

function targetableElement( item ) {
	if ( item.origin && item.target ) {
		let query  = '.' + item.origin + ( ( item.render === 'select' ) ? ' select option:selected' : ' input:radio:checked' );
		let result = jQuery( query );
		if ( result.length ) {
			let state = jQuery( result ).val();
			if ( state ) {
				let target = '.' + item.target;
				if ( item.show ) {
					if ( state === item.show ) {
						jQuery( target ).parent().parent().show( 2000 ); //removeClass('hidden');
					} else {
						jQuery( target ).parent().parent().hide( 2000 ); //addClass('hidden');
					}
				} else if ( item.hide ) {
					if ( state === item.hide ) {
						jQuery( target ).parent().parent().hide( 2000 ); //addClass('hidden');
					} else {
						jQuery( target ).parent().parent().show( 2000 ); //removeClass('hidden');
					}
				}
			}
			return query;
		}
	}
	return false;
}
