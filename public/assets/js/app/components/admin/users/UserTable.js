export class UserTable {
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

  getColumns() {
    return [
      {id: "id", name: "Id", hidden: true},
      {id: "plan_id", name: "planID", hidden: true},
      {id: "slug", name: "planSlug", hidden: true},
      {id: "email", name: "Email"},
      {
        id: "verified",
        name: "Подтвержден",
        formatter: (cell, row) => {

          if(cell === 1){
            return 'Да'
          }else {
            const userId = row._cells[0].data;
            return gridjs.html(
              `Нет <button class="btn btn-sm btn-primary confirm-user-btn" data-id="${userId}">
            <svg width="12" height="12" fill="currentColor">
                <use xlink:href="#icon-confirm"></use>
              </svg>
            </button>`
            );
          }
        }
      },
      {id: "name", name: "План подписки"},
      {id: "created_at", name: "Создан"},
      {
        id: "ends_at",
        name: "Окончание подписки",
        formatter: (cell) => {
          if(cell === null) return 'Бесплатный'
          const date = new Date(cell);
          const day = String(date.getDate()).padStart(2, '0');
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const year = date.getFullYear();
          return `${day}.${month}.${year}`;
        }
      },
      {
        name: "Действия",
        align: 'center',
        formatter: (_, row) => {
          const userId = row._cells[0].data;
          const planId = row._cells[1].data;
          const planSlug = row._cells[2].data;
          return gridjs.html(
            `<button class="btn btn-secondary edit-btn" data-id="${userId}" data-plan-id="${planId}" data-plan-slug="${planSlug}">
               <svg width="18" height="18" fill="currentColor">
                    <use xlink:href="#icon-edit"></use>
                </svg>
             </button>`
          );
        }
      }
    ]
  }
}
