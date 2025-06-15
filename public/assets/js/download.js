$(document).ready(function (){
  $("#doDownload").on('click', function (e){
    e.preventDefault();
    const document_id = $(this).data('id');
    const response = API.post('documents/get-document',
      {document_id:document_id})
      .then(response => {
        if (!response?.download_url) {
          throw new Error('Invalid server response');
        }
        window.location.href = response.download_url
      }).catch(error => {
        const message = error.responseJSON?.message || 'Download failed';
        window.FlashMessage.error(message)
      })

  })
})
