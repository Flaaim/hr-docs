export class Helper {

  static handleError(error){
    const message = error.responseJSON?.message || error.message || "Произошла непредвиденная ошибка";
    window.FlashMessage.error(message);
  }

  static setLoading(element, text) {
    element.disabled = true;
    element.value = (text || 'Загрузка...')
  }

  static formatDate(dateString){
    const date = new Date(dateString);

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Месяцы начинаются с 0
    const year = date.getFullYear();

    return `${day}.${month}.${year}`;
  }
}
