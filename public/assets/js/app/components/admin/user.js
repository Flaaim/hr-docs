$(document).ready(async function (){
  if($(".dashboard-content").data('page') !== 'users-admin-page'){
    return;
  }
  try{
    const users = await getUsers();
    initUserTable(users);
  }catch (error){
    handleError(error);
  }


  async function getUsers(){
    const response = await API.get('users/all');

    if (!Array.isArray(response)) {
      throw new Error('Некорректный формат ответа от сервера');
    }
    return response;
  }

  function initUserTable(users){
    let grid;
    renderGrid(users);



    document.addEventListener("click", async (e) => {
      if(e.target.classList.contains('confirm-user-btn')){
        const userId = e.target.dataset.id
        const confirmUser = confirm('Вы уверены что хотите подвердить регистрацию пользователя?')

        if (confirmUser){
          try{
            await API.post('users/confirm', {user_id:userId})
            window.FlashMessage.success('Пользователь подтвержден.');

            const updatedData = await getUsers();
            renderGrid(updatedData)
          }catch (error) {
            console.error(error);
            window.FlashMessage.error("Ошибка при подтверждении пользователя");
          }
        }
      }
    })

    function renderGrid(data){
      if(grid) grid.destroy()
      grid = new gridjs.Grid({
        columns: [
          {id: "id", name: "Id", hidden: true},
          {id: "email", name: "Email"},
          {
            id: "verified",
            name: "Подтвержден",
            formatter: (cell, row) => {
              if(cell === 1){
                return 'Да'
              }else {
                const userId = row._cells[0].data;
                return gridjs.html(
                  `Нет <button class="btn btn-sm btn-info confirm-user-btn" data-id="${userId}">confirm</button>`
                );
              }
            }
          },
          {id: "name", name: "План подписки"},
          {id: "created_at", name: "Создан"},
          {
            id: "ends_at",
            name: "Окончание подписки",
            formatter: (cell) => {
              if(cell === null) return 'Бесплатный'
              const date = new Date(cell);
              const day = String(date.getDate()).padStart(2, '0');
              const month = String(date.getMonth() + 1).padStart(2, '0');
              const year = date.getFullYear();
              return `${day}.${month}.${year}`;
            }
          }
        ],
        data: data,
        search: true,
        sort: true,
        pagination: {enabled: true, limit: 20}
      }).render(document.getElementById("users_table"))
    }
  }
  function handleError(error){
    console.error(error);
    const message = error.responseJSON?.message || "Произошла ошибка при загрузке документов";
    window.FlashMessage.error(message);
  }



})
