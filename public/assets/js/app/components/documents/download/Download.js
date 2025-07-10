export class Download {

  constructor(documentId) {
    this.documentId = documentId;
  }

  async handleEvents() {
    document.getElementById('doDownload').addEventListener('click', (e) => {
      e.preventDefault();
      this.handleDownload();
    })
  }

  async handleDownload(){
      await API.post('documents/get-document', {document_id:this.documentId})
      .then(response => {
        if (!response?.download_url) {
          throw new Error('Invalid server response');
        }
        window.location.href = response.download_url
      }).catch(error => {
        if(error.status === 401){
          $.magnificPopup.open({
            items: {
              src: '#small-dialog-login',
              type: 'inline',
            }
          })
        }
        const message = error.responseJSON?.message || 'Download failed';
        window.FlashMessage.error(message)
      })
  }
}
