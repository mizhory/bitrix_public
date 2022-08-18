<?php

namespace Immo\Statements\Data;

class StatementsApproval extends HLBlock
{
    private static $obStatementsApproval;

    public static function init(): self
    {
        if (static::$obStatementsApproval === null) {
            static::$obStatementsApproval = new static('StatementsApproval');
        }
        return static::$obStatementsApproval;
    }

    public static function setNextStatus(string $sXmlIdCurrentStatus, string $sXmlIdNextStatus)
    {
        $obStatementsApproval = self::init();
        $iIdCurrentStatus = $obStatementsApproval->getEnumIdByXmlCode($sXmlIdCurrentStatus);
        $iIdNextStatus = $obStatementsApproval->getEnumIdByXmlCode($sXmlIdNextStatus);
        $arOptions = [
            'filter' => [
                '=UF_STATUS_CARD' => $iIdCurrentStatus
            ],
            'select' => [
                'ID'
            ]
        ];
        $class = $obStatementsApproval->getEntity();
        $arElements = $class::getList($arOptions)->fetchAll();
        if($arElements){
            foreach ($arElements as $arElement) {
                $class::update($arElement['ID'], ['UF_STATUS_CARD' => $iIdNextStatus]);
            }
        }
    }

    public function getStatusCard()
    {
        static $arEnumList = null;
        if ($arEnumList === null) {
            $arEnumPropStatus = $this->getFields(['FIELD_NAME' => 'UF_STATUS_CARD']);
            $arEnumList = $arEnumPropStatus['items'];
        }
        return $arEnumList;
    }

    public function getEnumIdByXmlCode(string $sXmlCodeStatus): int
    {
        $arStatus = $this->getStatusCard();
        foreach ($arStatus as $status) {
            if ($status['xml_id'] === $sXmlCodeStatus) {
                return $status['id'];
            }
        }
        return 0;
    }

}