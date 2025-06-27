import {PaymentManager} from "./PaymentManager.js";
import {Helper} from "../../../utils/Helper.js";

$(document).ready(async function () {
  if ($(".dashboard-content").data('page') !== 'payments-admin-page') {
    return;
  }

  try {
    const paymentManager = new PaymentManager();
    await paymentManager.init();
  } catch (error) {
    Helper.handleError(error)
  }
})
