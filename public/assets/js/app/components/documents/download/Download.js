import {Subscription} from "../../subscriptions/Subscription.js";
import {Auth} from "../../auth/Auth.js";
import {Popup} from "../../popup/Popup.js";

export class Download {

  constructor(documentId) {
    this.documentId = documentId;
    this.subscrpition = new Subscription();
    this.auth = new Auth();
    this.popup = new Popup();
  }

  async handleEvents() {
    document.getElementById('doDownload').addEventListener('click', (e) => {
      e.preventDefault();
      this.handleDownload();
    })
  }

  async handleDownload(){
      // Show subscription popup if it hasn't been shown recently
      if (this.popup.shouldShowPopup('#small-dialog-subscription')) {
        this.subscrpition.handleSubscription();
        return;
      }
      try {
        const response = await API.post('documents/get-document', {document_id: this.documentId});
        if (!response?.download_url) {
          throw new Error('Invalid server response');
        }
        // Reset popup timer after successful download
        this.popup.resetPopupTimer('#small-dialog-subscription');
        window.location.href = response.download_url;
      } catch (error) {
        if (error.status === 401) {
          this.auth.handleLogin();
        }
        if (error.status === 403) {
          this.subscrpition.handleSubscription();
        }
        const message = error.responseJSON?.message || 'Download failed';
        window.FlashMessage.error(message);
      }
  }
}
