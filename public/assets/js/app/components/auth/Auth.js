import {RememberMe} from "./RememberMe.js";

export class Auth {

  constructor() {
    this.login = '#small-dialog-login'
    this.register = '#small-dialog-register'
    this.reset = '#small-dialog-reset'

    this.logout = document.getElementById('doLogout');
    this.toggleButtons = document.querySelectorAll('.toggle-password');

    this.rememberMe = new RememberMe()
  }

  async init() {
    this.handleEvents()
    await this.rememberMe.getRememberMe();

  }

  handleEvents(){
    document.querySelectorAll('.login-link').forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          this.handleLogin()
        })
    })
    document.querySelectorAll('.register-link').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        this.handleRegister()
      })
    })

    document.querySelectorAll('.reset-link').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        this.handleReset();
      })
    })

    if(this.logout !== null){
      this.logout.addEventListener('click', async (e) => {
        e.preventDefault();
        await this.handleLogout();
      })
    }


    this.toggleButtons.forEach(button => {
      button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        const icon = this.querySelector('svg');
        if (input.type === 'password') {
          input.type = 'text';
          icon.innerHTML = `<use xlink:href="#icon-eye-slash"></use>`
        } else {
          input.type = 'password';
          icon.innerHTML = `<use xlink:href="#icon-eye-open"></use>`
        }
      });
    });
  }


  handleLogin(){
    $.magnificPopup.close();
    setTimeout(() => {
      this.openPopup(this.login)
    }, 100)
  }

  handleRegister(){
    $.magnificPopup.close();
    setTimeout(() => {
      this.openPopup(this.register)
    }, 100)
  }

  handleReset(){
    $.magnificPopup.close();
    setTimeout(() => {
      this.openPopup(this.reset)
    }, 100)
  }

  openPopup(src){
    $.magnificPopup.open({
      items: {
        src: src,
        type: 'inline',
      }
    })
  }

  async handleLogout(){
      const response = await API.post('auth/logout');
      if(!response){
        throw new Error('Ошибка выхода из системы')
      }
      window.FlashMessage.success(response.message, {progress: true, timeout: 1000});
      setTimeout(() => {
            window.location.reload();
      }, 1100);
  }
}
