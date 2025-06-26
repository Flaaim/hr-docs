import { UserManager } from "./UserManager.js";
import { Helper } from '../../../utils/Helper.js'

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
    Helper.handleError(error);
  }

})
