<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для получения согласующих по БЕ
 * Class CBPGetUsersForBPIblock
 */
class CBPGetUsersForBPIblock extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPGetUsersForBPIblock constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'arElements' => '',
            'user'=>'',
            'dealId'=>'',
            'beId'=>'',
            'propCode' => '',
            'entityCode' => '',
            'countUsers' => '',
            'elementId' => '',
        ];

        $this->SetPropertiesTypes([
            'arElementsUsers' => ['Type' => 'string']
        ]);
    }

    /**
     * @description Получение согласующих по БЕ из инфоблока
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
        $arUsers = CBPHelper::ExtractUsers($this->user, $this->GetRootActivity()->GetDocumentId());
        $user = (!empty($arUsers)) ? $arUsers[0] : 0;

        $propCode = $this->propCode;
        $propEntity = $this->entityCode;
        $propEntity = (empty($propEntity)) ? 'BE' : $propEntity;
        $arForStr = \Immo\Tools\ActivityTools::getAppUsersBe(
            (int)$id,
            (int)$beId,
            (int)$user,
            (string)$propCode,
            (string)$propEntity
        );
        $this->arElementsUsers = implode(',', ($arForStr ?? []));
        $this->elementId = (count($arForStr) == 1) ? current($arForStr) : 0;
        $this->countUsers = count($arForStr) ?? 0;
        return CBPActivityExecutionStatus::Closed;
    }
}
?>