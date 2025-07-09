import {Helper} from "../../../utils/Helper.js";
import {PreviewDocument} from "./PreviewDocument.js";

$(document).ready(async function (){
  if($('.document-dashboard').data('page') !== 'document-page'){
    return;
  }

  try{
    const documentId = document.getElementById('doPreview').dataset.id;
    if(documentId === undefined){
      throw new Error('Ошибка предварительного просмотра')
    }
    const preview = new PreviewDocument(documentId);
    await preview.preview();
    preview.blockSelectionAndCopy();
  }catch (error){
    Helper.handleError(error)
  }
});
