import { DocumentManager } from './DocumentManager.js';
import { Helper } from '../../../utils/Helper.js'

$(document).ready(async function () {
  if ($(".dashboard-content").data('page') !== 'documents-admin-page') {
    return;
  }

  // Инициализация popup EDIT
  $(".edit-btn").magnificPopup({
    items: {
      src: '#small-dialog-edit-document',
      type: 'inline',
    }
  });
  // Инициализация popup UPLOAD
  $('.upload-btn').magnificPopup({
    items: {
      src: '#small-dialog-upload-document',
      type: 'inline',
    }
  })

  try {
    const documentManager = new DocumentManager();
    await documentManager.init();
  } catch (error) {
    Helper.handleError(error)
  }


});
