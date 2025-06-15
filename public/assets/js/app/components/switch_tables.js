$(document).ready(async function (){
  if($(".dashboard-content").data('page') !== 'documents-page'){
    return;
  }
  const staticTable = $(".static-table");
  const dynamicWrapper = $("#dynamic-table-wrapper")
  let grid = null;
  try{
    const documents = await getDocuments();

    $("#show-dynamic").on('click', function (e){
      e.preventDefault();

      $("#dynamic-table").empty();

      initDocumentTable(documents)

      staticTable.hide()
      dynamicWrapper.show()
    })

    $("#show-static").on('click', function (e){
      e.preventDefault();
      if(grid){
        grid.destroy();
        grid = null;
      }
      $(staticTable).show();
      $(dynamicWrapper).hide()
    })
  }catch (error){
    handleError(error);
  }


  async function getDocuments(){
    const direction_id = $("#show-dynamic").data('direction');
    const response = await API.get('documents/byDirection', {direction_id:direction_id});
    if (!Array.isArray(response)) {
      throw new Error('Некорректный формат ответа от сервера');
    }
    return response;
  }

  function initDocumentTable(documents){
    renderGrid(documents)
    function renderGrid(data){
      if(grid) grid.destroy();
      const container = document.getElementById("dynamic-table");
      container.innerHTML = '';
      grid = new gridjs.Grid({
        columns: [
          {id: "id", name: "Id", hidden: true},
          {
            id: "title",
            name: "Наименование",
            formatter: (cell, row) => {
              const documentId = row._cells[0].data;
              return gridjs.html(`<a href="/document/${documentId}">${cell}</a>`);
            }
          },
          {id: "section_name", name: "Раздел"},
          {id: "type_name", name: "Тип"},
          {
            id: "updated",
            name: "Дата",
            formatter: (cell) => {
              const date = new Date(cell * 1000);
              const day = String(date.getDate()).padStart(2, '0');
              const month = String(date.getMonth() + 1).padStart(2, '0');
              const year = date.getFullYear();
              return `${day}.${month}.${year}`;
            }
          },
        ],
        data: data,
        search: true,
        sort: true,
        pagination: {enabled: true, limit: 20}
      }).render(container);
    }
  }

  function handleError(error) {
    console.error(error);
    const message = error.responseJSON?.message || "Произошла ошибка при загрузке документов";
    window.FlashMessage.error(message);
  }
})
