$(document).ready(function (){
  $("#doPreview").on('click', function (e){
    e.preventDefault();
    const document_id = $(this).data('id');
    const response = API.post('documents/preview',
      {document_id:document_id}
    ).then(response => {
      if (!response) {
        throw new Error('Invalid server response');
      }
      const previewContainer = $("#previewContainer");
      previewContainer.empty();
      previewContainer.html('<iframe id="docPreview" class="w-100 border rounded" style="height: 500px;"></iframe>')
      const iframe = document.getElementById('docPreview');
      const doc = iframe.contentDocument || iframe.contentWindow.document;
      doc.open();
      doc.write(response);
      doc.close();
    }).catch(error => {
      const message = error.responseJSON?.message || 'Preview failed';
      window.FlashMessage.error(message)
    })
  })
});
