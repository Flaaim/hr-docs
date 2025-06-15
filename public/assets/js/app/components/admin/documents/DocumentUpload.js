export class DocumentUpload {
  constructor() {
    this.directionsSelect = $(".directions");
    this.sectionsSelect = $(".sections");
    this.typesSelect = $(".types");
    this.htmlDirectionOptions = `<option value="0" disabled selected>Выберите значение</option>`;
    this.htmlSectionOptions = `<option value="0" disabled selected>Выберите значение</option>`;
    this.htmlTypeOptions = `<option value="0" disabled selected>Выберите значение</option>`;
  }

  async loadDirections(){
    try{
      this.directionsSelect.prop('disabled', true);
      this.directionsSelect.html('<option value="">Загрузка...</option>');

      const response = await API.get('documents/directions');

      if (!Array.isArray(response)) {
        throw new Error('Некорректный формат ответа от сервера');
      }

      this.htmlDirectionOptions += response.map(item =>
        `<option value="${item.id}">${item.name}</option>`
      ).join('');

      if (!response.length) {
        this.htmlDirectionOptions = '<option value="">Нет доступных разделов</option>';
      }

      // Вставляем опции в select
      this.directionsSelect.html(this.htmlDirectionOptions);
      this.directionsSelect.prop('disabled', false);
    }catch (error){
      console.error('Ошибка загрузки разделов документов:', error);
      // Показываем сообщение об ошибке пользователю
      this.directionsSelect.html(`<option value="">Ошибка загрузки (${error.message})</option>`);
    }
  }
  async loadSections(optionValue){
    try{
      this.sectionsSelect.prop('disabled', true);
      this.sectionsSelect.html('<option value="">Загрузка...</option>');

      const response = await API.get('documents/sections', {"direction_id": optionValue});

      if (!Array.isArray(response)) {
        throw new Error('Некорректный формат ответа от сервера');
      }

      this.htmlSectionOptions += response.map(item =>
        `<option value="${item.id}">${item.name}</option>`
      ).join('');

      if (!response.length) {
        this.htmlSectionOptions = '<option value="">Нет доступных разделов</option>';
      }

      // Вставляем опции в select
      this.sectionsSelect.html(this.htmlSectionOptions);
      this.sectionsSelect.prop('disabled', false);
    }catch (error){
      console.error('Ошибка загрузки секций документов:', error);
      // Показываем сообщение об ошибке пользователю
      this.sectionsSelect.html(`<option value="">Ошибка загрузки (${error.message})</option>`);
    }
  }
  async loadTypes(optionValue){
    try{
      this.typesSelect.prop('disabled', true);
      this.typesSelect.html('<option value="">Загрузка...</option>');

      const response = await API.get('documents/types')
      if (!Array.isArray(response)) {
        throw new Error('Некорректный формат ответа от сервера');
      }

      this.htmlTypeOptions += response.map(item =>
        `<option value="${item.id}">${item.name}</option>`
      ).join('');

      if (!response.length) {
        this.htmlTypeOptions += '<option value="">Нет доступных разделов</option>';
      }

      this.typesSelect.html(this.htmlTypeOptions);
      this.typesSelect.prop('disabled', false);
    }catch (error){
      console.error('Ошибка загрузки типов документа:', error);
      // Показываем сообщение об ошибке пользователю
      this.typesSelect.html(`<option value="">Ошибка загрузки (${error.message})</option>`);
    }
  }


}
