$(document).ready(function () {

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

})
