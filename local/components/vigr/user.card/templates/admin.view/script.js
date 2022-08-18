BX.ready(()=>{
   class userCardTemplate{
      constructor() {
         let card = new Usercard();
         this.MainField = document.querySelector('.main-field');
         this.AdditionalSalary = document.querySelector('.additional-salary');
         this.salary = document.querySelector('.salary');
         this.overSalary = document.querySelector('.over-salary');
         this.balance = document.querySelector('.balance');
         this.beSelect = card.createSelect({
            select:'.be-select',
            searchPlaceholder:"Введите название БЕ",
            placeholder:"Выберите значение БЕ",
         });
         this.salaryBeSelect = card.createSelect({
            select:'.salary-be',
            searchPlaceholder:"Введите название БЕ",
            placeholder:"Выберите значение БЕ",
         });
         [...document.querySelectorAll('input')].forEach((el)=>el.addEventListener('input',(e)=>{
            if(!e.target.classList.contains('main-field')) {
               this.changeHandler()
            }
         }))
         document.querySelector('.be-select').addEventListener('change',(event)=>{
            let itemsForSecondSelect = [];
            [...event.target.selectedOptions].forEach(el => {
                  itemsForSecondSelect.push({
                     text:el.innerText,
                     value:el.value,
                  });
            })
            this.salaryBeSelect.setData(itemsForSecondSelect);
            this.MainField.value = JSON.stringify({
               beSelect:this.beSelect.selected().join(','),
               salaryBeSelect:this.salaryBeSelect.selected(),
               salary:this.salary.value,
               additionalSalary:this.AdditionalSalary.value,
               overSalary:this.overSalary.value,
               balance:this.balance.value,
            });
         })
         document.querySelector('.salary-be').addEventListener('change',(event)=>{
            this.MainField.value = JSON.stringify({
               beSelect:this.beSelect.selected().join(','),
               salaryBeSelect:this.salaryBeSelect.selected(),
               salary:this.salary.value,
               additionalSalary:this.AdditionalSalary.value,
               overSalary:this.overSalary.value,
               balance:this.balance.value,
            });
         })
      }
      changeHandler(){
         this.MainField.value = JSON.stringify({
            beSelect:this.beSelect.selected().join(','),
            salaryBeSelect:this.salaryBeSelect.selected(),
            salary:this.salary.value,
            additionalSalary:this.AdditionalSalary.value,
            overSalary:this.overSalary.value,
            balance:this.balance.value,
         });
      }
   }
   var userCardTemplateChild = new userCardTemplate()
})
