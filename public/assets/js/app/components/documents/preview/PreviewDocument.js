
export class PreviewDocument {

  constructor(documentId) {
    this.documentId = documentId;
    this.container = document.getElementById('previewContainer');
    this.container.innerHTML = `<iframe id="docPreview" class="w-100 border rounded" style="height: 500px;"></iframe>`
    this.iframe = document.getElementById('docPreview');

    this.doc = this.iframe.contentDocument || this.iframe.contentWindow.document;


  }

  async preview(){
    const response = await API.post('documents/preview', {document_id:this.documentId})
    if (!response) {
      throw new Error('Invalid server response to preview document');
    }
    this.docWrite(response)
  }

  docWrite(response)
  {
    this.doc.open();
    this.doc.write(response);
    this.doc.close()

  }

  blockSelectionAndCopy(){
    const style = document.createElement('style');
    style.textContent = `
      * {
        user-select: none !important;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
      }
    `;
    this.doc.head.appendChild(style);

    // JavaScript: Блокировка событий
    this.doc.addEventListener('copy', (e) => {
      e.preventDefault();
      return false;
    });

    this.doc.addEventListener('contextmenu', (e) => {
      e.preventDefault();
      return false;
    });

    this.doc.addEventListener('selectstart', (e) => {
      e.preventDefault();
      return false;
    });

    // Дополнительная защита (устаревший способ, но надёжный)
    this.doc.onselectstart = () => false;
    this.doc.oncopy = () => false;
  }
}
