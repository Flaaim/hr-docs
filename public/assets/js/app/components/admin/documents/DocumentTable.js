export class DocumentTable {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    this.grid = null;
  }

  render(data) {
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

  getColumns() {
    return [
      { id: "id", name: "Id", hidden: true },
      { id: "direction_id", name: "directionId", hidden: true },
      { id: "section_id", name: "sectionId", hidden: true },
      { id: "type_id", name: "typeId", hidden: true },
      {
        id: "title",
        name: "Наименование",
        formatter: (cell, row) => {
          const documentId = row._cells[0].data;
          return gridjs.html(`<a href="/document/${documentId}">${cell}</a>`);
        }
      },
      { id: "stored_name", name: "Файл" },
      { id: "direction_name", name: "Направление" },
      { id: "section_name", name: "Раздел" },
      { id: "type_name", name: "Тип" },
      {
        name: "Действия",
        align: "center",
        formatter: (_, row) => {
          const documentId = row._cells[0].data;
          const directionId = row._cells[1].data;
          const sectionId = row._cells[2].data;
          const typeId = row._cells[3].data;
          return gridjs.html(
            `<button class="btn btn-secondary edit-btn" data-type-id="${typeId}"
               data-section-id="${sectionId}" data-direction-id="${directionId}" data-id="${documentId}">
               <svg width="18" height="18" fill="currentColor">
                    <use xlink:href="#icon-edit"></use>
                </svg>
             </button>
             <button class="btn btn-danger delete-btn" data-id="${documentId}">
                 <svg width="18" height="18" fill="currentColor">
                    <use xlink:href="#icon-trash"></use>
                </svg>
             </button>`
          );
        }
      }
    ];
  }
}


