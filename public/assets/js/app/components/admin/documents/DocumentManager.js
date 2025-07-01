import { DocumentTable } from './DocumentTable.js';
import { DocumentFilters } from '../../../utils/filters/DocumentFilters.js'
import { DocumentEditor } from './DocumentEditor.js';
import { DocumentUpload } from './DocumentUpload.js';
import {Orphaned} from "./Orphaned.js";

export class DocumentManager {
  constructor() {
    this.table = new DocumentTable("documents_table");
    this.filters = new DocumentFilters();
    this.upload = new DocumentUpload();
    this.editor = new DocumentEditor();
    this.orphaned = new Orphaned();
    this.documents = [];
  }

  async init() {
    await this.loadData();
    this.initEventHandlers();
  }

  async loadData() {
    this.documents = await this.fetchDocuments();
    this.filters.populate(this.documents);
    this.table.render(this.documents);
  }

  async fetchDocuments() {
    const response = await API.get('documents/all');
    if (!Array.isArray(response)) {
      throw new Error('Некорректный формат ответа от сервера');
    }
    return response;
  }

  initEventHandlers() {
    // Фильтры
    this.resetFilters("resetFilter")
    this.changeFilter("sectionFilter")
    this.changeFilter('typeFilter')

    /* Загрузка документа */
    document.getElementById("upload-document-btn").addEventListener('click', async (e)=> {
        await this.handleUpload(e);
    })

    document.getElementById("check-document-btn").addEventListener('click', async (e) => {
      await this.checkOrphanedFiles(e)
    })
    document.getElementById("directionUploadFilter").addEventListener('change', async (e) => {
      await this.upload.loadSections(e.target.value);
    })

    document.getElementById("sectionUploadFilter").addEventListener('change', async (e) => {
      await this.upload.loadTypes(e.target.value);
    })

    // Делегирование событий
    document.getElementById("documents_table").addEventListener("click", async (e) => {
      if (e.target.closest(".delete-btn")) {
        await this.handleDelete(e);
      } else if (e.target.closest(".edit-btn")) {
        await this.handleEdit(e);
      }
    });
  }

  async handleDelete(e) {
    const button = e.target.closest(".delete-btn");
    const documentId = button.dataset.id;

    if (confirm("Вы уверены, что хотите удалить документ?")) {
      try {
        await API.post(`documents/delete`, { document_id: documentId });
        window.FlashMessage.success("Документ успешно удален");
        await this.loadData();
      } catch (error) {
        console.warn(error);
        window.FlashMessage.error("Ошибка при удалении документа");
      }
    }
  }
  async handleEdit(e) {
    const button = e.target.closest(".edit-btn");
    const { id: documentId, directionId, sectionId, typeId } = button.dataset;

    $("#admin-edit-document-form input[name=document_id]").val(documentId);

    $.magnificPopup.open({
      items: {
        src: '#small-dialog-edit-document',
        type: 'inline'
      },
      callbacks: {
        open: async () => {
          await this.editor.loadDocument(documentId);
          await this.editor.loadSections(directionId, sectionId);
          await this.editor.loadTypes(typeId);
        }
      }
    });
  }

  async handleUpload(){
    try {
      await this.upload.loadDirections();
      $.magnificPopup.open({
        items: {
          src: '#small-dialog-upload-document',
          type: 'inline'
        }
      });
    } catch (error) {
      console.error('Ошибка при открытии попапа загрузки:', error);
      window.FlashMessage.error('Не удалось загрузить данные для формы');
    }
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
            await this.orphaned.findDocuments();
          }
        }
      });
    }catch (error){
      console.error('Ошибка при открытии попапа загрузки:', error);
      window.FlashMessage.error('Не удалось загрузить данные для формы');
    }
  }

  changeFilter(elementId){
    document.getElementById(elementId).addEventListener("change", () => {
      const filtered = this.filters.getFiltered(this.documents);
      this.table.render(filtered)
    });
  }
  resetFilters(elementId)
  {
    document.getElementById(elementId).addEventListener('click', () => this.filters.reset());
  }

}
