import {SubscriptionCard} from './SubscriptionCard.js'
import {Payment} from "./Payment.js";
export class Subscription {

  constructor() {
    this.cards = new SubscriptionCard()
    this.payment = new Payment()

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
          document.querySelectorAll('.doUpgrade')
            .forEach(el => el.addEventListener('click', async (e) => {
              const button = e.target;
              await this.payment.create(button.dataset.slug);
            }))
        }
      }
    })


  }

  handlePayment(){

  }

  async loadPlans(){
    try{
      const {plans, current_plan} = await API.get('subscriptions/all-with-current')
      if (!Array.isArray(plans)) {
        throw new Error('Некорректный формат ответа от сервера');
      }

      this.cards.build(plans, current_plan);
    }catch (error){
      console.error(error)
      window.FlashMessage.error('Не удалось загрузить данные формы');
    }
  }


}
