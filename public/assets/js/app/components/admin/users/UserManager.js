import {UserTable} from "./UserTable.js";
import {UserEditor} from "./UserEditor.js";

export class UserManager {
  constructor() {
    this.table = new UserTable('users_table')
    this.editor = new UserEditor();
    this.users = [];
  }
  async init(){
    await this.loadData();
    this.initEventHandlers();
  }

  async loadData() {
    this.users = await this.fetchUsers();
    this.table.render(this.users);
  }

  async fetchUsers() {
    const response = await API.get('users/all');
    if (!Array.isArray(response)) {
      throw new Error('Некорректный формат ответа от сервера');
    }
    return response;
  }

  initEventHandlers() {
    /* Делегирование событий  */
    document.getElementById('users_table').addEventListener('click', async (e)=> {
      if(e.target.closest('.confirm-user-btn')){
        await this.handleConfirm(e)
      }else if(e.target.closest('.edit-btn')){
        await this.handleEdit(e)
      }
    })
  }

  async handleConfirm(e) {
    const button = e.target.closest('.confirm-user-btn');
    const userId = button.dataset.id

    if(confirm("Подтвердить регистрацию пользователя?")){
      try {
        await API.post('users/confirm', {user_id:userId})
        window.FlashMessage.success('Регистрация подтверждена');

        await this.loadData();
      }catch (error){
        console.warn(error);
        window.FlashMessage.error("Ошибка при подтверждении пользователя");
      }
    }
  }
  async handleEdit(e) {
    const button = e.target.closest(".edit-btn");
    const {id: userId, planSlug: planSlug} = button.dataset;

    $("#admin-edit-user-form input[name=user_id]").val(userId);
    $.magnificPopup.open({
      items: {
        src: '#small-dialog-edit-user',
        type: 'inline'
      },
      callbacks: {
        open: async () => {
          await this.editor.loadUser(userId);
          await this.editor.loadPlans(planSlug);
        }
      }
    })
  }
}
