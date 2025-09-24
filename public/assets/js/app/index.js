import {Token} from './Token.js'
import {Helper} from "./utils/Helper.js";
import {Popup} from "./components/popup/Popup.js";

$(document).ready(async function (){
  try{
    const token = new Token();
    const popup = new Popup();
    await token.getToken();

    popup.multipleOpen('#small-dialog-main')


  }catch (error){
    Helper.handleError(error)
  }
});
