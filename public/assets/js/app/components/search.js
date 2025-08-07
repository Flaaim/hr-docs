$(document).ready(async function (){
  if($(".dashboard-content").data('page') !== 'home'){
    return;
  }

  await initSearch();

  async function initSearch() {
    try {
      const documents = await API.get('documents/all');
      const idx = lunr(function () {
      this.use(lunr.ru);
      this.ref('id');
      this.field('title', {boost: 10});

      documents.forEach(doc => {this.add(doc)})
    })

      window.searchIndex = idx;
      window.searchDocuments = documents;
      $("#search-form").on('submit', function (e){
        e.preventDefault();
        handleSearch();
      })

      function handleSearch(e) {
        try{
          const query = document.getElementById('search-input').value.trim();
          if (!query) {
            window.FlashMessage.error('Введите поисковый запрос!')
            return;
          }

          const results = idx.search(query);
          const limitedResults = results.slice(0, 20);

          const resultContainer = $("#search-result");
          if (results.length === 0) {
            window.FlashMessage.error('Ничего не найдено...')
            resultContainer.empty();

          } else {
            let html = '<ul class="list-group list-group-numbered">';
            limitedResults.forEach(result => {
              const doc = window.searchDocuments.find(d => d.id == result.ref);
              if (doc?.title) {
                html += `
              <li class="list-group-item" >
                <a href="/document/${doc.id}">${doc.title}</a>
              </li>`
              }
            });
            html += '</ul>';
            resultContainer.html(html);
          }
        }catch (error){
          console.error('Search error:', error);
          window.FlashMessage.error('Ошибка при выполнении поиска');
        }
      }
    }catch (error) {
      console.error('Ошибка при инициализации поиска:', error);
      window.FlashMessage.error('Произошла ошибка при загрузке поиска');
    }
  }
})

