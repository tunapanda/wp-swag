import 'jquery';

jQuery(document).ready(() => {
  if (window.H5P && H5P.externalDispatcher) {
    H5P.externalDispatcher.on('xAPI', e => {
      let data = {
        action: 'xapi_event',
        swagpath: api_settings.swagpath_id,
        swagifact: api_settings.swagifact_slug,
        statement: e.data.statement
      };

      jQuery.ajax({
        type: 'POST',
        url: api_settings.ajax_url,
        headers: {
          'X-WP-Nonce': api_settings.nonce
        },
        contentType: 'application/json',
        data: JSON.stringify(data)
      });
    });
  }
});