<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для удаления согласующих по БЕ
 * Class CBPGetUsersForBPIblockDelete
 */
class CBPGetUsersForBPIblockDelete extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPGetUsersForBPIblockDelete constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'dealId'=>'',
            'beId'=>'',
            'propCode' => '',
            'entityCode' => '',
        ];

        $this->SetPropertiesTypes([
            'arElements' => ['Type' => 'string']
        ]);
    }

    /**
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function Execute()
    {
        $id = $this->dealId;
        if (empty($id)) {
            return CBPActivityExecutionStatus::Closed;
        }

        $beId = $this->beId;
        if (empty($beId)) {
            return CBPActivityExecutionStatus::Closed;
        }

        $propCode = $this->propCode;
        $propEntity = $this->entityCode;
        $propEntity = (empty($propEntity)) ? 'BE' : $propEntity;
        $arAppUsers = \Immo\Tools\ActivityTools::getAppUsersBe(
            (int)$id,
            (int)$beId,
            0,
            (string)$propCode,
            (string)$propEntity
        );
        foreach ($arAppUsers as $id) {
            \CIBlockElement::Delete($id);
        }

        return CBPActivityExecutionStatus::Closed;
    }
}
?>