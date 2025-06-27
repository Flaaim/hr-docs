export class PaymentTable {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    this.grid = null;
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
      {id: "yookassa_id", name: 'yookassa_id'},
      {id: "email", name: "email"},
      {id: "amount", name: 'amount'},
      {id: "status", name: 'status'},
      {
        id: "created_at",
        name: "Создан",
        formatter: (cell) => {
          const date = new Date(cell);
          const day = String(date.getDate()).padStart(2, '0');
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const year = date.getFullYear();
          return `${day}.${month}.${year}`;
        }
      },
      {
        name: "Действия",
        align: "center",
        formatter: (_, row) => {
          const paymentId = row._cells[0].data;
          return gridjs.html(
             `<button class="btn btn-danger delete-btn" data-id="${paymentId}">
                 <svg width="18" height="18" fill="currentColor">
                    <use xlink:href="#icon-trash"></use>
                </svg>
             </button>`
          );
        }
      }

    ]
  }
}
