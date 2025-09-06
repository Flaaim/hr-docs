import { DocumentTable } from './DocumentTable.js'
import {DocumentFilters} from "../../utils/filters/DocumentFilters.js";
import {Direction} from "./Direction.js"
export class DocumentManager {

  constructor() {
    this.table = new DocumentTable('dynamic-table')
    this.filters = new DocumentFilters()
    this.direction_id = (new Direction()).getDirectionId()
    this.documents = []
  }

  async init(){
    await this.loadData();
    this.initEventHandlers();
  }

  initEventHandlers(){
    document.getElementById('show-static').addEventListener('click', (e) => {
      e.preventDefault()
      this.filters.reset();
      this.table.showStatic();
    })

    document.getElementById('show-dynamic').addEventListener('click', (e) => {
      e.preventDefault();
      this.filters.reset();
      this.table.showDynamic(this.documents);
    })

    // Фильтры
    this.resetFilters("resetFilter")
    this.changeFilter("sectionFilter")
    this.changeFilter('typeFilter')

  }

  async loadData(){
    this.documents = await this.fetchDocuments();
    this.filters.populate(this.documents);
    this.table.render(this.documents);
  }

  async fetchDocuments(){
    const response = await API.get('documents/byDirection', {direction_id:this.direction_id})
    if(!Array.isArray(response)){
      throw new Error("Некорректный формат ответа от сервера")
    }
    return response
  }
  changeFilter(elementId){
    document.getElementById(elementId).addEventListener("change", () => {
      const filtered = this.filters.getFiltered(this.documents);
      this.table.render(filtered)
    });
  }
  resetFilters(elementId)
  {
    document.getElementById(elementId).addEventListener('click', async () => {
      this.filters.reset()
      await this.loadData();
    });
  }
}
