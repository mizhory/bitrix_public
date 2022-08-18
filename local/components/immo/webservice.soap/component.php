<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("webservice") || !CModule::IncludeModule("iblock"))
    return;

class CWebServiceSoap1C extends IWebService
{

    private $PHP_LOGIN, $PHP_PASSWORD;

    static function GetWebServiceDesc()
    {
        $wsdesc = new \CWebServiceDesc();
        $wsdesc->wsname = "immo.webservice.soap";
        $wsdesc->wsclassname = "CAddBeData";
        $wsdesc->wsdlauto = true;
        $wsdesc->wsendpoint = \CWebService::GetDefaultEndpoint();
        $wsdesc->wstargetns = \CWebService::GetDefaultTargetNS();
        $wsdesc->classTypes = [];
        $wsdesc->structTypes = [];
        $wsdesc->classes = [
            "CWebServiceSoap1C" => [
                "PushCompanyData" => [
                    'type' => 'public',
                    'name' => 'PushCompanyData',
                    'input' => [
                        'NAME' => [
                            'varType' => 'string'
                        ],
                        'INN' => [
                            'varType' => 'string'
                        ],
                        'ORG_OBJECT_ID' => [
                            'varType' => 'string'
                        ],
                    ],
                    'output' => [
                        'OBJECT_ID' => [
                            'varType' => 'integer'
                        ]
                    ],
                    'httpauth' => 'N'
                ],
                "PushUserData" => [
                    'type' => 'public',
                    'name' => 'PushUserData',
                    'input' => [
                        'OBJECT_ID' => [
                            'varType' => 'string'
                        ],
                        'ORG_OBJECT_ID' => [
                            'varType' => 'string'
                        ],
                        'SNILS' => [
                            'varType' => 'string'
                        ],
                        'LAST_NAME' => [
                            'varType' => 'string'
                        ],
                        'NAME' => [
                            'varType' => 'string'
                        ],
                        'SECOND_NAME' => [
                            'varType' => 'string'
                        ],
                        'WORK_TYPE' => [
                            'varType' => 'string'
                        ],
                        'START_DATE' => [
                            'varType' => 'string'
                        ],
                        'WORK_DAYS' => [
                            'varType' => 'float'
                        ],
                        'DEPARTMENT' => [
                            'varType' => 'string'
                        ],
                        'SALARY_FIX' => [
                            'varType' => 'float'
                        ],
                        'SALARY_TOTAL' => [
                            'varType' => 'float'
                        ],
                        'PART_KOEF' => [
                            'varType' => 'float'
                        ],
                        'VACATION_NUM' => [
                            'varType' => 'float'
                        ],
                        'FROM_15' => [
                            'varType' => 'float'
                        ],
                        'SALARY_DIFF' => [
                            'varType' => 'float'
                        ],
                    ],
                    'output' => [
                        'OBJECT_ID' => [
                            'varType' => 'string'
                        ]
                    ],
                    'httpauth' => 'N'
                ],
                'putAVO' => [
                    'type' => 'public',
                    'name' => 'putAVO',
                    'input' => [
                        'NUM' => ['varType' => 'string'],
                        'TO' => ['varType' => 'string'],
                        'SUM' => ['varType' => 'float'],
                        'COMPANY_INN' => ['varType' => 'string'],
                    ],
                    'output' => [
                        'OBJECT_ID' => ['varType' => 'integer']
                    ],
                    'httpauth' => 'N'
                ]
            ]
        ];

        return $wsdesc;
    }

    function PushCompanyData($name, $inn, $object_org_id)
    {
        if (!$this->CheckBasicAuth()) {
            return new CSOAPFault('Server Error', 'Unable to authorize user.');
        }
        if ($object_org_id === null) {
            return new CSOAPFault('Data Error', 'Object can not be null');
        }
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockElementProvider = new \CIblockElement(false);
            $elementId = $iblockElementProvider->GetList(['ID' => 'DESC'], [
                'PROPERTY_ORG_OBJECT_ID' => $object_org_id
            ])->Fetch()['ID'];
            if ($elementId !== null) {
                $iblockElementProvider->Update($elementId, [
                    'NAME' => $name,
                ]);
                $iblockElementProvider->SetPropertyValuesEx($elementId, false, [
                    'INN' => (int)$inn
                ]);
            } else {
                $elementId = $iblockElementProvider->Add([
                    'ACTIVE' => 'Y',
                    'NAME' => $name,
                    "IBLOCK_ID" => getIblockIdByCode('companies'),
                    'PROPERTY_VALUES' => [
                        'INN' => (int)$inn,
                        'PROPERTY_ORG_OBJECT_ID' => $object_org_id
                    ]
                ]);
            }
        }
        return $elementId;
    }

    function PushUserData($object_id, $object_org_id, $snils, $lastName, $name, $secondName, $workType,
                          $startDate, $workDays, $department, $salaryFix, $salaryTotal, $partKoeff, $vacationNum, $from15, $salaryDiff)
    {

        try {
            global $USER;
            if (!$this->CheckBasicAuth()) {
                return new CSOAPFault('Server Error', 'Unable to authorize user.');
            }
            if ($object_id === null) {
                return new CSOAPFault('Data Error', 'Object can not be null');
            }
            if ($object_org_id === null) {
                return new CSOAPFault('Data Error', 'Organization object can not be null');
            }

            if (\Bitrix\Main\Loader::includeModule('iblock')) {
                $iblockElementProvider = new \CIblockElement(false);
                $companyID = $iblockElementProvider->GetList(['ID' => 'DESC'], [
                    'PROPERTY_ORG_OBJECT_ID' => $object_org_id
                ])->Fetch()['ID'];
                if ($companyID === null) {
                    return new CSOAPFault('Data Error', 'Company not found');
                }
            }
            if (\Bitrix\Main\Loader::includeModule('highloadblock')) {
                $usersDataHL = \Bitrix\Highloadblock\HighloadBlockTable::getList([
                    'filter' => [
                        '=NAME' => 'CompanyEmplyeesData'
                    ]
                ])->fetch();
                $this->HL_BLOCK = $usersDataHL['ID'];
                if ($this->HL_BLOCK < 1) {
                    return new CSOAPFault('Data Error', $this->HL_BLOCK);
                }

                $usersDataHlEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($usersDataHL);
                $hlDataClass = $usersDataHlEntity->getDataClass();

                $hlElement = $hlDataClass::getList(array(
                    'order' => [
                        'ID' => 'DESC',
                    ],
                    'select' => ['*'],
                    'filter' => [
                        'UF_OBJECT_ID' => $object_id,
                    ],
                ))->fetch();

                if ($hlElement !== false) {
                    $hlDataClass::update($hlElement['ID'], [
                        "UF_OBJECT_ID" => $object_id,
                        "UF_WORK_TYPE" => $workType,
                        "UF_START_DATE" => $startDate,
                        "UF_WORK_DAYS" => $workDays,
                        "UF_DEPARTMENT" => $department,
                        "UF_SALARY_FIX" => $salaryFix,
                        "UF_SALARY_TOTAL" => $salaryTotal,
                        "UF_PART_KOEF" => $partKoeff,
                        "UF_VACATION_NUM" => $vacationNum,
                        "UF_FROM_15" => $from15,
                        "UF_SALARY_DIFF" => $salaryDiff,
                        "UF_COMPANY" => $companyID,
                        "UF_SNILS" => $snils,
                    ]);
                    $hlElementID = $hlElement['ID'];
                } else {

                    $hlElement = $hlDataClass::add([
                        "UF_OBJECT_ID" => $object_id,
                        "UF_WORK_TYPE" => $workType,
                        "UF_START_DATE" => $startDate,
                        "UF_WORK_DAYS" => $workDays,
                        "UF_DEPARTMENT" => $department,
                        "UF_SALARY_FIX" => $salaryFix,
                        "UF_SALARY_TOTAL" => $salaryTotal,
                        "UF_PART_KOEF" => $partKoeff,
                        "UF_VACATION_NUM" => $vacationNum,
                        "UF_FROM_15" => $from15,
                        "UF_SALARY_DIFF" => $salaryDiff,
                        "UF_COMPANY" => $companyID,
                        "UF_SNILS" => $snils,
                    ]);

                    $hlElementID = $hlElement->getId();

                }
            }
            $user = \Bitrix\Main\UserTable::GetList([
                'filter' => [
                    "UF_SNILS" => $snils
                ],
                'select' => [
                    'ID', 'UF_CS_LEGAL', 'UF_CS_BE'
                ]
            ])->Fetch();

            if ($user !== false) {
                $ufCsField = (array)json_decode($user['UF_CS_LEGAL']);
                $ufCsFieldArray = explode(',', $ufCsField['legalSelect']);
                $balanceField = (array)json_decode($user['UF_CS_BE']);
                $userFields = [];
                if (!in_array($hlElementID, $ufCsFieldArray)) {
                    $ufCsFieldArray[] = $hlElementID;
                    $userFields['UF_CS_LEGAL'] = json_encode([
                        'legalSelect' => implode(',', $ufCsFieldArray)
                    ]);
                }
                if ($from15 > $balanceField['balance']) {
                    $userFields['UF_CS_BE'] = json_encode([
                        'balance' => $from15
                    ]);
                }
                $USER->update($user['ID'], $userFields);
            } else {
                $password = randString(8, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789![]@#');
                $arUserFields = [
                    'LAST_NAME' => $lastName,
                    'NAME' => $name,
                    'SECOND_NAME' => $secondName,
                    'LOGIN' => \CUtil::translit($lastName, 'ru', ['change_case' => false]),
                    'EMAIL' => \CUtil::translit($name, 'ru', ['change_case' => false]) . '.' .
                        \CUtil::translit($lastName, 'ru', ['change_case' => false]) . '@immo.ru',
                    'PASSWORD' => $password,
                    'CONFIRM_PASSWORD' => $password,
                    'UF_SNILS' => $snils,
                    'UF_CS_LEGAL' => json_encode([
                        'legalSelect' => implode(',', [$hlElementID])
                    ]),
                    'UF_CS_BE' => json_encode([
                        'balance' => $from15
                    ]),
                ];
                $USER->Add($arUserFields);
            }
            return json_encode($hlElementID);
        } catch (Throwable $e) {
            return new CSOAPFault('Error', $e->getMessage());
        }
    }

    function CheckBasicAuth()
    {
        $this->PHP_LOGIN = \Bitrix\Main\Config\Option::get("askaron.settings", "UF_USER_LOGIN");
        $this->PHP_PASSWORD = \Bitrix\Main\Config\Option::get("askaron.settings", "UF_PASSWORD");
        if ($_SERVER['PHP_AUTH_USER'] !== $this->PHP_LOGIN || $_SERVER['PHP_AUTH_PW'] !== $this->PHP_PASSWORD) {
            $GLOBALS["USER"]->RequiredHTTPAuthBasic();
            return false;
        }
        return true;
    }

    /**
     * @description Метод вебсервиса. Создает новую заявку на авансовый отчет из 1С
     * @param $num
     * @param $to
     * @param $sum
     * @param $inn
     * @return CSOAPFault|int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function putAVO($num, $to, $sum, $inn)
    {
        if (!$this->CheckBasicAuth()) {
            return new CSOAPFault('Server Error', 'Unable to authorize user.');
        }

        if (empty($num)) {
            return new CSOAPFault('Server Error', 'Не указан номер отчета.');
        }

        if (empty($inn)) {
            return new CSOAPFault('Server Error', 'Не указан ИНН юр. лица.');
        }

        $baseError = new CSOAPFault('Server Error', 'Внутреняя ошибка, невозможно создать заявку.');

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return $baseError;
        }

        $iblockId = \Immo\Iblock\Manager::getAvansIblockId();
        if ($iblockId <= 0) {
            return $baseError;
        }

        $query = \Immo\Iblock\Manager::getQueryElements($iblockId);
        if (empty($query)) {
            return $baseError;
        }

        $companiesIblockId = \Immo\Iblock\Manager::getIblockId('companies');
        if (empty($companiesIblockId)) {
            return $baseError;
        }

        $arCompany = \CIBlockElement::GetList([], [
            'PROPERTY_INN' => $inn,
            'IBLOCK_ID' => $companiesIblockId
        ], false, false, [
            'ID',
            'IBLOCK_ID'
        ])->Fetch();

        if ($arCompany === false or empty($arCompany['ID'])) {
            return new CSOAPFault('Server Error', 'Не найдено юр. лицо по указаному ИНН.');
        }

        $count = $query
            ->where([
                ['REP_NUM_1C.VALUE', $num],
                ['AUTOCOMPLETE_FIELD.VALUE', $to],
                ['APP_SUM.VALUE', $sum],
                ['SELECTED_PAYER.VALUE', $arCompany['ID']]
            ])
            ->countTotal(true)
            ->exec()
            ->getCount();
        if ($count > 0) {
            return new CSOAPFault('-1', 'Заявки с такими параметрами уже существует');
        }

        $arFields = [
            'NAME' => "Заявка на авансовый отчет {$num}",
            'IBLOCK_ID' => $iblockId,
            'PROPERTY_VALUES' => [
                'REP_NUM_1C' => $num,
                'AUTOCOMPLETE_FIELD' => $to,
                'APP_SUM' => $sum,
                'SELECTED_PAYER' => $arCompany['ID']
            ]
        ];

        $draftProperty = \Immo\Iblock\Manager::getPropertyByCode('DRAFT', $iblockId);
        if (
            !empty($draftProperty)
            and !empty($enumDraft = \Immo\Iblock\Manager::getEnumByCode($draftProperty['ID'], 'Y'))
        ) {
            $arFields['PROPERTY_VALUES']['DRAFT'] = $enumDraft['ID'];
        }

        $fromOut = \Immo\Iblock\Manager::getPropertyByCode('FROM_1C', $iblockId);
        if (!empty($fromOut)) {
            $arEnum = \Immo\Iblock\Manager::getEnumByCode($fromOut['ID'], 'Y');
            if (!empty($arEnum)) {
                $arFields['PROPERTY_VALUES']['FROM_1C'] = $arEnum['ID'];
            }
        }

        $iblockProvider = new \CIBlockElement();
        $id = $iblockProvider->Add($arFields);
        if ((empty($id) or $id === false) or !empty($iblockProvider->LAST_ERROR)) {
            return new CSOAPFault('Server Error', $iblockProvider->LAST_ERROR);
        }

        \Immo\Tools\BizprocHelper::runBizprocByElement((int)$id, $iblockId);

        return (int)$id;
    }
}

$APPLICATION->IncludeComponent(
    'bitrix:webservice.server',
    '',
    [
        "WEBSERVICE_NAME" => "immo.webservice.soap",
        "WEBSERVICE_MODULE" => "",
        "WEBSERVICE_CLASS" => "CWebServiceSoap1C"
    ]
);
die();

