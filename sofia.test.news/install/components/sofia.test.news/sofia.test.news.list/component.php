<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {
    if ($arParams["SET_TITLE"] == "Y") {
        $APPLICATION->SetTitle("Тестовое задание - Список новостей");
    }

    $elementsPerPage = ($arParams["PER_PAGE"]) ? $arParams["PER_PAGE"] : 5;
    $filter = [];
    if (!empty($arParams["DATE_FROM"]) || !empty($arParams["DATE_TO"])) {
        $filter = [">=DATE_INSERT" => $arParams["DATE_FROM"], "<=DATE_INSERT" => $arParams["DATE_TO"]];
    }
    $nav = new \Bitrix\Main\UI\PageNavigation("page");
    $nav->allowAllRecords(true)
        ->setPageSize($elementsPerPage)
        ->initFromUri();
    $arOrder =[
        "ID" => "DESC"
    ];
    $ttl = $arParams["CACHE_TIME"] ?? 0;

    if ($this->StartResultCache($ttl, $nav, 'sofia_news_list')) {
        $result = \Sofia\Test\News\NewsTable::getList([
            "order" => $arOrder,
            "filter" => $filter,
            "limit" => $nav->getLimit(),
            "offset" => $nav->getOffset(),
            "count_total" => true,
            "cache"=> [
                "ttl"=> $ttl
            ]
        ]);
        $nav->setRecordCount($result->getCount());
        $arResult["ITEMS"] = $result->fetchAll();
        $arResult["NAV"] = $nav;
        $this->IncludeComponentTemplate();
    }
}
?>
