import {SelectFilter} from "./SelectFilter.js";

export class DocumentFilters {
  constructor() {
    this.sectionFilter = new SelectFilter(
      document.getElementById("sectionFilter"),
      {allItemsText: "Все разделы"}
    );

    this.typeFilter = new SelectFilter(
      document.getElementById("typeFilter"),
      { allItemsText: "Все типы" }
    );

  }
  populate(documents) {
    this.sectionFilter.populate(documents, "section_id", "section_name");
    this.typeFilter.populate(documents, "type_id", "type_name");
  }

  getFilters() {
    return {
      sectionId: this.sectionFilter.value,
      typeId: this.typeFilter.value
    };
  }
  getFiltered(documents) {
    const { sectionId, typeId } = this.getFilters();
    return documents.filter(row => {
      const matchesSection = !sectionId || String(row.section_id) === sectionId;
      const matchesType = !typeId || String(row.type_id) === typeId;
      return matchesSection && matchesType;
    });
  }

  reset() {
    this.sectionFilter.reset();
    this.typeFilter.reset();
  }
}
