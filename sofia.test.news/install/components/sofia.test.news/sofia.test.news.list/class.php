<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

class SofiaTestNewsList extends CBitrixComponent
{
    protected function checkModule()
    {
        if (!Loader::includeModule("sofia.test.news")) {
            ShowError(Loc::getMessage("SOFIA_TEST_NEWS_MODULE_NOT_INSTALLED"));
            return false;
        }
        return true;
    }
}
?>
