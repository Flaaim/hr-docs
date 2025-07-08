$(document).ready(function () {
  async function getCsrfToken(){
    try{
      const response = await API.get('csrf/get');
      document.querySelectorAll('input[name="csrf_token"]').forEach(input => {
        input.value = response.token;
      });
    }catch (error){
      const message = error.responseJSON?.message || "Ошибка загрузки CSRF-токена";
      window.FlashMessage.error(message);
    }
  }
    getCsrfToken().then(r => {

    });

    $('.auth-form-login').magnificPopup({
        items: {
            src: '#small-dialog-login',
            type: 'inline',
        }
    })

    $('.auth-form-register').magnificPopup({
        items: {
            src: '#small-dialog-register',
            type: 'inline'
        }
    });

    $(".auth-form-reset").magnificPopup({
      items: {
        src: '#small-dialog-reset',
        type: 'inline'
      }
    });

    $(document).on('click', '#doLogout', function (e) {
        e.preventDefault();
        API.logout().then(
        ).catch(error => console.error('Logout failed', error))
    })

    const toggleButtons = document.querySelectorAll('.toggle-password');

    toggleButtons.forEach(button => {
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
})
