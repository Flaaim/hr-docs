export class Search {


  async search() {
    const response = await API.get('documents/all');

    if (!response) {
      throw new Error('Ошибка загрузки поиска...')
    }

    this.idx = lunr(function () {
      this.use(lunr.ru);
      this.ref('id');
      this.field('title', {boost: 10})

      response.forEach(doc => {
        this.add(doc)
      })
    })
  }

}
