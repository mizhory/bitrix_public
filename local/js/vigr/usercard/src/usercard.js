import {Type} from 'main.core';
import SlimSelect from 'slim-select';
import IMask from 'imask';
import Alpine from 'alpinejs'
export class Usercard
{
   createSelect(options){
      return new SlimSelect(options)
   }
   createMoneyField(value){
      const prettyEuro = prettyMoney({
         currency: "€",
         decimals: "fixed",
         decimalDelimiter: ",",
         thousandsDelimiter: "."
      })
      return prettyEuro(value);
   }
}
window.Alpine = Alpine
Alpine.start()