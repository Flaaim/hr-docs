import {Helper} from "../../utils/Helper.js";
import {Auth} from "./Auth.js";

$(document).ready(function (){
    try {
      const auth = new Auth();
      auth.init();
    }catch (error){
      Helper.handleError(error)
    }
});
