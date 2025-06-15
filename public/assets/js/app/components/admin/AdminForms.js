(function ($) {
  $.fn.initAdminForms = function () {
    return this.each(function () {
      const $form = $(this);
      const formType = $form.data('form-type')

      $form.on('submit', function (e){
        e.preventDefault();
        const submitBtn = $form.find('[type="submit"]');
        const originalBtnHtml = submitBtn.html();

        const formData = new FormData(this);

        $.ajax({
          url: $form.attr('action'),
          type: $form.attr('method'),
          data: formData,
          processData: false,
          contentType: false,
          beforeSend: function () {
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Подождите...')
          },
          success: function (res){
            switch (formType){
              case 'upload-document':
                if (res.status === 'success') {
                  let message = res.message + `Файлов: ` + res.affectedRows
                  window.FlashMessage.success(message , {progress: true, timeout: 1000});
                  setTimeout(() => {
                    window.location.reload();
                  }, 1100);
                }
                return;
              case 'edit-document':
                if (res.status === 'success') {

                  window.FlashMessage.success(res.message , {progress: true, timeout: 1000});
                  setTimeout(() => {
                    window.location.reload();
                  }, 1100);

                }
                return;
            }

          },
          complete: function () {
            submitBtn.prop('disabled', false).html(originalBtnHtml)
          },error: function (xhr, status, error) {

            if (xhr.responseJSON.errors) {
              const firstErrorKey = Object.keys(xhr.responseJSON.errors)[0];
              const errorMessage = xhr.responseJSON.errors[firstErrorKey];

              window.FlashMessage.error(errorMessage);
            } else {
              window.FlashMessage.error(xhr.responseJSON.message || 'Произошла ошибка');
            }
          }
        })

      })
    })
  }
})(jQuery)
