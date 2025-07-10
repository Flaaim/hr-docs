export class Token {

  constructor() {
    this.input = document.querySelectorAll('input[name="csrf_token"]');
  }
  async getToken(){
    const response = await API.get('csrf/get');
    if(!response){
      throw new Error('Ошибка загрузки CSRF-токена')
    }
    this.input.forEach(input => {
      input.value = response.token;
    })
  }
}
