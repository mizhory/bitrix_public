<?

use Immo\Iblock\Manager;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для ID БЕ по юр.лицу
 * Class CBPGetBeByCompany
 */
class CBPGetBeByCompany extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPGetBeByCompany constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'id'=>'',
            'idCompany' => '',
            'oldBeId' => '',
            'beName' => ''
        ];
    }

    /**
     * @description Достает ID БЕ по свойству юрлица
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function Execute()
    {
        $this->id = 0;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return CBPActivityExecutionStatus::Closed;
        }

        $companyId = $this->idCompany;
        if (intval($companyId) <= 0) {
            return CBPActivityExecutionStatus::Closed;
        }

        $orgCompanyId = \Immo\Structure\Organization::defineCompanyComp($companyId);
        if ($orgCompanyId <= 0) {
            return CBPActivityExecutionStatus::Closed;
        }

        $entity = \Immo\Structure\Organization::getSectionEntity();
        if (empty($entity)) {
            return CBPActivityExecutionStatus::Closed;
        }

        $arBe = $entity::query()
            ->where('IBLOCK_ID', \Immo\Structure\Organization::getDepartmentIblockId())
            ->where('ID', $orgCompanyId)
            ->setSelect(['IBLOCK_SECTION_ID', 'BE_NAME' => 'PARENT_SECTION.NAME'])
            ->exec()
            ->fetch();

        $this->id = (int)$arBe['IBLOCK_SECTION_ID'];
        if ($this->id > 0) {
            $this->beName = $arBe['BE_NAME'];
            $this->fillOldBeId($this->id);
        }

        return CBPActivityExecutionStatus::Closed;
    }

    /**
     * @description Находит и заполняет ID БЕ из старого списка
     * @param int $orgBeId
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function fillOldBeId(int $orgBeId): void
    {
        $beIblockId = Manager::getIblockId('be');
        if ($beIblockId <= 0) {
            return;
        }

        $arBe = \CIBlockElement::GetList([], [
            'PROPERTY_ORG_STRUCTURE' => $orgBeId,
            'IBLOCK_ID' => $beIblockId
        ], false, false, [
            'IBLOCK_ID',
            'ID'
        ])->Fetch();
        if (empty($arBe['ID']) or $arBe === false) {
            return;
        }

        $this->oldBeId = $arBe['ID'];
    }
}
?>