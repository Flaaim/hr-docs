import {Helper} from "../../../utils/Helper.js";
import {PreviewDocument} from "./PreviewDocument.js";

$(document).ready(async function (){
  if($('.document-dashboard').data('page') !== 'document-page'){
    return;
  }

  try{
    const documentId = document.getElementById('doPreview')?.dataset.id;
    if(!documentId?.trim()){
      throw new Error('Ошибка: ID документа отсутствует или пустой')
    }
    const preview = new PreviewDocument(documentId);
    await preview.preview();
    preview.blockSelectionAndCopy();
  }catch (error){
    Helper.handleError(error)
  }
});
