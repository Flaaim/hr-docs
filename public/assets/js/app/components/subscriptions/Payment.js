import {Helper} from "../../utils/Helper.js";

export class Payment {
  async create(slug) {
    try {
      const response = await API.post('payments/create', {slug:slug})

      if (response && response.status === 'success') {
        window.location.href = response.redirect_url;
      } else {
        throw new Error("Ошибка создания платежа...")
      }
    }catch (error){
      Helper.handleError(error)
    }



  }

}
