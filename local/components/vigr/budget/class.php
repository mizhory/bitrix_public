<?php
use Bitrix\Main\UI\Extension;

/**
 * Class CBudget
 * Компексный компонент бюджета
 */
class CBudget extends CBitrixComponent
{
    public function executeComponent()
    {
        Extension::load('ui.bootstrap4');
        $this->buildComplexVariables();
    }

    protected function buildComplexVariables(){
        global $APPLICATION;

        $arComponentVariables = [
            'type',
        ];

        $arDefaultUrlTemplates404 = array(
            'list' => 'budget/',
            'all' => 'budget/all/',
            'detail' => 'budget/detail/#ID#/',
            'download' => 'budget/download/',
            'history' => 'budget/history/',
            'edit' => 'budget/edit/#beId#/#articleId#/#year#/'
        );

        $arDefaultVariableAliases404 = [];
        $arDefaultVariableAliases    = [];
        $arVariableAliases = [];

        $arVariables = [];

        $arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $this->arParams['SEF_URL_TEMPLATES']);

        $componentPage = CComponentEngine::ParseComponentPath('/', $arUrlTemplates, $arVariables);

        if($componentPage === 'edit' && !$_REQUEST['IFRAME']){
            //LocalRedirect('/budget/');
        }

        CComponentEngine::InitComponentVariables(
            $componentPage,
            $arComponentVariables,
            $arVariableAliases,
            $arVariables);

        $this->arResult['VARIABLES'] = $arVariables;

        $this->IncludeComponentTemplate($componentPage);
    }

};
