export class CheckTable {
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
      { id: "stored_name", name: "storedName", hidden: true },
      {
        id: "title",
        name: "Наименование",
        formatter: (cell, row) => {
          const documentId = row._cells[0].data;
          return gridjs.html(`<a href="/document/${documentId}">${cell}</a>`);
        }
      },
      { id: "stored_name", name: "Файл" },
      {
        name: "Действия",
        align: "center",
        formatter: (_, row) => {
          const documentId = row._cells[0].data;
          const storedName = row._cells[1].data;
          return gridjs.html(
            `<button class="btn btn-secondary reload-btn"
                data-stored-name="${storedName}">
               <svg width="18" height="18" fill="currentColor">
                    <use xlink:href="#icon-upload"></use>
                </svg>
             </button>
             <input type="file" class="reload-input" style="display: none;">
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


