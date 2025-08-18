import {MailingTable} from './MailingTable.js'

export class MailingManager{

  constructor() {
    this.table = new MailingTable('mailing_table')
    this.list = [];
  }

  async init(){
    await this.loadData();
    //this.initEventHandlers();
  }

  async loadData() {
    this.list = await this.FetchUserMailingList()
    this.table.render(this.list);
  }

  async FetchUserMailingList() {
    const response = await API.get('mailing/list');
    if (!Array.isArray(response)) {
      throw new Error('Некорректный формат ответа от сервера');
    }
    return response;
  }
}
