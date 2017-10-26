function renderMediaUploader( $ ) {
  'use strict';

  var file_frame, image_data;

  if( undefined !== file_frame ) {
    file_frame.open();
    return;
  }

  file_frame = wp.media.frames.file_frame = wp.media({
    frame: 'post',
    state: 'insert',
    multiple: false
  });

  file_frame.on( 'insert', function() {
    var json = file_frame.state().get('selection').first().toJSON();

    if( 0 > $.trim( json.url.length ) ) {
      return;
    }

    $("#custom_avatar").val(json.url);
    $(".user-profile-picture img").attr('src',json.url);
  });

  file_frame.open();
}

jQuery(document).ready(function() {
  jQuery("#custom_avatar").click(function(e){
    e.preventDefault();
    renderMediaUploader( jQuery );
  });
});
