<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

// Получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("sofia.test.news");
// Если пользователю запрещен доступ - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

// Подключим модуль
\Bitrix\Main\Loader::includeModule("sofia.test.news");

// ID таблицы (название)
$sTableID = "sofia_test_news_news_table";
// Создадим основной объект сортировки, и установим сортировку по умолчанию
$oSort = new CAdminSorting($sTableID, "ID", "desc");
// Создадим основной объект списка с заданной сортировкой
$lAdmin = new CAdminList($sTableID, $oSort);

// Настроим фильтрацию списка
// Проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter()
{
    global $arFilter, $lAdmin;

    // У нас есть поле даты, для которого сделаны 2 отдельных фильтра, их выпишем отдельно
    $str = "";
    if ($_REQUEST["find_timestamp_x_1"] <> '') {
        if (!CheckDateTime($_REQUEST["find_timestamp_x_1"], CSite::GetDateFormat("FULL"))) {
            $str .= GetMessage("MAIN_EVENTLOG_WRONG_TIMESTAMP_X_FROM") . "<br>"; // Сообщение взято из другого модуля
        }
    }

    if ($_REQUEST["find_timestamp_x_2"] <> '') {
        if (!CheckDateTime($_REQUEST["find_timestamp_x_2"], CSite::GetDateFormat("FULL"))) {
            $str .= GetMessage("MAIN_EVENTLOG_WRONG_TIMESTAMP_X_TO") . "<br>"; // Сообщение взято из другого модуля
        }
    }

    if ($str <> '') {
        $lAdmin->AddFilterError($str);

        return false;
    }

    // Проверим остальные поля
    foreach ($arFilter as $f) {
        global $f;
    }

    return count($lAdmin->arFilterErrors) == 0;
}

// Опишем элементы фильтра
// Элементы фильтра - названия переменных, куда будут заноситься параметры фильтрации
$arFilter = Array(
    "find_id",
    "find_active",
    "find_title",
    "find_author_name",
    "find_text",
    "find_timestamp_x_1",
    "find_timestamp_x_2",
);

// Инициализируем фильтр
$lAdmin->InitFilter($arFilter);
// Если все значения фильтра корректны, обработаем его
if (CheckFilter()) {
    // Создадим массив фильтрации для выборки на основе значений фильтра
    // Проверим задана ли каждая переменная, чтобы не писать пустое значение
    $arFilter = [];
    if ($find_id) {
        $arFilter["ID"] = $find_id;
    }
    if ($find_active) {
        $arFilter["ACTIVE"] = $find_active;
    }
    if ($find_title) {
        $arFilter["TITLE"] = $find_title;
    }
    if ($find_author_name) {
        $arFilter["AUTHOR_NAME"] = $find_author_name;
    }
    if ($find_text) {
        $arFilter["TEXT"] = $find_text;
    }
    if ($find_timestamp_x_1 && !$find_timestamp_x_2) {
        $arFilter[">=DATE_INSERT"] = $find_timestamp_x_1;
    } else if (!$find_timestamp_x_1 && $find_timestamp_x_2) {
        $arFilter["<DATE_INSERT"] = $find_timestamp_x_2;
    } else if ($find_timestamp_x_1 && $find_timestamp_x_2) {
        $arFilter[">=DATE_INSERT"] = $find_timestamp_x_1;
        $arFilter["<DATE_INSERT"]  = $find_timestamp_x_2;
    }
}

// $by => $order - объявляются автоматически, но передаются в нижнем регистре, чтобы корректно сортировать, приводим их в верхний регистр
$arOrder = [];
if ($by && $order) {
    $arOrder[mb_strtoupper($by)] = mb_strtoupper($order);
}

// Обновление элементов из списка
// Сохранение отредактированных элементов
if ($lAdmin->EditAction() && $POST_RIGHT === "W") {
    // Пройдем по списку переданных элементов
    // $FIELDS объявляются автоматически
    foreach ($FIELDS as $ID => $arFields) {
        // Если элемент не обновляется, то пропускаем
        if (!$lAdmin->IsUpdated($ID)) {
            continue;
        }

        $ID = IntVal($ID);

        // Сохраним изменения каждого элемента
        // $DB - объявляется автоматически
        $DB->StartTransaction();
        $elem = new \Sofia\Test\News\NewsTable;
        if (($rsData = $elem->GetByID($ID)) && ($arData = $rsData->fetch())) {
            // Перед тем как сохранить обойдем поля и сформируем массив на отправку
            // Это нужно, т.к. ORM воспринимает только корректный формат, то есть, если у нас есть поле integer, то строку туда передавать нельзя, как и с датой
            foreach ($arFields as $key => $value) {
                $val = $value;
                if ($key == "AUTHOR_NAME") {
                    $val = intval($val);
                }
                if ($key == "DATE_INSERT") {
                    $val = new \Bitrix\Main\Type\DateTime($val);
                }
                $arData[$key] = $val;
            }
            // Обновим элемент по айди, передав новые параметры
            $res = $elem->Update($ID, $arData);
            if (!$res->isSuccess()) {
                // Если ошибка то выведем её и откатим операцию назад
                $lAdmin->AddGroupError("Ошибка обновления:" . " " . print_r($res->getErrorMessages(), true), $ID);
                $DB->Rollback();
            }
        } else {
            $lAdmin->AddGroupError("Ошибка обновления:  не удалось получить информацию элемента по его ID", $ID);
            $DB->Rollback();
        }
        $DB->Commit();
    }
}

// Обработка одиночных и групповых действий
// Доступно, если есть полные права на модуль
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT === "W") {
    // Если выбрано "Для всех элементов"
    if ($_REQUEST['action_target'] == 'selected') {
        $cData = new \Sofia\Test\News\NewsTable;
        $rsData = $cData->getList(array(
            "order"  => $arOrder,
            "filter" => $arFilter
        ));
        while ($arRes = $rsData->Fetch()) {
            $arID[] = $arRes['ID'];
        }
    }
    // Пройдем по списку элементов
    foreach ($arID as $ID) {
        if (strlen($ID) <= 0) {
            continue;
        }

        $ID = IntVal($ID);

        // Для каждого элемента совершим требуемое действие
        switch ($_REQUEST['action']) {
            // Удаление
            case "delete":
                @set_time_limit(0);
                $DB->StartTransaction();
                if(!\Sofia\Test\News\NewsTable::Delete($ID)->isSuccess()) {
                    $DB->Rollback();
                    $lAdmin->AddGroupError("Ошибка удаления", $ID);
                }
                $DB->Commit();

                break;
            // Активация / Деактивация
            case "activate":
            case "deactivate":
                $cData = new \Sofia\Test\News\NewsTable;
                if (($rsData = $cData->GetByID($ID)) && ($arFields = $rsData->Fetch())) {
                    $arFields["ACTIVE"] = ($_REQUEST['action'] == "activate" ? "Y" : "N");
                    $res = $cData->Update($ID, $arFields);
                    if (!$res->isSuccess()) {
                        $lAdmin->AddGroupError("Ошибка обновления: " . print_r($res->getErrorMessages(), true), $ID);
                    }
                } else {
                    $lAdmin->AddGroupError("Ошибка получения элемента при сохранении", $ID);
                }

                break;
        }
    }
}

// Делаем выборку по заданной сортировке и фильтру
$rsData = \Sofia\Test\News\NewsTable::getList(array(
    "order"  => $arOrder,
    "filter" => $arFilter
));
// Преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);
// Аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();
// Отправим вывод переключателя страниц в основной объект $lAdmin
// Текст указывается для отображения количества выведенных элементов из заданного
$lAdmin->NavText($rsData->GetNavPrint("Элементов"));

// Сформируем заголовки столбцов
// id 	   - Идентификатор колонки.
// content - Заголовок колонки.
// sort    - Значение параметра GET-запроса для сортировки.
// default - Параметр, показывающий, будет ли колонка по умолчанию отображаться в списке (true | false)
// align   - Куда прижмется текст колонки (left | right)
$lAdmin->AddHeaders(array(
    array(
        "id"       => "ID",
        "content"  => "ID",
        "sort"     => "id",
        "default"  => true,
    ),
    array(
        "id"       => "ACTIVE",
        "content"  => "Активность",
        "sort"     => "active",
        "default"  => true,
    ),
    array(
        "id"       => "TITLE",
        "content"  => "Заголовок",
        "sort"     => "title",
        "default"  => true,
    ),
    array(
        "id"       => "DATE_INSERT",
        "content"  => "Дата публикации",
        "sort"     => "date_insert",
        "default"  => true,
    ),
    array(
        "id"       => "AUTHOR_NAME",
        "content"  => "Автор",
        "sort"     => "author_name",
        "default"  => true,
    ),
    array(
        "id"       => "TEXT",
        "content"  => "Описание",
        "sort"     => "text",
        "align"    => "right",
        "default"  => true,
    ),

));

//  Передача списка элементов в основной объект осуществляется следующим образом:
//  Вызываем CAdminList::AddRow(). Результат метода - ссылка на пустой экземпляр класса CAdminListRow
//  Формируем поля строки, используя следующие методы класса CAdminListRow:
//      AddField - значение ячейки будет отображаться в заданном виде при просмотре и при редактировании списка
//      AddViewField - при просмотре списка значение ячейки будет отображаться в заданном виде
//      AddEditField - при редактировании списка значение ячейки будет отображаться в заданном виде
//      AddCheckField - значение ячейки будет редактироваться в виде чекбокса
//      AddSelectField - значение ячейки будет редактироваться в виде выпадающего списка
//      AddInputField - значение ячейки будет редактироваться в виде текстового поля с заданным набором атрибутов
//      AddCalendarField - значение ячейки будет редактироваться в виде поля для ввода даты
//  Формируем контекстное меню для строки (CAdminListRow::AddActions())
//  При формировании полей строки можно комбинировать различные методы для одного и того же поля.
//  Контекстное меню элемента задается массивом, элементы которого представлюят собой ассоциативные массивы со следующим набором ключей:
//      ICON 	    Имя CSS-класса с иконкой действия.
//      DISABLED 	Флаг "пункт меню заблокирован" (true|false).
//      DEFAULT 	Флаг "пункт меню является действием по умолчанию" (true|false). При двойном клике по строке сработает действие по умолчанию.
//      TEXT 	    Название пункта меню.
//      TITLE 	    Текст всплывающей подсказки пункта меню.
//      ACTION 	    Действие, производимое по выбору пункта меню (Javascript).
//      SEPARATOR 	Вставка разделителя {true|false}. При значении, равном true, остальные ключи пункта меню будут проигнорированы.
while ($arRes = $rsData->NavNext(true, "f_")) {
    // Создаем строку. результат - экземпляр класса CAdminListRow
    // $f_ID и другие, типа f_NAME, в зависимости от того, объявляются автоматически
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    // Далее настроим отображение значений при просмотре и редактировании списка

    // Параметр NAME будет редактироваться как текст, а отображаться ссылкой
  
    $row->AddCheckField("ACTIVE");

    $row->AddInputField(
        "TITLE",
        array("size" => 20)
    );
    $row->AddViewField(
        "TITLE",
        '<a href="sofia_test_news_edit.php?ID=' . $f_ID . '&lang=' . LANG . '">' . $f_TITLE . '</a>'
    );

    $row->AddCalendarField("DATE_INSERT");
    $row->AddInputField("AUTHOR_NAME", array("size" => 20));
    $row->AddInputField("TEXT", array("size" => 40));



    // Сформируем контекстное меню
    $arActions = array();
    // Редактирование элемента
    $arActions[] = array(
        "ICON" => "edit",
        "DEFAULT" => true,
        "TEXT" => "Редактировать",
        "ACTION" => $lAdmin->ActionRedirect("sofia_test_news_edit.php?ID=" . $f_ID)
    );
    // Удаление элемента
    if ($POST_RIGHT >= "W") {
        $arActions[] = array(
            "ICON" => "delete",
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('" . "Удалить" . "')) " . $lAdmin->ActionDoGroup($f_ID, "delete")
        );
    }

    // Вставим разделитель
    $arActions[] = array("SEPARATOR" => true);

    // Если последний элемент - разделитель, почистим мусор.
    if (is_set($arActions[count($arActions) - 1], "SEPARATOR")) {
        unset($arActions[count($arActions) - 1]);
    }

    // Применим контекстное меню к строке
    $row->AddActions($arActions);
}

// Резюме таблицы
// Резюме таблицы формируется в виде массива, элементами которого являются ассоциативные массивы с ключами
// "title" - название параметра - и "value" - значение параметра.
// Кроме того, ассоциативный массив может содержать элемент с ключом "counter" и значением true.
// В этом случае, элемент резюме будет счетчиком отмеченных элементов таблицы и значение будет динамически изменяться.
// Прикрепляется резюме вызовом метода CAdminList::AddFooter().
$lAdmin->AddFooter(
    array(
        // Кол-во элементов
        array(
            "title" => "Выбрано",
            "value" => $rsData->SelectedRowsCount()
        ),
        // Счетчик выбранных элементов
        array(
            "counter" => true,
            "title"   => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
            "value"   => "0"
        ),
    )
);

// Групповые действия
$lAdmin->AddGroupActionTable(Array(
    "delete" => "Удалить", // Удалить выбранные элементы
    "activate" => "Активировать", // Активировать выбранные элементы
    "deactivate" => "Деактивировать", // Деактивировать выбранные элементы
));

// Задание параметров административного меню
// Также можно задать административное меню, которое обычно отображается над таблицей со списком (только если у текущего пользователя есть права на редактирование).
// Административное формируется в виде массива, элементами которого являются ассоциативные массивы с ключами:
// Сформируем меню из одного пункта - добавление рассылки
// Аналогичное меню выводили сверху
// Задается текст при наведении, заголовок кнопки, ссылка куда ведет и тип, может принимать разные типы для кнопок
$aMenu = array(
    array(
        "TEXT"  => "К списку",
        "TITLE" => "К списку",
        "LINK"  => "/bitrix/admin/sofia_test_news_list.php?lang=".LANGUAGE_ID,
        "ICON"  => "btn_list",
    ),
    array(
        "TEXT"  => "Добавить",
        "TITLE" => "Добавить",
        "LINK"  => "/bitrix/admin/sofia_test_news_edit.php?lang=".LANGUAGE_ID,
        "ICON"  => "btn_new", // Другие типы: btn_list, btn_delete
    )
);

// И прикрепим его к списку
$lAdmin->AddAdminContextMenu($aMenu);
// Альтернативный вывод
$lAdmin->CheckListMode();
// Установка заголовка
$APPLICATION->SetTitle("Тестовый модуль новостей - список новостей");

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

// Создадим объект фильтра
$oFilter = new CAdminFilter(
    $sTableID."_filter",
    array(
        "ID",
        "ACTIVE",
        "TITLE",
        "AUTHOR_NAME",
        "TEXT",
        "DATE_INSERT",
    )
);
// Выведем фильтр
?>
<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <? $oFilter->Begin(); ?>
    <tr>
        <td>ID</td>
        <td>
            <input type="text" name="find_id" size="47" value="<?= htmlspecialcharsbx($find_id) ?>">
        </td>
    </tr>
    <tr>
        <td>Активность</td>
        <td>
            <?
            $arr = array(
                "reference" => array(
                    "Да",
                    "Нет",
                ),
                "reference_id" => array(
                    "Y",
                    "N",
                )
            );
            echo SelectBoxFromArray("find_active", $arr, $find_active, "Все", "");
            ?>
        </td>
    </tr>
    <tr>
        <td>Заголовок</td>
        <td><input type="text" name="find_title" size="47" value="<?= htmlspecialcharsbx($find_title) ?>"></td>
    </tr>
    <tr>
        <td>Автор</td>
        <td><input type="text" name="find_author_name" size="47" value="<?= htmlspecialcharsbx($find_author_name) ?>"></td>
    </tr>
    <tr>
        <td>Текст новости</td>
        <td><input type="text" name="find_text" size="47" value="<?= htmlspecialcharsbx($find_text) ?>"></td>
    </tr>
    <tr>
        <td>Дата публикации:</td>
        <td><? echo CAdminCalendar::CalendarPeriod("find_timestamp_x_1", "find_timestamp_x_2", $find_timestamp_x_1, $find_timestamp_x_2, false, 15, true) ?></td>
    </tr>
    <?
    // Выведем кнопки фильтра
    $oFilter->Buttons(
        array(
            "table_id" => $sTableID,
            "url"      => $APPLICATION->GetCurPage(),
            "form"     => "find_form"
        )
    );
    $oFilter->End();
    ?>
</form>
<?
// Выведем таблицу списка элементов
$lAdmin->DisplayList();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
?>
