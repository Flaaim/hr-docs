import {SelectFilter} from "./SelectFilter.js";

export class DocumentEditFilters {
  constructor() {
    this.sectionFilter = new SelectFilter(
      document.getElementById('section-document'),
      {allItemsText: "Выберите значение"}
    )

    this.typeFilter = new SelectFilter(
      document.getElementById('type-document'),
      {allItemsText: "Выберите значение"}
    )
  }

  populateSection(data, selectedId = null){
    this.sectionFilter.populate(data, 'id', 'name', selectedId);
  }

  populateType(data, selectedId = null){
    this.typeFilter.populate(data, 'id', 'name', selectedId)
  }

}
