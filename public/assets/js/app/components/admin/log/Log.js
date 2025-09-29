export class Log {
  constructor() {
    this.container = document.getElementById('logOutput')
  }
  init()
  {
    this.loadData()
    this.initEventHandlers()
  }

  async loadData(){
    let logs = await this.fetchLogs()
    this.writeLog(logs)
  }
  initEventHandlers(){
    document.getElementById('doUpdateLog').addEventListener('click', async (e) => {
      await this.loadData();
    })
  }
  async fetchLogs(){
    const response = await API.get('logs/get');
    if (!String(response).length) {
      throw new Error('Некорректный формат ответа от сервера');
    }
    return response;
  }
  writeLog(data){
    this.container.innerHTML = data
  }
}
