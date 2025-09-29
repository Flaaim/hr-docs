import {Helper} from "../../../utils/Helper.js";
import {Log} from "./Log.js";

$(document).ready(function (){
  if($(".dashboard-content").data('page') !== 'log-admin-page'){
    return;
  }

  try{

    const log = new Log();
    log.init()
  }catch (Error){
    Helper.handleError(Error)
  }
})
