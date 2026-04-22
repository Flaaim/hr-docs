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
        const isActive = plan.id === this.currentPlan?.plan_id;
        plan.htmlButton = `
          <button
            data-slug="${plan.slug}"
            class="plan-buy-btn doUpgrade ${isActive ? 'disabled' : ''}"
            ${isActive ? 'disabled' : ''}
          >
            ${isActive ? '<i class="fas fa-check me-1"></i>Активен' : '<i class="fas fa-bolt me-1"></i>Выбрать'}
          </button>`;
      }
    })
    return this;
  }

  buildPriceText(){
    this.plans.forEach(plan => {
      if(plan.slug === 'free'){
        plan.priceText = `Бесплатно`;
        plan.priceAmount = '0';
        plan.priceUnit = '';
        plan.icon = '🎁';
        plan.isBestValue = false;
      }else if(plan.slug === 'monthly'){
        plan.priceText = `${plan.price} ₽ / месяц`;
        plan.priceAmount = plan.price;
        plan.priceUnit = '/ мес';
        plan.icon = '📅';
        plan.isBestValue = false;
      }else if(plan.slug === 'annual'){
        plan.priceText = `${plan.price} ₽ / год`;
        plan.priceAmount = plan.price;
        plan.priceUnit = '/ год';
        plan.icon = '🗓️';
        plan.isBestValue = false;
      }else if(plan.slug === 'eternal'){
        plan.priceText = `${plan.price} ₽ навсегда`;
        plan.priceAmount = plan.price;
        plan.priceUnit = 'навсегда';
        plan.icon = '♾️';
        plan.isBestValue = true;
      }else if(plan.slug === 'one-time'){
        plan.priceText = `${plan.price} ₽`;
        plan.priceAmount = plan.price;
        plan.priceUnit = '';
        plan.icon = '⚡';
        plan.isBestValue = false;
      }
    })
    return this;
  }

  buildCards(){
    let currentPlan = this.currentPlan;
    return this.plans
      .filter(item => item.slug !== 'free' && item.slug !== 'one-time')
      .map((item) => ((currentPlan) => {
        const isActive = item.id === currentPlan?.plan_id;
        const activeInfo = isActive
          ? `<div class="plan-active-badge">
               <i class="fas fa-check-circle me-1"></i>
               ${currentPlan.ends_at === null ? 'Активен' : 'Активен до ' + Helper.formatDate(currentPlan.ends_at)}
             </div>`
          : '';
        const bestBadge = item.isBestValue
          ? `<div class="plan-best-badge">Выгодно</div>`
          : '';

        return `
          <div class="plan-card ${isActive ? 'plan-card--active' : ''} ${item.isBestValue ? 'plan-card--best' : ''}">
            ${bestBadge}
            <div class="plan-card__icon">${item.icon}</div>
            <div class="plan-card__name">${item.name}</div>
            <div class="plan-card__price">
              <span class="plan-card__amount">${item.priceAmount}</span>
              <span class="plan-card__currency">₽</span>
              <span class="plan-card__unit">${item.priceUnit}</span>
            </div>
            <p class="plan-card__desc">${item.description}</p>
            ${activeInfo}
            ${item.htmlButton}
          </div>`;
      })(currentPlan)).join('')
  }
}
