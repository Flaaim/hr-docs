import { UserManager } from "./UserManager.js";

$(document).ready(async function (){
  if($(".dashboard-content").data('page') !== 'users-admin-page'){
    return;
  }


  $(".edit-btn").magnificPopup({
    items: {
      src: '#small-dialog-edit-user',
      type: 'inline',
    }
  });

  try {
    const userManager = new UserManager();
    await userManager.init();
  } catch (error) {
    handleError(error);
  }

  function handleError(error) {
    console.error(error);
    const message = error.responseJSON?.message || "Произошла ошибка при загрузке пользователей";
    window.FlashMessage.error(message);
  }
})
