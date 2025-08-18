import {Helper} from "../../../utils/Helper.js";

export class MailingTable {
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
      {id: "email", name: "email"},
      {id: "is_unsubscribed", name: 'Отписался',
        formatter: (cell) => {
          return (cell === 1) ? 'Да' : 'Нет'
        }
      },
      {
        id: "subscription_date",
        name: "Дата подписки",
        formatter: (cell) => {
          return Helper.formatDate(cell)
        }
      }
    ]
  }
}
