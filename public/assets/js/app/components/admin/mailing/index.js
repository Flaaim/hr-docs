import {Helper} from "../../../utils/Helper.js";
import {MailingManager} from "./MailingManager.js";

$(document).ready(function (){
  if($(".dashboard-content").data('page') !== 'mailing-admin-page'){
    return;
  }

  try{

    const list = new MailingManager();
    list.init()
  }catch (Error){
    Helper.handleError(Error)
  }
})
