BX.ready(()=>{
   class userCardTemplate{
      constructor() {
         let card = new Usercard();
         this.MainField = document.querySelector('.main-field-legal');
         if(document.querySelector('.legal-select') !== null) {
            this.legalSelect = card.createSelect({
               select: '.legal-select',
               searchPlaceholder: "Введите название Юр.лица",
               placeholder: "Выберите значение Юр.лица",
            });
         }
         if(document.querySelector('.legal-select') !== null) {
            document.querySelector('.legal-select').addEventListener('change', (event) => {
               this.MainField.value = JSON.stringify({
                  legalSelect: this.legalSelect.selected().join(','),
               });
            })
         }
      }
   }
   var userCardTemplateChild = new userCardTemplate()
})
