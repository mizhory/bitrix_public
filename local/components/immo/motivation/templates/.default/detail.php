<?php

use Immo\Component\Motivation;

/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 * @var $USER CUser
 * @var $component Motivation
 *
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
?>
<?php
$APPLICATION->IncludeComponent(
    'immo:motivation.detail',
    '',
    [
        'ID' => $arResult['VARIABLES']['ID'],
        'LIST_URL' => '/sheets/motivation/'
    ],
    $component,
    ['HIDE_ICONS' => 'Y'],
);
?><?php
$this->SetViewTarget('pagetitle'); ?>
    <div class="pagetitle-container pagetitle-align-right-container ">
        <a href="/sheets/motivation/" class="ui-btn ui-btn-default">К СПИСКУ</a>
    </div>
<? $this->EndViewTarget(); ?>