<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активти для получения согласующих по стадии
 * Class CBPGetUsersForStageComplex
 */
class CBPGetUsersForStageComplex extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPGetUsersForStageComplex constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'bp' => '',
            'stage' => '',
            'depId' => '',
            'userStr' => '',
            'userArray' => '',
        ];
    }

    /**
     * @description Достает согласующих по стадии заявки
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function Execute()
    {
        $arUsers = static::getUsersForStageActivity((string)$this->stage, (int)$this->bp, (int)$this->depId);

        $this->userArray = $arUsers['array'];
        $this->userStr = $arUsers['string'];

        return CBPActivityExecutionStatus::Closed;
    }

    /**
     * @description Возвращает массив и строку согласующих по стадии в формате пригодным для активити
     * @param string $stage
     * @param int $iblockId
     * @param int $departmentId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getUsersForStageActivity(string $stage, int $iblockId, int $departmentId): array
    {
        $result = [
            'array' => [],
            'string' => ''
        ];

        $arUsers = \Immo\Tools\ActivityTools::loadUsersForStage($stage, $iblockId, $departmentId);
        foreach ($arUsers as $index => $user) {
            $arUsers[$index] = "user_{$user}";
        }
        $result['array'] = $arUsers;
        $result['string'] = implode(',', $arUsers);

        return $result;
    }

    /**
     * @param $documentType
     * @param $activityName
     * @param $arWorkflowTemplate
     * @param $arWorkflowParameters
     * @param $arWorkflowVariables
     * @param null $arCurrentValues
     * @param string $formName
     * @return false|string|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
    {
        $arCurrentValues = (!empty($arCurrentValues)) ? $arCurrentValues : [];

        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
        if (is_array($arCurrentActivity['Properties'])) {
            $arCurrentValues = array_merge($arCurrentValues, $arCurrentActivity['Properties']);
        }

        $bpId = 0;
        if ($arCurrentValues['bp'] > 0) {
            $bpId = $arCurrentValues['bp'];
        }

        \Bitrix\Main\Loader::includeModule('iblock');

        $dbIblocks = \Bitrix\Iblock\IblockTable::query()
            ->where([['ACTIVE'],])
            ->whereIn('IBLOCK_TYPE_ID', ['bitrix_processes', 'lists'])
            ->setSelect(['ID', 'NAME', 'IBLOCK_TYPE_ID'])
            ->addOrder('IBLOCK_TYPE_ID')
            ->addOrder('NAME')
            ->exec();

        $arIblocks = [];
        while($arIblock = $dbIblocks->fetch()){
            $arIblocks[$arIblock['ID']] = '['.$arIblock['IBLOCK_TYPE_ID'].'] ' . $arIblock['NAME'];
        }

        $arCurrentValues['IBLOCKS'] = $arIblocks;
        $arCurrentValues['IB'] = $bpId;

        $runtime = CBPRuntime::GetRuntime();
        return $runtime->ExecuteResourceFile(
            static::getActivityFilePath(),
            "properties_dialog.php",
            ["arCurrentValues" => $arCurrentValues]
        );
    }
}
?>