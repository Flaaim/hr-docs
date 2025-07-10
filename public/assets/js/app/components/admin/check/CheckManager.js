import {Orphaned} from "./Orphaned.js";
import {CheckTable} from "./CheckTable.js";
import {Helper} from "../../../utils/Helper.js";

export class CheckManager {
  constructor() {
    this.table = new CheckTable('orphaned_table')
    this.orphaned = new Orphaned();
    this.files = [];
  }

  async init(){
    await this.loadData();
    this.initEventHandlers()
  }

  async loadData() {
    try{
      this.files = await this.fetchDocuments();
      this.table.render(this.files);
    }catch (error){
      Helper.handleError(error)
    }

  }

  async fetchDocuments(){
    const response = await API.get('documents/find-lost-files');
    if (!Array.isArray(response)) {
      throw new Error('Некорректный формат ответа от сервера');
    }
    return response;
  }
  initEventHandlers(){

    document.getElementById("check-files-btn").addEventListener('click', async (e) => {
      await this.checkOrphanedFiles(e)
    })

    document.getElementById('orphaned_table').addEventListener('click', async (e) => {
      if (e.target.closest(".delete-btn")) {
        await this.handleDelete(e);
      } else if (e.target.closest(".reload-btn")) {
        await this.handleReload(e);
      }
    })
  }

  async checkOrphanedFiles(){
    try {
      $.magnificPopup.open({
        items: {
          src: '#small-dialog-orphaned-files',
          type: 'inline'
        },
        callbacks: {
          open: async () => {
            await this.orphaned.findFiles();
          }
        }
      });
    }catch (error){
      console.error('Ошибка при открытии попапа загрузки:', error);
      window.FlashMessage.error('Не удалось загрузить данные для формы');
    }
  }

  async handleDelete(e) {
    const button = e.target.closest(".delete-btn");
    const documentId = button.dataset.id;

    if (confirm("Вы уверены, что хотите удалить документ?")) {
      try {
        await API.post(`documents/delete-from-db`, { document_id: documentId });
        window.FlashMessage.success("Документ успешно удален");
        await this.loadData();
      } catch (error) {
        console.warn(error);
        window.FlashMessage.error("Ошибка при удалении документа");
      }
    }
  }

  async handleReload(e){
    const button = e.target.closest('.reload-btn');
    const storedName = button.dataset.storedName;
    $.magnificPopup.open({
      items: {
        src: '#small-dialog-reload-document',
        type: 'inline'
      },
      callbacks: {
        open: () => {
          document.getElementById('storedName').value = storedName
        }
      }

    })
  }
}
