import { DocumentManager } from './DocumentManager.js';

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
    handleError(error);
  }

  function handleError(error) {
    console.error(error);
    const message = error.responseJSON?.message || "Произошла ошибка при загрузке документов";
    window.FlashMessage.error(message);
  }
});
