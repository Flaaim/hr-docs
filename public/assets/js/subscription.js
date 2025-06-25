$(document).ready(function () {

    $('.subscription-form').magnificPopup({
      items: {
        src: '#small-dialog-subscription',
        type: 'inline',
      }, callbacks: {
        open: function (){
          loadPlans()
        }
      }
    })

    $(".subscription-content").on('click', '[id^="do"]', function (){
      const button = $(this);
      button.prop('disabled', true);

      API.post('payment/create', {slug: $(this).data('slug')})
        .then(response => {
          if(response.status === 'success'){
            window.location.href = response.redirect_url
          }
        }).catch(error => {
          window.FlashMessage.error(error.responseJSON?.message)
      })

  })

  async function loadPlans(){
      try{
        const {plans, current_plan} = await API.get('subscriptions/all-with-current')
        const subscriptionContent = $(".subscription-content");




        if (!Array.isArray(plans)) {
          throw new Error('Некорректный формат ответа от сервера');
        }


        let htmlCards = plans.map((item) => ((current_plan) => {
          let priceText = item.price === "0.00" ? "Бесплатно" :
            item.slug === "monthly" ? `${item.price} рублей в месяц` :
              item.slug === "annual" ? `${item.price} рублей в год` :
                `${item.price} рублей`;

          let buttonHtml = item.slug === "free" ? '' :
            `<div class="d-flex justify-content-between align-items-center mt-3">
                  <button id="doUpgrade" data-slug="${item.slug}" class="btn btn-sm btn-outline-primary ${item.id === current_plan?.plan_id ? 'disabled' : ''}">Приобрести</button></div>`;

                    return `
                    <div class="card m-2 shadow-sm ${item.id === current_plan?.plan_id ? 'active-plan' : ''}" style="width: 14rem; border-radius: 8px; transition: transform 0.2s;">
                        <div class="card-body">
                            <h5 class="card-title">${item.name}</h5>
                            <p>${item.description}</p>
                            <p>${item.id === current_plan?.plan_id ? `
                            <div class="current-badge">
                            <i class="fas fa-check-circle"></i>${(current_plan.ends_at === null) ? 'Активен' : 'Активен до ' + formatDate(current_plan.ends_at)}</div>` : ''}</p>
                            <span class="badge bg-light text-dark mb-2" style="font-size: 0.8rem;">${priceText}</span>
                            ${buttonHtml}
                        </div>
                    </div>`;
        })(current_plan)).join('')

        subscriptionContent.html(htmlCards);
      }catch (error){
        console.error('Ошибка загрузки разделов документов:', error);
        // Показываем сообщение об ошибке пользователю
        $(".subscription-content").html('<div class="alert alert-danger">Ошибка загрузки данных</div>');
      }
  }
  function formatDate(dateString){
    const date = new Date(dateString);

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Месяцы начинаются с 0
    const year = date.getFullYear();

    return `${day}.${month}.${year}`;
  }
})
