export class DocumentFilters{
  constructor() {
    this.sectionFilter = document.getElementById("sectionFilter");
    this.typeFilter = document.getElementById("typeFilter");
  }

  populate(documents) {
    this.populateSectionFilter(documents);
    this.populateTypeFilter(documents);
  }

  populateSectionFilter(documents) {
    this.sectionFilter.innerHTML = '<option value="" selected>Все разделы</option>';
    const sections = this.getUniqueValues(documents, "section_id", "section_name");
    sections.forEach(section => {
      this.sectionFilter.appendChild(new Option(section.name, section.id));
    });
  }

  populateTypeFilter(documents) {
    this.typeFilter.innerHTML = '<option value="" selected>Все типы</option>';
    const types = this.getUniqueValues(documents, "type_id", "type_name");
    types.forEach(type => {
      this.typeFilter.appendChild(new Option(type.name, type.id));
    });
  }

  getUniqueValues(data, idKey, nameKey) {
    const uniqueMap = new Map();
    data.forEach(row => {
      if (!uniqueMap.has(row[idKey])) {
        uniqueMap.set(row[idKey], {
          id: row[idKey],
          name: row[nameKey]
        });
      }
    });
    return Array.from(uniqueMap.values());
  }

  getFilters() {
    return {
      sectionId: this.sectionFilter.value,
      typeId: this.typeFilter.value
    };
  }

  reset() {
    this.sectionFilter.value = "";
    this.typeFilter.value = "";
  }
}
