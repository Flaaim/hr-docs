import {Select} from "../services/Select.js";

export class UserEditor {
  constructor() {
    this.userEmailInput = $("#email");
    this.planSelect = $("#plan-user");
    this.select = new Select();
  }

   async loadUser(userId){
    try{
      this.setLoading(this.userEmailInput, 'Загрузка')
      const response = await API.get('users/get', { user_id: userId });
      this.userEmailInput.val(response.email).prop('disabled', true);
    }catch (error) {
      console.error('Ошибка загрузки документа:', error);
      this.userEmailInput.val(`Ошибка загрузки (${error.message})`);
    }

  }

  async loadPlans(selectedPlanId = null){
    try{
      this.setLoading(this.planSelect, 'Загрузка');
      const response = await API.get('subscriptions/all');
      this.select.populateSelectBySlug(this.planSelect, response, selectedPlanId)
    }catch (error){
      console.error('Ошибка загрузки планов подписки:', error);
      this.planSelect.html(`<option>Ошибка загрузки (${error.message})</option>`);
    }
  }

  setLoading(element, text) {
    element.prop('disabled', true).val(text || 'Загрузка...');
  }
}
