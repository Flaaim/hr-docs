export class DocumentTable {

  constructor(containerId) {
    this.container = document.getElementById(containerId);
    this.grid = null;
    this.staticTable = document.getElementById('static-table');
    this.dynamicTable = document.getElementById('dynamic-table');
    this.dynamicWrapper = document.getElementById('dynamic-table-wrapper');
  }

  render(data){
    if (this.grid) this.grid.destroy();
    this.grid = new gridjs.Grid({
      columns: this.getColumns(),
      data: data,
      search: true,
      sort: true,
      pagination: { enabled: true, limit: 20 }
    })

    this.grid.render(this.container)
  }

  getColumns(){
    return [
      {id: "id", name: "Id", hidden: true},
      {
        id: "title",
        name: "Наименование",
        formatter: (cell, row) => {
          const documentId = row._cells[0].data;
          return gridjs.html(`<a href="/document/${documentId}" target="_blank">${cell}</a>`);
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
    ]
  }
  showStatic(){
    this.dynamicWrapper.style.display = 'none';
    this.staticTable.style.display = 'block';
  }

  showDynamic(documents){
    this.dynamicTable.innerHTML = '';
    this.render(documents)
    this.staticTable.style.display = 'none'
    this.dynamicWrapper.style.display = 'block'
  }


}
