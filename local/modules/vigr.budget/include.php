<?php
defined('B_PROLOG_INCLUDED') || die;

define('USER_BE',11);
define('FD_GROUP',17);

CModule::AddAutoloadClasses(
    "vigr.budget",
    array(
        "Vigr\\Budget\\FileDownloader" => "lib/budget/FileDownloader.php",
        "Vigr\\Budget\\FileDownloaderExcel" => "lib/budget/FileDownloaderExcel.php",
        "Vigr\\Budget\\Internals\\BudgetTable" => "lib/internals/BudgetTable.php",
        "Vigr\\Budget\\Budget" => "lib/budget/Budget.php",
        "Vigr\\Budget\\Deal" => "lib/budget/Deal.php",
        "Vigr\\Budget\\History" => "lib/budget/History.php",
        "Vigr\\Budget\\Internals\\BudgetTechTable" => "lib/internals/BudgetTechTable.php",
        "Vigr\\Budget\\Internals\\HistoryTable" => "lib/internals/HistoryTable.php",
        "Vigr\\Budget\\Traits\\ErrorTrait" => "lib/traits/ErrorTrait.php",
        "Vigr\\Budget\\Traits\\AjaxTrait" => "lib/traits/AjaxTrait.php",
    )
);

?>