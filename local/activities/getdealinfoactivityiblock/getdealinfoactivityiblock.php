<?

use Immo\Iblock\Manager;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для получения инфы по БЕ в заявке
 * Class CBPGetDealInfoActivityIblock
 */
class CBPGetDealInfoActivityIblock extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPGetDealInfoActivityIblock constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'cardId'=>'',
            'iblockId' => '',
            'be' => '',
            'curse'=>'',
            'rate'=>'',
            'rateBe' => '',
            'ur' => '',
            'companyId' => '',
            'summa' => '',
            'article' => '',
            'beName' => '',
            'articleName' => '',
            'urName' => '',
            'textTask' => '',
            'rateId' => '',
            'beBank' => '',
            'bankName' => '',
            'products' => ''
        ];
    }

    /**
     * @description Достает информацию по БЕ из заявки инфоблока
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function Execute()
    {
        \Bitrix\Main\Loader::includeModule('vigr.budget');

        $deal = new \Immo\Integration\Budget\Iblock();

        $arFields = $deal->getFieldsByDealBudget($this->cardId, [
            'rate',
            'rateBe',
            'rateCurse',
            'id',
            'ur',
            'items',
            'sum',
            'article'
        ], (int)$this->iblockId);

        if (empty($arFields)) {
            $this->ur = $this->article = $this->rateBe = $this->rate = '';
            $this->summa = $this->curse = '0';
            $this->be = [];
            return CBPActivityExecutionStatus::Closed;
        }

        $typeBe = \Immo\Integration\Budget\Iblock::defineTypeBe($this->iblockId);
        if ($typeBe['MULTIPLE']) {
            $firstFields = current($arFields);
            $this->rate = $firstFields['rate'];
            $this->rateBe = $firstFields['rateBe'];
            $this->curse = $firstFields['rateCurse'];

            $sum = 0;
            $arUr = $arArticle = $arBe = [];
            foreach ($arFields as $item) {
                $arBe = array_merge($arBe, array_keys($item['items']));
                $arArticle[] = $item['article'];
                $arUr[] = $item['ur'];
                $sum += $item['sum'];
            }

            $this->be = array_unique($arBe);
            $this->article = array_unique($arArticle);
            $this->ur = array_unique($arUr);
            if (!empty($this->ur)) {
                $urIds = $this->ur;
                $arCompanies = [];
                foreach ($urIds as $id) {
                    $arCompanies[] = \Immo\Structure\Organization::defineCompanyComp($id);
                }

                if (!empty($arCompanies)) {
                    $this->companyId = $arCompanies;
                }
            }

            $this->summa = $sum;

            if (!empty($arFields)) {
                foreach ($arFields as $articleFields) {
                    $arProducts = [];
                    foreach ($articleFields['items'] as $be) {
                        if (empty($be['items'])) {
                            continue;
                        }

                        $arProducts = array_merge($arProducts, array_keys($be['items']));
                    }

                    $this->products = $arProducts;
                }
            }

            $this->fillTextTask();
        } else {
            $arFields = current($arFields);
            if($arFields['rate'] === $arFields['rateBe']){
                $arFields['rateCurse'] = 1;
            }

            $this->rate = $arFields['rate'];
            $this->rateBe = $arFields['rateBe'];
            $this->curse = $arFields['rateCurse'];
            $this->ur = $arFields['ur'];
            $this->beBank = $arFields['beBank'];
            if (!empty($arFields['beBank'])) {
                $this->fillBeBankName((int)$arFields['beBank'], $typeBe);
            }
            if (!empty($arFields['ur'])) {
                $this->companyId = \Immo\Structure\Organization::defineCompanyComp($arFields['ur']);
            }

            $this->be = array_unique(array_column($arFields['items'], 'id'));
            $this->summa = $arFields['sum'];
            $this->article = $arFields['article'];
            if (!empty($arFields['items'])) {
                $arProducts = [];
                foreach ($arFields['items'] as $be) {
                    if (empty($be['items'])) {
                        continue;
                    }

                    $arProducts = array_merge($arProducts, array_keys($be['items']));
                }

                $this->products = $arProducts;
            }
        }

        $this->writeVariableNames([
            [
                'ID' => $this->article,
                'IBLOCK_ID' => \Immo\Iblock\Manager::getArticleIblockId(),
                'VAR' => 'articleName'
            ],
            [
                'ID' => $this->ur,
                'IBLOCK_ID' => \Immo\Iblock\Manager::getIblockId('companies'),
                'VAR' => 'urName'
            ],
            [
                'ID' => $this->be,
                'IBLOCK_ID' => \Immo\Iblock\Manager::getIblockId('be'),
                'VAR' => 'beName'
            ],
        ]);

        $this->defineCurrencyId($this->rate);

        return CBPActivityExecutionStatus::Closed;
    }

    /**
     * @description Заполняет название внутреннего банка в свйоство активити
     * @param int $beId
     * @param array $typeBe
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function fillBeBankName(int $beId, array $typeBe): void
    {
        if (
            $beId <= 0
            or (
                empty($typeBe)
                or $typeBe['PARAMS']['USE_BANK'] == 'N'
                or empty($typeBe['PARAMS']['BANK_FIELD'])
            )
        ) {
            return;
        }

        $beIblockId = Manager::getIblockId($typeBe['PARAMS']['IBLOCK_LIST']['BIZNES_UNITS']);
        if ($beIblockId <= 0) {
            return;
        }

        $arBe = \CIBlockElement::GetList([], [
            'IBLOCK_ID' => $beIblockId,
            'ID' => $beId,
            "!PROPERTY_{$typeBe['PARAMS']['BANK_FIELD']}" => false
        ], false, false, [
            'ID',
            'IBLOCK_ID',
            "PROPERTY_{$typeBe['PARAMS']['BANK_FIELD']}"
        ])->Fetch();

        if ($arBe === false or empty($arBe["PROPERTY_{$typeBe['PARAMS']['BANK_FIELD']}_VALUE"])) {
            return;
        }

        $this->bankName = $arBe["PROPERTY_{$typeBe['PARAMS']['BANK_FIELD']}_VALUE"];
    }

    /**
     * @description Определяет ID валюты в заявке по коду. Записывает ID в свойство активити
     * @param string|null $rateCode
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function defineCurrencyId(?string $rateCode = ''): void
    {
        if (!\Bitrix\Main\Loader::includeModule('iblock') or empty($rateCode)) {
            return;
        }

        $iblockId = Manager::getIblockId('currencies_ib');
        if ($iblockId <= 0) {
            return;
        }

        $arCurrency = \CIBlockElement::getList([], [
            'IBLOCK_ID' => $iblockId,
            'PROPERTY_KOD_VALYUTY' => $rateCode
        ], false, false, ['ID'])->Fetch();

        if ($arCurrency === false or empty($arCurrency['ID'])) {
            return;
        }

        $this->rateId = $arCurrency['ID'];
    }

    /**
     * @description Заполнет тест для задания
     * @param bool $includeBBCode
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function fillTextTask(bool $includeBBCode = true): void
    {
        $printableValues = \Immo\Iblock\Property\BiznesUnitsIblockField::getPrintableValues(
            $this->cardId,
            $this->iblockId
        );

        if (empty($printableValues)) {
            return;
        }

        $mess = '';

        foreach ($printableValues['be_block'] as $item) {
            $mess .= $this->getStringFormat(["Статья расходов", $item['BE::ARTICLE']]) . PHP_EOL;
            $itemsMess = $this->getStringFormat(['БЕ', $item['BE::ITEMS']], $includeBBCode);
            $mess .= str_replace(PHP_EOL, '; ', $itemsMess) . PHP_EOL;
        }
        
        if (empty($mess)) {
            return;
        }

        $this->textTask = trim($mess);
    }

    /**
     * @description Возвращает строку в формате: [b]Описание[/b]: Значение
     * @param array $values
     * @param bool $includeBBCode
     * @return string
     */
    protected function getStringFormat(array $values, bool $includeBBCode = true): string
    {
        return str_replace(
            ['#BB#', '#BBE#'],
            ($includeBBCode ? ['[b]', '[/b]'] : ''),
            sprintf('#BB#%s#BBE#: %s', ...$values)
        );
    }

    /**
     * @description Записывает в переменные названия элементов инфоблока
     * @param array $arNameData
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function writeVariableNames(array $arNameData): void
    {
        foreach ($arNameData as $item) {
            if (empty($item['ID']) or empty($item['IBLOCK_ID'])) {
                continue;
            }

            $arResult = $this->getNameIblock($item['ID'], $item['IBLOCK_ID']);
            $this->arProperties[$item['VAR']] = (!empty($arResult))
                ? implode(', ', array_column($arResult, 'NAME'))
                : '';
        }
    }

    /**
     * @description Возвращает названия элементов инфоблока
     * @param $element
     * @param int $iblockId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getNameIblock($element, int $iblockId): array
    {
        $query = $arBe = \Bitrix\Iblock\ElementTable::query()
            ->where('IBLOCK_ID', $iblockId)
            ->addSelect('NAME');

        if (is_array($element)) {
            $query->whereIn('ID', $element);
        } else {
            $query->where('ID', $element);
        }

        return $query->exec()->fetchAll();
    }
}
?>