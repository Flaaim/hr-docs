import {Download} from "./Download.js";
import {Helper} from "../../../utils/Helper.js";
import {Auth} from "../../auth/Auth.js";


$(document).ready(function (){
  if( $('.document-dashboard').data('page') !== 'document-page'){
    return;
  }

  try{
    const documentId = document.getElementById('doDownload')?.dataset.id;
    if(!documentId?.trim()){
      throw new Error('Ошибка: ID документа отсутствует или пустой');
    }
    const download = new Download(documentId);
    download.handleEvents()
  }catch (error){
    Helper.handleError(error)
  }

})
