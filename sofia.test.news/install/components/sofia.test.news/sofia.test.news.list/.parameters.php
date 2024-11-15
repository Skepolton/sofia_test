<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"GROUPS" => Array(),
	"PARAMETERS" => Array(
        "PER_PAGE" => array(
            "PARENT"  => "BASE",
            "NAME"    => "Количество элементов для вывода",
            "TYPE"    => "STRING",
            "DEFAULT" => 5,
        ),
        "DATE_FROM" => array(
            "PARENT"  => "BASE",
            "NAME"    => "Выводить с даты (ДД.ММ.ГГГГ)",
            "TYPE"    => "TIME",
            "DEFAULT" => (new \Bitrix\Main\Type\DateTime())->add("-30 day")->format("d.m.Y"),
        ),
        "DATE_TO" => array(
            "PARENT"  => "BASE",
            "NAME"    => "Выводить по дату (ДД.ММ.ГГГГ)",
            "TYPE"    => "TIME",
            "DEFAULT" => (new \Bitrix\Main\Type\DateTime())->format("d.m.Y"),
        ),
        "AJAX_MODE" => array(),
        "SET_TITLE" => array(),
        "CACHE_TIME" => array(),
	),
);
?>
