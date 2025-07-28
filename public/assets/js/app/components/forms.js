(function ($) {
    $.fn.initForms = function () {
        return this.each(function () {
            const $form = $(this);
            const formType = $form.data('form-type');

            if (formType === 'client-only') {
              return;
            }

            $form.on('submit',  function (e) {
                e.preventDefault();
                const submitBtn = $form.find('[type="submit"]');
                const originalBtnHtml = submitBtn.html();

                $.ajax({
                    url: $form.attr('action'),
                    type: $form.attr('method'),
                    data: $form.serializeArray(),
                    beforeSend: function () {
                        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Подождите...')
                    },
                    success: function (res) {
                        switch (formType) {
                            case 'login':
                                if (res.status === 'success') {
                                    $.magnificPopup.close();
                                    window.FlashMessage.success(res.message, {progress: true, timeout: 1000});
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1100);
                                }
                            return;
                            case 'register':
                                if (res.status === 'success') {
                                    $.magnificPopup.close();
                                    window.FlashMessage.success(res.message, {progress: true, timeout: 2000});
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2100);
                                }
                            return;
                            case 'reset':
                              if(res.status === 'success'){
                                $.magnificPopup.close();
                                window.FlashMessage.success(res.message, {progress: true});
                              }
                            return;
                            case 'updatePassword':
                              if(res.status === 'success'){
                                window.FlashMessage.success(res.message, {progress: true, timeout: 1000});
                                setTimeout(() => {
                                  window.location.href = '/';
                                }, 1100);
                              }
                            return;
                        }
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html(originalBtnHtml)
                    },
                    error: function (xhr, status, error) {
                        if (xhr.responseJSON.errors) {
                            const firstErrorKey = Object.keys(xhr.responseJSON.errors)[0];
                            const errorMessage = xhr.responseJSON.errors[firstErrorKey];

                            window.FlashMessage.error(errorMessage);
                        } else {
                            window.FlashMessage.error(xhr.responseJSON.message || 'Произошла ошибка');
                        }
                    }
                });
            })
        })
    };
})(jQuery);
