import {Token} from './Token.js'
import {Helper} from "./utils/Helper.js";

$(document).ready(async function (){
  try{
    const token = new Token();
    await token.getToken();
  }catch (error){
    Helper.handleError(error)
  }
});
