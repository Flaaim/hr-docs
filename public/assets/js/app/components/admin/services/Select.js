export class Select {
  populateSelect(select, items, selectedId = null) {
    let html = `<option value="0" disabled>Выберите значение</option>`;

    html += items.map(item =>
      `<option value="${item.id}" ${item.id == selectedId ? 'selected' : ''}>${item.name}</option>`
    ).join('');

    html = items.length ? html : '<option value="">Нет доступных вариантов</option>';
    select.html(html).prop('disabled', false);
  }

  populateSelectBySlug(select, items, selectedId = null) {
    let html = `<option value="0" disabled>Выберите значение</option>`;

    html += items.map(item =>
      `<option value="${item.slug}" ${item.id == selectedId ? 'selected' : ''}>${item.name}</option>`
    ).join('');

    html = items.length ? html : '<option value="">Нет доступных вариантов</option>';
    select.html(html).prop('disabled', false);
  }
}
