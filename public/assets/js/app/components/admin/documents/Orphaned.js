export class Orphaned {
  constructor() {
    this.item = ''
    this.list = document.getElementById('listOrphanedFiles');
    this.manage = document.getElementById('manageOrphanedFiles');
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
       return `<li class="list-group-item">${item}<button class="btn btn-danger doDeleteOrphaned" data-filename="${item}"><svg width="12" height="12" fill="currentColor">
            <use xlink:href="#icon-trash"></use></svg></button></li>`
    }).join('')
    if(!files.length) {
      this.item = 'Файлы не найдены...'
      this.list.innerHTML = this.item;
      return;
    }

    this.list.innerHTML = this.item;
  }

  async deleteOrphaned(filename){
    try{
      await API.post('documents/delete-orphaned-file', {filename:filename}).then(response => {
        if(response.status === 'success'){
          window.FlashMessage.success(response.message, {progress: true, timeout: 1000});
          setTimeout(() => {
            window.location.reload();
          }, 1100);

        }
      })
    }catch (error){
      console.warn(error)
      window.FlashMessage.error('Не удалось удалить файл');
    }
  }
}
