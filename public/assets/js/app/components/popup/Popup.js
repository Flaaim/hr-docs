export class Popup {

  constructor() {
    this.SHOW_INTERVAL = 2 * 24 * 60 * 60 * 1000; // 2 дня
  }
  open(elementId){
    $.magnificPopup.open({
      items: {
        src:  elementId,
        type: 'inline'
      }
    })
  }

  multipleOpen(elementId)
  {
    if(this.shouldShowPopup(elementId)){
      this.open(elementId)
      this.setPopupShown(elementId)
    }
  }
  close(){}

  shouldShowPopup(elementId){
    const popupData = localStorage.getItem(elementId);

    if (!popupData) return true;

    try{
      const data = JSON.parse(popupData);
      const now = Date.now();
      return (now - data.lastShown) > this.SHOW_INTERVAL;
    }catch{
      return true;
    }
  }
  setPopupShown(elementId){
    const popupData = JSON.stringify({
      lastShown: Date.now()
    })
    localStorage.setItem(elementId, popupData);
  }
  resetPopupTimer(elementId) {
    localStorage.removeItem(elementId);
  }
}
