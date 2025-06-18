import { Select } from "../services/Select.js";

export class DocumentEditor{
  constructor() {
    this.titleInput = $("#title-document");
    this.sectionSelect = $("#section-document");
    this.typeSelect = $("#type-document");
    this.select = new Select();
  }

  async loadDocument(documentId) {
    try {
      this.setLoading(this.titleInput, 'Загрузка...');
      const response = await API.get('documents/get', { document_id: documentId });
      this.titleInput.val(response.title).prop('disabled', false);
    } catch (error) {
      console.error('Ошибка загрузки документа:', error);
      this.titleInput.val(`Ошибка загрузки (${error.message})`);
    }
  }

  async loadSections(directionId, selectedSectionId = null) {
    try {
      this.setLoading(this.sectionSelect, 'Загрузка...');
      const response = await API.get('documents/sections', { direction_id: directionId });
      this.select.populateSelect(this.sectionSelect, response, selectedSectionId);
    } catch (error) {
      console.error('Ошибка загрузки разделов:', error);
      this.sectionSelect.html(`<option>Ошибка загрузки (${error.message})</option>`);
    }
  }

  async loadTypes(selectedTypeId = null) {
    try {
      this.setLoading(this.typeSelect, 'Загрузка...');
      const response = await API.get('documents/types');
      this.select.populateSelect(this.typeSelect, response, selectedTypeId);
    } catch (error) {
      console.error('Ошибка загрузки типов:', error);
      this.typeSelect.html(`<option>Ошибка загрузки (${error.message})</option>`);
    }
  }



  setLoading(element, text) {
    element.prop('disabled', true).val(text || 'Загрузка...');
  }
}
