export class SelectFilter {
  constructor(selectElement, { allItemsText = 'Все', defaultValue = '' } = {}) {
    this.selectElement = selectElement;
    this.config = { allItemsText, defaultValue };
  }
  populate(items, idKey, nameKey, selectedId = null) {
    this.clearOptions();
    this.addOption(
      this.config.allItemsText,
      this.config.defaultValue,
      selectedId === null || selectedId === this.config.defaultValue
    );

    const uniqueItems = this.getUniqueValues(items, idKey, nameKey);
    uniqueItems.forEach(item => {
      this.addOption(item[nameKey],
        item[idKey],
        String(item[idKey]) === String(selectedId)
      );
    });
  }

  clearOptions() {
    this.selectElement.innerHTML = '';
  }

  addOption(text, value, isSelected = false) {
    const option = new Option(text, value);
    option.selected = isSelected;
    this.selectElement.appendChild(option);
  }

  getUniqueValues(data, idKey, nameKey) {
    const uniqueMap = new Map();
    data.forEach(item => {
      if (!uniqueMap.has(item[idKey])) {
        uniqueMap.set(item[idKey], {
          [idKey]: item[idKey],
          [nameKey]: item[nameKey]
        });
      }
    });
    return Array.from(uniqueMap.values());
  }

  get value() {
    return this.selectElement.value;
  }

  reset() {
    this.selectElement.value = this.config.defaultValue;
  }
}
