import {SelectFilter} from "./SelectFilter.js";

export class UserEditFilters {
  constructor() {
   this.planUserFilter = new SelectFilter(
     document.getElementById('plan-user'),
     {allItemsText: "Выберите значение"}
   )

  }

  populatePlanUser(data, selectedId){
    this.planUserFilter.populate(data, 'slug', 'name', selectedId);
  }

}
