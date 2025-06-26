import {UserEditFilters} from '../../../utils/filters/UserEditFilters.js'
import {Helper} from "../../../utils/Helper.js";

export class UserEditor {
  constructor() {
    this.userEmailInput = document.getElementById("email");
    this.planSelect = document.getElementById('plan-user');
    this.filter = new UserEditFilters();
  }

   async loadUser(userId){
    try{
      Helper.setLoading(this.planSelect, 'Загрузка...')
      const response = await API.get('users/get', { user_id: userId });
      this.userEmailInput.value = response.email;
      this.userEmailInput.disabled = true;

    }catch (error) {
      console.error('Ошибка загрузки документа:', error);
      this.userEmailInput.val(`Ошибка загрузки (${error.message})`);
    }

  }

  async loadPlans(selectedPlanSlug = null){
    try{
      Helper.setLoading(this.planSelect, 'Загрузка...')
      const response = await API.get('subscriptions/all');
      this.filter.populatePlanUser(response, selectedPlanSlug)
      this.planSelect.disabled = false
    }catch (error){
      console.error('Ошибка загрузки планов подписки:', error);
      this.planSelect.html(`<option>Ошибка загрузки (${error.message})</option>`);
    }
  }

}
