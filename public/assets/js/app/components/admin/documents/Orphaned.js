export class Orphaned {
  constructor() {
    this.item = ''
    this.list = document.getElementById('listOrphanedFiles');
    this.manage = document.getElementById('manageOrphanedFiles');
    this.deleteBtn = `<button class="btn btn-danger" id="doDeleteOrphaned">
        <svg width="18" height="18" fill="currentColor">
            <use xlink:href="#icon-trash"></use></svg>Удалить
            </button>`
  }

  async findDocuments() {
    try {
      const response = await API.post('documents/check-orphaned-files');
      if (!Array.isArray(response)) {
        throw new Error('Некорректный формат ответа от сервера');
      }

      this.showHtml(response)
    } catch (error) {
      console.warn(error)
      window.FlashMessage.error('Не удалось загрузить данные');
    }
  }

  showHtml(files){

    this.item = files.map(item => {
       return `<li class="list-group-item">${item}</li>`
    }).join('')
    if(!files.length) {
      this.item = 'Файлы не найдены...'
      this.list.innerHTML = this.item;
      this.manage.innerHTML = '';
      return;
    }

    this.list.innerHTML = this.item;
    this.manage.innerHTML = this.deleteBtn;

  }

}
