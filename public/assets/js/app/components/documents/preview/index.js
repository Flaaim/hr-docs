import {Helper} from "../../../utils/Helper.js";
import {PreviewDocument} from "./PreviewDocument.js";

$(document).ready(async function (){
  if($('.document-dashboard').data('page') !== 'document-page'){
    return;
  }

  try{
    const documentType = document.getElementById('previewContainer')?.dataset.type;
    const documentId = document.getElementById('previewContainer')?.dataset.id;

    if(documentType !== 'pdf'){
      if(!documentId?.trim()){
        Helper.handleError('Ошибка: ID документа отсутствует или пустой')
      }
      const preview = new PreviewDocument(documentId);
      await preview.preview();
      preview.blockSelectionAndCopy();
    }


  }catch (error){
    Helper.handleError(error)
  }
});
