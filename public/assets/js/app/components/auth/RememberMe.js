export class RememberMe {
  async getRememberMe(){
    const response = await API.get('auth/checkRememberMe')

    if(!response){
      throw new Error('Ошибка загрузка RememberMe токена')
    }
  }

}
