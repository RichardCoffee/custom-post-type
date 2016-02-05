
jQuery(document).ready(function() {
  for(var i=0,cnt=term_list.length;i<cnt;i++) {
    jQuery('#tag-'+term_list[i]+' .row-actions .delete').remove();
  }
});
