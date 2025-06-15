$(document).ready(function () {
  $('.upload-form').magnificPopup({
    items: {
      src: '#small-dialog-upload',
      type: 'inline',
    },
    callbacks: {
      open: function (){
        loadDirections()
      }
    }

  })

  $(".directions").change(function (e){
    e.preventDefault();
    let optionValue =  $(this).val();
    loadSections(optionValue);
  })

  $(".sections").change(function (e){
    e.preventDefault();
    loadTypes();
  })
  async function loadDirections(){
    const directionSelect = $(".directions");
    try{
      directionSelect.prop('disabled', true);
      directionSelect.html('<option value="">Загрузка...</option>')

      const response = await API.get('documents/directions');

      if (!Array.isArray(response)) {
        throw new Error('Некорректный формат ответа от сервера');
      }
      let htmlOptions = `<option value="0" disabled selected>Выберите значение</option>`;
      htmlOptions += response.map(item =>
        `<option value="${item.id}">${item.name}</option>`
      ).join('');

      if (!response.length) {
        htmlOptions = '<option value="">Нет доступных разделов</option>';
      }

      // Вставляем опции в select
      directionSelect.html(htmlOptions);
      directionSelect.prop('disabled', false);

    }catch (error){
      console.error('Ошибка загрузки разделов документов:', error);
      // Показываем сообщение об ошибке пользователю
      directionSelect.html(`<option value="">Ошибка загрузки (${error.message})</option>`);
    }
  }

  async function loadSections(optionValue){
    const sectionSelect = $(".sections");
    try{
      sectionSelect.prop('disabled', true);
      sectionSelect.html('<option value="">Загрузка...</option>');

      const response = await API.get('documents/sections', {"direction_id": optionValue});

      if (!Array.isArray(response)) {
        throw new Error('Некорректный формат ответа от сервера');
      }

      let htmlOptions = `<option value="0" disabled selected>Выберите значение</option>`;
      htmlOptions += response.map(item =>
        `<option value="${item.id}">${item.name}</option>`
      ).join('');

      if (!response.length) {
        htmlOptions = '<option value="">Нет доступных разделов</option>';
      }

      // Вставляем опции в select
      sectionSelect.html(htmlOptions);
      sectionSelect.prop('disabled', false);
    }catch (error){
      console.error('Ошибка загрузки разделов документов:', error);
      // Показываем сообщение об ошибке пользователю
      sectionSelect.html(`<option value="">Ошибка загрузки (${error.message})</option>`);
    }
  }

  async function loadTypes()
  {
    const typeSelect = $(".types");
    try{
      typeSelect.html('<option value="">Загрузка...</option>');

      const response = await API.get('documents/types')
      if (!Array.isArray(response)) {
        throw new Error('Некорректный формат ответа от сервера');
      }

      let htmlOptions = `<option value="" disabled selected>Выберите значение</option>`;
      htmlOptions += response.map(item =>
        `<option value="${item.id}">${item.name}</option>`
      ).join('');

      if (!response.length) {
        htmlOptions += '<option value="">Нет доступных разделов</option>';
      }

      typeSelect.html(htmlOptions);
      typeSelect.prop('disabled', false);

    }catch (error){
      console.error('Ошибка загрузки разделов документов:', error);
      // Показываем сообщение об ошибке пользователю
      typeSelect.html(`<option value="">Ошибка загрузки (${error.message})</option>`);
    }

  }
})
