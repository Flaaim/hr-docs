export class Helper {

  static handleError(error){
    const message = error.responseJSON?.message || "Произошла непредвиденная ошибка";
    window.FlashMessage.error(message);
  }

  static setLoading(element, text) {
    element.disabled = true;
    element.value = (text || 'Загрузка...')
  }
}
