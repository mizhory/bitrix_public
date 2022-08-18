<?php foreach ($dialog->getMap() as $fieldId => $field):
	$filedType = $dialog->getFieldTypeObject($field);
	$fSettings = $filedType->getSettings();
	$allowSelection = (is_array($fSettings) && isset($fSettings['allowSelection'])) ? $fSettings['allowSelection'] : true;
	?>
	<tr>
		<td align="right" width="40%" >
			<span class="<?=$filedType->isRequired()?'adm-required-field':''?>">
				<?= htmlspecialcharsbx($field['Name']) ?>:
			</span>
			<?if (!empty($field['Description'])):?>
				<br/><?= htmlspecialcharsbx($field['Description']) ?>
			<?endif;?>
		</td>
		<td width="60%">
			<?php
			echo $filedType->renderControl(array(
				'Form' => $dialog->getFormName(),
				'Field' => $field['FieldName']
			), $dialog->getCurrentValue($field), $allowSelection, 0);

			?>
		</td>
	</tr>
<?php endforeach;?>