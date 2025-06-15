export class DocumentEditor{
  constructor() {
    this.titleInput = $("#title-document");
    this.sectionSelect = $("#section-document");
    this.typeSelect = $("#type-document");
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
      this.populateSelect(this.sectionSelect, response, selectedSectionId);
    } catch (error) {
      console.error('Ошибка загрузки разделов:', error);
      this.sectionSelect.html(`<option>Ошибка загрузки (${error.message})</option>`);
    }
  }

  async loadTypes(selectedTypeId = null) {
    try {
      this.setLoading(this.typeSelect, 'Загрузка...');
      const response = await API.get('documents/types');
      this.populateSelect(this.typeSelect, response, selectedTypeId);
    } catch (error) {
      console.error('Ошибка загрузки типов:', error);
      this.typeSelect.html(`<option>Ошибка загрузки (${error.message})</option>`);
    }
  }

  populateSelect(select, items, selectedId = null) {
    let html = `<option value="0" disabled>Выберите значение</option>`;

    html += items.map(item =>
      `<option value="${item.id}" ${item.id == selectedId ? 'selected' : ''}>${item.name}</option>`
    ).join('');

    html = items.length ? html : '<option value="">Нет доступных вариантов</option>';
    select.html(html).prop('disabled', false);
  }

  setLoading(element, text) {
    element.prop('disabled', true).val(text || 'Загрузка...');
  }
}
