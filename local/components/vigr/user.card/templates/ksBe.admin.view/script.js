BX.ready(() => {
   class userCardTemplate {
      constructor() {
         this.card = new Usercard();
         this.MainField = document.querySelector('.main-field');
         this.AdditionalSalary = document.querySelector('.additional-salary');
         this.salary = document.querySelector('.salary');
         this.overSalary = document.querySelector('.over-salary');
         this.balance = document.querySelector('.balance');
         // slim select для селекта БЕ
         /*if (document.querySelector('.be-select') !== null) {
             this.beSelect = this.card.createSelect({
                 select: '.be-select',
                 searchPlaceholder: "Введите название БЕ",
                 placeholder: "Выберите значение БЕ",
             });
         }
         // slim select для селекта БЕ по ЗП
         if (document.querySelector('.salary-be') !== null) {
             this.salaryBeSelect = this.card.createSelect({
                 select: '.salary-be',
                 searchPlaceholder: "Введите название БЕ",
                 placeholder: "Выберите значение БЕ",
             });
         }*/
         //навесим обработчик на изменение каждого инпута
         [...document.querySelectorAll('.cs-field input')].forEach((el) => {
            el.addEventListener('input', (e) => {
               if (!e.target.classList.contains('main-field')) {
                  this.changeHandler(e)
               }
            })
            //маска для input
            /*IMask(
               el,
               {
                  mask: 'num',
                  blocks: {
                     num: {
                        mask: Number,
                        thousandsSeparator: ' ',
                        radix:'.'
                     }
                  }
            })*/
         });

         /*f (document.querySelector('.be-select') !== null) {
             // событие изменения селекта Принадлежность к БЕ
             document.querySelector('.be-select').addEventListener('change', (event) => {
                 let itemsForSecondSelect = [];
                 [...event.target.selectedOptions].forEach(el => {
                     itemsForSecondSelect.push({
                         text: el.innerText,
                         value: el.value,
                     });
                 })
                 this.salaryBeSelect.setData(itemsForSecondSelect);
                 this.MainField.value = JSON.stringify({
                     //beSelect: this.beSelect.selected().join(','),
                     salaryBeSelect: this.salaryBeSelect.selected(),
                     salary: this.salary.value,
                     additionalSalary: this.AdditionalSalary.value,
                     overSalary: this.overSalary.value,
                     balance: this.balance.value,
                 });
             })
         }
         if (document.querySelector('.salary-be') !== null) {
             // событие изменения селекта с БЕ по ЗП
             document.querySelector('.salary-be').addEventListener('change', (event) => {
                 this.MainField.value = JSON.stringify({
                     //beSelect: this.beSelect.selected().join(','),
                     //salaryBeSelect: this.salaryBeSelect.selected(),
                     //salary: this.salary.value,
                     additionalSalary: this.AdditionalSalary.value,
                     overSalary: this.overSalary.value,
                     balance: this.balance.value,
                 });
             })
         }*/
      }

      /**
       * handler для изменения инпута, чтобы подставить данные в скрытое поле
       * **/
      changeHandler(e) {
         /*this.MainField.value = JSON.stringify({
             //beSelect: this.beSelect.selected().join(','),
             //salaryBeSelect: this.salaryBeSelect.selected(),
             salary: this.salary.value,
             additionalSalary: this.AdditionalSalary.value,
             overSalary: this.overSalary.value,
             balance: this.balance.value,
         });*/
      }
   }

   var userCardTemplateChild = new userCardTemplate()
})
