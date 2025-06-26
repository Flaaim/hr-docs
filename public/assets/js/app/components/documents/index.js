import { DocumentManager } from "./DocumentManager.js";
import { Helper } from '../../utils/Helper.js'
$(document).ready(async function () {
  if ($(".dashboard-content").data('page') !== 'documents-page') {
    return;
  }

  try {
    const DocumentManger = new DocumentManager();
    await DocumentManger.init()
  } catch (error) {
    Helper.handleError(error)
  }


})
