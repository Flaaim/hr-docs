import {Helper} from "../../utils/Helper.js";
import {Auth} from "./Auth.js";
import {RememberMe} from "./RememberMe.js";

$(document).ready(async function () {
  try {
    const auth = new Auth();
    await auth.init();

    const rememberMe = new RememberMe()
    await rememberMe.getRememberMe()
  } catch (error) {
    Helper.handleError(error)
  }
});
