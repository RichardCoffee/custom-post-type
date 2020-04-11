
jQuery(document).ready(function() {
  for( let i = 0, cnt = term_list.length; i < cnt; i++ ) {
    jQuery('#tag-'+term_list[i]+' .row-actions .delete').remove();
    jQuery('#tag-'+term_list[i]+' label').remove();
    jQuery('#tag-'+term_list[i]+' input').remove();
  }
});
