import {Subscription} from './Subscription.js'
import {Helper} from "../../utils/Helper.js";

$(document).ready(function(){
  if($('.user-dashboard').data('page') !== 'users-dashboard'){
    return;
  }

  try{
    const subscription = new Subscription();
    subscription.initHandlers();
  }catch (error){
    Helper.handleError(error)
  }



})
