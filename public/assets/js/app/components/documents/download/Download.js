import {Subscription} from "../../subscriptions/Subscription.js";
import {Auth} from "../../auth/Auth.js";

export class Download {

  constructor(documentId) {
    this.documentId = documentId;
    this.subscrpition = new Subscription()
    this.auth = new Auth();
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
          this.auth.handleLogin();
        }
        if(error.status === 403){
          this.subscrpition.handleSubscription()
        }
        const message = error.responseJSON?.message || 'Download failed';
        window.FlashMessage.error(message)
      })
  }
}
