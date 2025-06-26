import {DocumentEditFilters} from "../../../utils/filters/DocumentEditFilters.js";
import {Helper} from "../../../utils/Helper.js";

export class DocumentEditor{
  constructor() {
    this.titleInput = document.getElementById('title-document');
    this.sectionSelect = document.getElementById('section-document');
    this.typeSelect = document.getElementById('type-document');

    this.filter = new DocumentEditFilters()
  }

  async loadDocument(documentId) {
    try {
      Helper.setLoading(this.titleInput, 'Загрузка...')
      const response = await API.get('documents/get', { document_id: documentId });
      this.titleInput.value = response.title
      this.titleInput.disabled = false;
    } catch (error) {
      console.error('Ошибка загрузки документа:', error);
      this.titleInput.val(`Ошибка загрузки (${error.message})`);
    }
  }

  async loadSections(directionId, selectedSectionId = null) {
    try {
      Helper.setLoading(this.sectionSelect, 'Загрузка...')
      const response = await API.get('documents/sections', { direction_id: directionId });
      this.filter.populateSection(response, selectedSectionId)
      this.sectionSelect.disabled = false;
    } catch (error) {
      console.error('Ошибка загрузки разделов:', error);
      this.sectionSelect.html(`<option>Ошибка загрузки (${error.message})</option>`);
    }
  }

  async loadTypes(selectedTypeId = null) {
    try {
      Helper.setLoading(this.typeSelect, 'Загрузка...')
      const response = await API.get('documents/types');
      this.filter.populateType(response, selectedTypeId)
      this.typeSelect.disabled = false;
    } catch (error) {
      console.error('Ошибка загрузки типов:', error);
      this.typeSelect.html(`<option>Ошибка загрузки (${error.message})</option>`);
    }
  }
}
