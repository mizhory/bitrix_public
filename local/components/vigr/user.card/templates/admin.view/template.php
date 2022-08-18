<link href="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.0/slimselect.min.css" rel="stylesheet"></link>
<input type="hidden" name="<?=$arParams['userField']['FIELD_NAME']?>" class="main-field" value="<?=$arParams['userField']['VALUE']?>">
<div class="bg-white p-4">
   <div class="flex mt-2 mb-2">
      <div class="flex items-center mr-4 w-32">
         Принадлежность к БЕ
      </div>
      <div class="ml-4">
         <select class="be-select" multiple>
             <?foreach ($arResult['SELECTOR_VALUES'] as $id => $beElement):?>
                <option value="<?=$id?>" <?=in_array($id,$arResult['FIELD_VALUE']['beSelect'])?'selected':''?>><?=$beElement?></option>
             <?endforeach;?>
         </select>
      </div>
   </div>
   <div class="flex mt-2 mb-2">
      <div class="flex items-center mr-4 w-32">
         БЕ по зарплате
      </div>
      <div class="ml-4">
         <select class="salary-be">
             <?foreach ($arResult['SELECTOR_VALUES'] as $id => $beElement):
                 if(in_array($id,$arResult['FIELD_VALUE']['beSelect'])){?>
                    <option value="<?=$id?>" <?=$arResult['FIELD_VALUE']['salaryBeSelect'] == $id?'selected':''?>><?=$beElement?></option>
                 <?}?>
             <?endforeach;?>
         </select>
      </div>
   </div>
   <div class="flex mt-2 mb-2">
      <div class="flex items-center mr-4 w-32">
         Оклад по офферу
      </div>
      <div class="ml-4">
         <input type="text" class="salary" value="<?=$arResult['FIELD_VALUE']['salary']?>" />
      </div>
   </div>
   <div class="flex mt-2 mb-2">
      <div class="flex items-center mr-4 w-32">
         Дополнительный оклад
      </div>
      <div class="ml-4">
         <input type="text" class="additional-salary" value="<?=$arResult['FIELD_VALUE']['additionalSalary']?>"/>
      </div>
   </div>
   <div class="flex mt-2 mb-2">
      <div class="flex items-center mr-4 w-32">
         Переплата
      </div>
      <div class="ml-4">
         <input type="text" class="over-salary" value="<?=$arResult['FIELD_VALUE']['overSalary']?>"/>
      </div>
   </div>
   <div class="flex mt-2 mb-2">
      <div class="flex items-center mr-4 w-32">
         Остаток до 15 процентов
      </div>
      <div class="ml-4">
         <input disabled type="text" class="balance" value="<?=$arResult['FIELD_VALUE']['balance']?>"/>
      </div>
   </div>
</div>
