import {Subscription} from './Subscription.js'
import {Helper} from "../../utils/Helper.js";

$(document).ready(function(){
  if($('.user-dashboard').data('page') !== 'users-dashboard'){
    return;
  }

  /* Инициализация popup */
  $('#change-plan-btn').magnificPopup({
    items: {
      src: '#small-dialog-subscription',
      type: 'inline',
    },callbacks: {
      open: async function (){
        try{
          const subscription = new Subscription('subscription-content');
          await subscription.loadPlans()

        }catch (error){
          Helper.handleError(error)
        }
      }
    }
  })


})
