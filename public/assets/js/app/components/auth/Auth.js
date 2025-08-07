import {RememberMe} from "./RememberMe.js";

export class Auth {

  static AUTHORIZED = true
  static UNAUTHORIZED = false;

  constructor() {
    this.login = '#small-dialog-login'
    this.register = '#small-dialog-register'
    this.reset = '#small-dialog-reset'

    this.logout = document.getElementById('doLogout');
    this.toggleButtons = document.querySelectorAll('.toggle-password');

    this.rememberMe = new RememberMe()

    this.userPanel = document.getElementById('user-panel')
    this.isAuthenticated = this.userPanel.dataset.authenticated === 'true'

  }

  async init() {
    this.handleEvents()

    if(!this.isAuthenticated){
      const result = await this.rememberMe.getRememberMe();
      if(result?.status === 'success') {
        this.toggleAuthState(Auth.AUTHORIZED);
      }else {
        this.toggleAuthState(Auth.UNAUTHORIZED)
      }
    }else {
      this.toggleAuthState(Auth.AUTHORIZED);
    }



    }

  toggleAuthState(isAuthenticated) {
    this.isAuthenticated = isAuthenticated;
    if (isAuthenticated) {
      this.userPanel.classList.add('authenticated');
      this.userPanel.classList.remove('unauthenticated');
    } else {
      this.userPanel.classList.add('unauthenticated');
      this.userPanel.classList.remove('authenticated');
    }
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
  changeUserPanel(){
    document.getElementById('user-panel').
      innerHTML = `<li class="nav-item"><a class="nav-link" href="/user/dashboard">Профиль</a></li>
                            <li class="nav-item"><button class="btn btn-link nav-link" id="doLogout">Выход</button></li>`
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
