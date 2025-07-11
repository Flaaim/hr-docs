import {SubscriptionCard} from './SubscriptionCard.js'
export class Subscription {

  constructor() {
    this.cards = new SubscriptionCard()

  }
  initHandlers(){
    document.getElementById('change-plan-btn').addEventListener('click', (e) => {
      this.handleSubscription();
    })
  }

  handleSubscription(){
    $.magnificPopup.open({
      items: {
        src: '#small-dialog-subscription',
        type: 'inline',
      },callbacks: {
        open: async  () => {
          await this.loadPlans()
        }
      }
    })
  }



  async loadPlans(){
    try{
      const {plans, current_plan} = await API.get('subscriptions/all-with-current')
      if (!Array.isArray(plans)) {
        throw new Error('Некорректный формат ответа от сервера');
      }
      this.cards.build(plans, current_plan);
    }catch (error){
      window.FlashMessage.error('Не удалось загрузить данные формы');
    }
  }


}
