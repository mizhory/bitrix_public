<table>
    <tr>
        <td>Принадлежность к БЕ</td>
        <td><?=$arResult['FIELD_VALUE']['BE_TEXT']?></td>
    </tr>
    <tr>
        <td>БЕ по зарплате</td>
       <td><?=$arResult['FIELD_VALUE']['SALARY_BE_TEXT']?></td>
    </tr>
   <tr>
      <td>Оклад по офферу</td>
      <td><?=$arResult['FIELD_VALUE']['additionalSalary']?></td>
   </tr>
   <tr>
      <td>Переплаты</td>
      <td><?=$arResult['FIELD_VALUE']['overSalary']?></td>
   </tr>
   <tr>
      <td>Остаток до 15%</td>
      <td><?=$arResult['FIELD_VALUE']['balance']?></td>
   </tr>
</table>