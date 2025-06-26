export class Direction {

  constructor() {
    this.direction_id = document.getElementById('show-dynamic').dataset.direction
  }

  getDirectionId(){
    return this.direction_id;
  }
}
