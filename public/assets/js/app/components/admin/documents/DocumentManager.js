import { DocumentTable } from './DocumentTable.js';
import { DocumentFilters } from './DocumentFilters.js';
import { DocumentEditor } from './DocumentEditor.js';

export class DocumentManager {
  constructor() {
    this.table = new DocumentTable("documents_table");
    this.filters = new DocumentFilters();
    this.editor = new DocumentEditor();
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
    document.getElementById("sectionFilter").addEventListener("change", () => this.applyFilters());
    document.getElementById("typeFilter").addEventListener("change", () => this.applyFilters());
    document.getElementById("resetFilter").addEventListener('click', () => this.resetFilters());

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
  applyFilters() {
    const { sectionId, typeId } = this.filters.getFilters();
    const filtered = this.documents.filter(row => {
      const matchesSection = !sectionId || String(row.section_id) === sectionId;
      const matchesType = !typeId || String(row.type_id) === typeId;
      return matchesSection && matchesType;
    });
    this.table.render(filtered);
  }
  resetFilters() {
    this.filters.reset();
    this.table.render(this.documents);
  }
}
