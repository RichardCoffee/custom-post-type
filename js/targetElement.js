
class TargetElement {

|  constructor( item ) {
|  |  this.item = item;
|  }

|  targetable() {
|  |  let origin = '.' + this.item.origin + ' input';
|  |  if ( this.item.render === 'select' ) {
|  |  |  this.seek = origin + ':select:selected';
|  |  } else {
|  |  |  this.seek = origin + ':radio:checked';
|  |  }
|  |  let test = jQuery( this.seek );
|  |  return test.length;
|  }

|  state() {
|  |  let item = jQuery( this.seek );
|  |  if ( item.length ) {
|  |  |  let state = jQuery( item ).val();
|  |  |  if ( state ) {
|  |  |  |  let target = '.' + this.item.target;
|  |  |  |  if ( this.item.show ) {
|  |  |  |  |  if ( state === this.item.show ) {
|  |  |  |  |  |  jQuery( target ).parent().parent().show( 2000 ); //removeClass('hidden');
|  |  |  |  |  } else {
|  |  |  |  |  |  jQuery( target ).parent().parent().hide( 2000 ); //addClass('hidden');
|  |  |  |  |  }
|  |  |  |  } else if ( this.item.hide ) {
|  |  |  |  |  if ( state === this.item.hide ) {
|  |  |  |  |  |  jQuery( target ).parent().parent().hide( 2000 ); //addClass('hidden');
|  |  |  |  |  } else {
|  |  |  |  |  |  jQuery( target ).parent().parent().show( 2000 ); //removeClass('hidden');
|  |  |  |  |  }
				}
			}
		}
	}

}
