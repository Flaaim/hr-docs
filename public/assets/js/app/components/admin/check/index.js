import {CheckManager} from './CheckManager.js'
import {Helper} from "../../../utils/Helper.js";


$(document).ready(function (){
  if($('.dashboard-content').data('page') !== 'checkOrphaned-admin-page'){
    return;
  }

  try{
    const checkManager = new CheckManager();
    checkManager.init();
  }catch (error){
    Helper.handleError(error)
  }
})
