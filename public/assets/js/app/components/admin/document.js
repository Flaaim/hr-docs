$(document).ready(async function () {
  if($(".dashboard-content").data('page') !== 'documents-admin-page'){
    return;
  }

  $(".edit-btn").magnificPopup({
    items: {
      src: '#small-dialog-edit-document',
      type: 'inline',
    }
  })

  try{
    const documents = await getDocuments();
    initDocumentTable(documents);
  }catch (error){
    handleError(error);
  }


  async function getDocuments() {
    const response = await API.get('documents/all');
    if (!Array.isArray(response)) {
      throw new Error('Некорректный формат ответа от сервера');
    }
    return response;
  }

  function initDocumentTable(documents){
    let grid;

    renderGrid(documents)
    populateSectionFilter(documents);
    populateTypeFilter(documents);

    document.getElementById("sectionFilter").addEventListener("change", (e) => applyFilters(documents));

    document.getElementById("typeFilter").addEventListener("change", (e) => applyFilters(documents));
    document.getElementById("resetFilter").addEventListener('click', () => resetFilters(documents))

    document.addEventListener("click", async (e) => {
      if (e.target.classList.contains("delete-btn")) {
        const documentId = e.target.dataset.id;
        const confirmDelete = confirm("Вы уверены, что хотите удалить документ?");

        if (confirmDelete) {
          try {
            await API.post(`documents/delete`, {document_id:documentId});
            window.FlashMessage.success("Документ успешно удален");

            // Обновляем таблицу после удаления
            const updatedData = await getDocuments();
            renderGrid(updatedData);
          } catch (error) {
            console.error(error);
            window.FlashMessage.error("Ошибка при удалении документа");
          }
        }
      }

      if(e.target.classList.contains("edit-btn")){
        const documentId = e.target.dataset.id
        const directionId = e.target.dataset.directionId
        const sectionId = e.target.dataset.sectionId
        const typeId = e.target.dataset.typeId
        $("#admin-edit-document-form input[name=document_id]").val(documentId);
        $.magnificPopup.open({
          items: {
            src: '#small-dialog-edit-document',
            type: 'inline'
          },
          callbacks: {
            open: function (){
              loadDocument(documentId)
              loadSections(directionId, sectionId)
              loadTypes(typeId)
            }
          }
        });
      }
    });
    async function loadDocument(documentId){
      const titleDoc = $("#title-document");
      try{

        titleDoc.prop('disabled', true);
        titleDoc.val('Загрузка...')

        const response = await API.get('documents/get', {document_id:documentId})
        titleDoc.val(response.title)

        titleDoc.prop('disabled', false);
      }catch (error){
        console.error('Ошибка загрузки разделов документов:', error);
        // Показываем сообщение об ошибке пользователю
        titleDoc.val(`Ошибка загрузки (${error.message})`);
      }
    }
    async function loadSections(directionId, selectedSectionId = null){
      const sectionDoc = $("#section-document");
      try{
        sectionDoc.prop('disabled', true);
        sectionDoc.html('<option value="">Загрузка...</option>')

        const response = await API.get('documents/sections', {direction_id:directionId})
        if (!Array.isArray(response)) {
          throw new Error('Некорректный формат ответа от сервера');
        }

        let htmlOptions = `<option value="0" disabled>Выберите значение</option>`;

        htmlOptions += response.map(item =>
          `<option value="${item.id}" ${item.id == selectedSectionId ? 'selected' : ''}>${item.name}</option>`
        ).join('');

        if (!response.length) {
          htmlOptions = '<option value="">Нет доступных разделов</option>';
        }

        // Вставляем опции в select
        sectionDoc.html(htmlOptions);
        sectionDoc.prop('disabled', false);

        if (selectedSectionId && !response.some(item => item.id == selectedSectionId)) {
          console.warn(`Select ID ${selectedSectionId} not found in response`);
        }
      }catch (error){
        console.error('Ошибка загрузки разделов документов:', error);
        // Показываем сообщение об ошибке пользователю
        sectionDoc.html(`<option>Ошибка загрузки (${error.message})</option>`);
      }
    }

    async function loadTypes(selectedTypeId = null){
      const typeDoc = $("#type-document");
      try{

        typeDoc.prop('disabled', true);
        typeDoc.html('<option value="">Загрузка...</option>')

        const response = await API.get('documents/types')
        if (!Array.isArray(response)) {
          throw new Error('Некорректный формат ответа от сервера');
        }
        let htmlOptions = `<option value="0" disabled>Выберите значение</option>`;

        htmlOptions += response.map(item =>
          `<option value="${item.id}" ${item.id == selectedTypeId ? 'selected' : ''}>${item.name}</option>`
        ).join('');

        if (!response.length) {
          htmlOptions = '<option value="">Нет доступных разделов</option>';
        }

        // Вставляем опции в select
        typeDoc.html(htmlOptions);
        typeDoc.prop('disabled', false);

        if (selectedTypeId && !response.some(item => item.id == selectedTypeId)) {
          console.warn(`Type ID ${selectedTypeId} not found in response`);
        }
      }catch (error){
        console.error('Ошибка загрузки разделов документов:', error);
        // Показываем сообщение об ошибке пользователю
        typeDoc.html(`<option>Ошибка загрузки (${error.message})</option>`);
      }
    }
    function applyFilters(data){
      const sectionId = document.getElementById("sectionFilter").value;
      const typeId = document.getElementById("typeFilter").value;

      const filtered = data.filter(row => {
        const matchesSection = !sectionId || String(row.section_id) === sectionId;
        const matchesType = !typeId || String(row.type_id) === typeId;
        return matchesSection && matchesType;
      })
      renderGrid(filtered);
    }

    function getUniqueValues(data, idKey, nameKey){
      const uniqueMap = new Map();
      data.forEach(row => {
        if (!uniqueMap.has(row[idKey])) {
          uniqueMap.set(row[idKey], {
            id: row[idKey],
            name: row[nameKey]
          });
        }
      });
      return Array.from(uniqueMap.values());
    }

    function populateSectionFilter(data) {
      const select = document.getElementById("sectionFilter");
      select.innerHTML = '<option value="" selected>Все разделы</option>';

      const sections = getUniqueValues(data, "section_id", "section_name");
      sections.forEach(section => {
      const option = new Option(section.name, section.id);
      select.appendChild(option);
      });
    }
    function populateTypeFilter(data) {
      const select = document.getElementById("typeFilter");
      select.innerHTML = '<option value="" selected>Все типы</option>';

      const types = getUniqueValues(data, "type_id", "type_name");
      types.forEach(type => {
        const option = new Option(type.name, type.id);
        select.appendChild(option);
      });
    }
    function renderGrid(data){
        if(grid) grid.destroy();
        grid = new gridjs.Grid({
          columns: [
            {id: "id", name: "Id", hidden: true},
            {id: "direction_id", name: "directionId", hidden: true},
            {id: "section_id", name: "sectionId", hidden: true},
            {id: "type_id", name: "typeId", hidden: true},
            {
              id: "title",
              name: "Наименование",
              formatter: (cell, row) => {
                const documentId = row._cells[0].data;
                return gridjs.html(`<a href="/document/${documentId}">${cell}</a>`);
              }
            },
            {id: "stored_name", name: "Файл"},
            {id: "direction_name", name: "direction_name"},
            "section_name",
            "type_name",
            {
              name: "Удалить",
              align: "center",
              formatter: (_, row) => {
                const documentId = row._cells[0].data;
                const directionId = row._cells[1].data
                const sectionId = row._cells[2].data
                const typeId = row._cells[3].data
                return gridjs.html(
                  `<button class="btn btn-secondary edit-btn" data-type-id="${typeId}"
                    data-section-id="${sectionId}" data-direction-id="${directionId}" data-id="${documentId}"><i class="fas fa-edit"></i></button>
                   <button class="btn btn-danger delete-btn" data-id="${documentId}">X</button>`
                );
              }
            }
          ],
          data: data,
          search: true,
          sort: true,
          pagination: {enabled: true, limit: 20}
        }).render(document.getElementById("documents_table"));
      }

    function resetFilters(data){
      $("#typeFilter option[selected]").prop('selected', true);
      $("#sectionFilter option[selected]").prop('selected', true);
      renderGrid(data);
    }
  }

  function handleError(error) {
    console.error(error);
    const message = error.responseJSON?.message || "Произошла ошибка при загрузке документов";
    window.FlashMessage.error(message);
  }

})
