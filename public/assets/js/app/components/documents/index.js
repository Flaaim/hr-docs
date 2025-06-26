import { DocumentManager } from "./DocumentManger.js";
import { Helper } from '../../utils/Helper.js'
$(document).ready(function (){
  if($(".dashboard-content").data('page') !== 'documents-page'){
    return;
  }

  try{

  }catch (error){
    Helper.handleError(error)
  }


})
