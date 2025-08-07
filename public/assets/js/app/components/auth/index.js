import {Helper} from "../../utils/Helper.js";
import {Auth} from "./Auth.js";

$(document).ready(async function () {
  try {
    const auth = new Auth();
    await auth.init();

  } catch (error) {
    Helper.handleError(error)
  }
});
