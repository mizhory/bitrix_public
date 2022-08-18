<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити генерации ссылки на скачивание
 * Class CBPGeneratePrintLinkIblock
 */
class CBPGeneratePrintLinkIblock extends CBPActivity
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
            'idPrint'=>'',
            'iblockIdPrint' => '',
            'linkPrint' => '',
        ];
    }

    /**
     * @description Генерирует ссылка для скачивания
     * @return int
     * @throws \Bitrix\Main\LoaderException
     */
    public function Execute()
    {
        $id = $this->idPrint;
        $iblockId = $this->iblockIdPrint;
        $this->linkPrint = '';

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return CBPActivityExecutionStatus::Closed;
        }

        if (intval($id) <= 0 or intval($iblockId) <= 0) {
            return CBPActivityExecutionStatus::Closed;
        }

        $this->linkPrint = \Immo\Tools\File\FileDownload::generateLink($id, $iblockId);

        return CBPActivityExecutionStatus::Closed;
    }
}
?>