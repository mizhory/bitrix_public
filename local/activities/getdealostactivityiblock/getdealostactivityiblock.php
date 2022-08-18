<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для получения остатка по заявке
 * Class CBPGetDealOstActivityIblock
 */
class CBPGetDealOstActivityIblock extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPGetDealOstActivityIblock constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'dealId'=>'',
            'ost'=>'',
        ];

        $this->SetPropertiesTypes([
            'ost' => ['Type' => 'string']
        ]);
    }

    /**
     * @description Собирает и возвращает остаток по заявке инфоблока
     * @return int
     * @throws \Bitrix\Main\LoaderException
     */
    public function Execute()
    {
        \Bitrix\Main\Loader::includeModule('vigr.budget');
        $arProducts = (new \Immo\Integration\Budget\Iblock())->getDealProducts($this->dealId, false);

        $sum = 0;
        foreach ($arProducts as $product){
            $sum += $product['summa'];
            $sum -= $product['payInValute'];
        }
        $ost = round($sum,2);

        $this->ost = $ost;

        return CBPActivityExecutionStatus::Closed;
    }
}
?>