export class Helper {

  static handleError(error){
    const message = error.responseJSON?.message || "Произошла непредвиденная ошибка";
    window.FlashMessage.error(message);
  }
}
