import {Helper} from "../../utils/Helper.js";

export class SubscriptionCard {
  constructor() {
    this.content = document.getElementById('subscription-content')
    this.plans = {}
    this.currentPlan = {}
  }

  render(cards){
    this.content.innerHTML = cards;
  }

  build(plans, currentPlan){
    this.plans = plans;
    this.currentPlan = currentPlan;

    const htmlCards  = this.buildPriceText()
      .buildButton()
      .buildCards();

    this.render(htmlCards)
  }

  buildButton(){
    this.plans.forEach(plan => {
      if(plan.slug === "free"){
        plan.htmlButton = '';
      }else{
        plan.htmlButton = `<div class="d-flex justify-content-between align-items-center mt-3">
      <button data-slug="${plan.slug}" class="btn btn-outline-primary doUpgrade ${plan.id === this.currentPlan?.plan_id ? 'disabled' : ''}">Приобрести</button></div>`
      }
    })
    return this;
  }

  buildPriceText(){
    this.plans.forEach(plan => {
      if(plan.slug === 'free'){
        plan.priceText = `Бесплатно`;
      }else if(plan.slug === 'monthly'){
        plan.priceText = `${plan.price} рублей в месяц`;
      }else if(plan.slug === 'annual'){
        plan.priceText = `${plan.price} рублей в год`
      }else if(plan.slug === 'eternal'){
        plan.priceText = `${plan.price} рублей`
      }else if(plan.slug === 'one-time'){
        plan.priceText = `${plan.price} рублей`
      }
    })
    return this;
  }

  buildCards(){
    let currentPlan = this.currentPlan;
    return this.plans.map((item) => ((currentPlan) => {
        return `<div class="card m-2 shadow-sm ${item.id === currentPlan?.plan_id ? 'active-plan' : ''}" style="width: 16rem; border-radius: 8px; transition: transform 0.2s;">
            <div class="card-body">
                <h5 class="card-title">${item.name}</h5>
                <p>${item.description}</p>
                <p>${item.id === currentPlan?.plan_id ? `
                <div class="current-badge">
                <i class="fas fa-check-circle"></i>${(currentPlan.ends_at === null) ? 'Активен' : 'Активен до ' + Helper.formatDate(currentPlan.ends_at)}</div>` : ''}</p>
                <span class="badge bg-light text-dark mb-2" style="font-size: 0.8rem;">${item.priceText}</span>
                ${item.htmlButton}
            </div>
        </div>`;
    })(currentPlan)).join('')
  }

}
