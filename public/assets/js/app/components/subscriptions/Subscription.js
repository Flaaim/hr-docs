import {SubscriptionCard} from './SubscriptionCard.js'
export class Subscription {

  constructor(containerId) {
    this.containerId = containerId
    this.cards = new SubscriptionCard()

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
