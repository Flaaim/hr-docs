import {PaymentTable} from './PaymentTable.js'



export class PaymentManager {
  constructor() {
    this.table = new PaymentTable('payments_table')
    this.payments = [];
  }

  async init(){
    await this.loadData();
    this.initEventHandlers();
  }

  async loadData() {
    this.payments = await this.fetchPayments();
    this.table.render(this.payments);
  }

  async fetchPayments() {
    const response = await API.get('payments/all');
    if (!Array.isArray(response)) {
      throw new Error('Некорректный формат ответа от сервера');
    }
    return response;
  }

  initEventHandlers() {
    /* Делегирование событий  */
    document.getElementById('payments_table').addEventListener('click', async (e)=> {
      if(e.target.closest(".delete-btn")){
        await this.handleDelete(e);
      }
    })
  }
  async handleDelete(e){
    const button = e.target.closest(".delete-btn");
    const paymentId = button.dataset.id;

    if (confirm("Вы уверены, что хотите удалить информацию о платеже?")) {
      try {
        await API.post(`payments/delete`, { payment_id: paymentId });
        window.FlashMessage.success("Платеж успешно удален");
        await this.loadData();
      } catch (error) {
        console.warn(error);
        window.FlashMessage.error("Ошибка при удалении документа");
      }
    }
  }
}
