import {Helper} from "../../utils/Helper.js";

$(document).ready(function (){
  if($(".dashboard-content").data('page') !== 'home'){
    return;
  }


  try{

  }catch (error){
    Helper.handleError(error)
  }

});
