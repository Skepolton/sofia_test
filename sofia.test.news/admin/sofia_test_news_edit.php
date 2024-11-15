<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$POST_RIGHT = $APPLICATION->GetGroupRight("sofia.test.news");
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);
$aTabs = [
    [
        "DIV" => "edit1",
        "TAB" => GetMessage("TAB1"),
        "ICON" => "main_user_edit",
        "TITLE" => GetMessage("TAB1")
    ]
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);

\Bitrix\Main\Loader::includeModule("sofia.test.news");

$ID = intval($ID);
$message = null;
$bVarsFromForm = false;
$curDate = new \Bitrix\Main\Type\DateTime();
$curDateString = $curDate->format("d.m.Y H:i:s");

if (
    $REQUEST_METHOD == "POST"        
    && ($save != "" || $apply != "") 
    && ($POST_RIGHT === "W")         
    && check_bitrix_sessid()         
) {
    $newsTableObject = new \Sofia\Test\News\NewsTable;

    $arFields = [
        "ACTIVE" => ($ACTIVE <> "Y" ? "N" : "Y"),
        "TITLE" => $TITLE,
        "AUTHOR_NAME" => $AUTHOR_NAME,
        "TEXT" => $TEXT,
        "DATE_INSERT" => new \Bitrix\Main\Type\DateTime($DATE_INSERT),
    ];

    if ($ID > 0) {
        $res = $newsTableObject->Update($ID, $arFields);
    } else {
        $res = $newsTableObject->Add($arFields);
        if ($res->isSuccess()) {
            $ID = $res->getId();
        }
    }

    if ($res->isSuccess()) {
        if ($apply != "") {
            LocalRedirect(
                "/bitrix/admin/sofia_test_news_edit.php?ID=" .
                $ID .
                "&mess=ok" .
                "&lang=" . LANG .
                "&" . $tabControl->ActiveTabParam()
            );
        } else {
            LocalRedirect("/bitrix/admin/sofia_test_news_list.php?lang=" . LANG);
        }
    } else {
        if ($e = $APPLICATION->GetException()) {
            $message = new CAdminMessage("Ошибка сохранения", $e);
        } else {
            $mess = print_r($res->getErrorMessages(), true);
            $message = new CAdminMessage("Ошибка сохранения: " . $mess);
        }
        $bVarsFromForm = true;
    }
}

$str_ACTIVE = "Y";
$str_DATE_INSERT = $curDateString;
$str_TITLE = "";
$str_TEXT = "";
$str_AUTHOR_NAME = "";

if ($ID > 0) {
    $result = \Sofia\Test\News\NewsTable::GetByID($ID);
    if (!empty($result)) {
        $el = $result->fetch();
        $str_ACTIVE = $el["ACTIVE"];
        $str_DATE_INSERT = $el["DATE_INSERT"];
        $str_TITLE = $el["TITLE"];
        $str_TEXT = $el["TEXT"];
        $str_AUTHOR_NAME = $el["AUTHOR_NAME"];
    } else {
        $ID = 0;
    }
}

if ($bVarsFromForm) {
    $DB->InitTableVarsForEdit("sofia_test_news_news_table", "", "str_");
}

$APPLICATION->SetTitle(($ID > 0 ? "Редактирование " . $el["TITLE"] : "Добавление"));

$aMenu = array(
    array(
        "TEXT"  => "К списку",
        "TITLE" => "К списку",
        "LINK"  => "sofia_test_news_list.php?lang=" . LANG,
        "ICON"  => "btn_list",
    )
);

if ($ID > 0) {
    $aMenu[] = array("SEPARATOR"=>"Y");

    $aMenu[] = array(
        "TEXT"  => "Добавить",
        "TITLE" => "Добавить",
        "LINK"  => "sofia_test_news_edit.php?lang=" . LANG,
        "ICON"  => "btn_new",
    );

    $aMenu[] = array(
        "TEXT"  => "Удалить",
        "TITLE" => "Удалить",
        "LINK"  => "javascript:if(confirm('" . "Подтвердить удаление?" . "')) " . "window.location='sofia_test_news_list.php?ID=" . $ID . "&action=delete&lang=" . LANG . "&" . bitrix_sessid_get() . "';",
        "ICON"  => "btn_delete",
    );

    $aMenu[] = array("SEPARATOR" => "Y");
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($_REQUEST["mess"] == "ok" && $ID > 0) {
    CAdminMessage::ShowMessage(array("MESSAGE" => "Сохранено успешно", "TYPE" => "OK"));
}

if ($message) {
    echo $message->Show();
} elseif ($el->LAST_ERROR != "") {
    CAdminMessage::ShowMessage($el->LAST_ERROR);
}
?>
<form method="POST" Action="<?= $APPLICATION->GetCurPage() ?>" ENCTYPE="multipart/form-data" name="post_form">
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <? if ($ID > 0 && !$bCopy): ?>
        <input type="hidden" name="ID" value="<?= $ID ?>">
    <? endif; ?>
    <?
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%">Активность</td>
        <td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<? if ($str_ACTIVE === "Y") echo " checked" ?>></td>
    </tr>
    <tr>
        <td width="40%"><span class="required">*</span>Дата публикации<?= " (" . FORMAT_DATETIME . "):" ?></td>
        <td width="60%"><?= CalendarDate("DATE_INSERT", $str_DATE_INSERT, "post_form", "20") ?></td>
    </tr>
    <tr>
        <td width="40%">Автор</td>
        <td width="60%"><input type="text" name="AUTHOR_NAME" value="<?= $str_AUTHOR_NAME ?>"></td>
    </tr>
    <tr>
        <td width="40%">Заголовок новости</td>
        <td width="60%"><input type="text" name="TITLE" value="<?= $str_TITLE ?>"></td>
    </tr>
    <tr>
        <td width="40%">Текст новости</td>
        <td width="60%"><textarea class="typearea" cols="45" rows="5" wrap="VIRTUAL" name="TEXT"><?= $str_TEXT ?></textarea></td>
    </tr>
</form>

<?
$tabControl->Buttons(
    array(
        "disabled" => ($POST_RIGHT < "W"),
        "back_url" => "sofia_test_news_list.php?lang=" . LANG,
    )
);
$tabControl->End();
$tabControl->ShowWarnings("post_form", $message);
?>

<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
?>
