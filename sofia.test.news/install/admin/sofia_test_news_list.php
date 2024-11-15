<?
if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sofia.test.news/")) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sofia.test.news/admin/sofia_test_news_list.php");
} elseif (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/sofia.test.news/")) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/sofia.test.news/admin/sofia_test_news_list.php");
}
?>
