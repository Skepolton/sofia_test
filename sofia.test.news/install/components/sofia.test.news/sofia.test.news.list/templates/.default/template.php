<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// Включаем голосование "за" технологии композитный сайт
$this->setFrameMode(true);

if (!empty($arResult["ITEMS"])): ?>
    <? foreach ($arResult["ITEMS"] as $item): ?>
        <div class="news_element">
            <span class="date"><?= $item["DATE_INSERT"];?></span>
            <h3><?= $item["TITLE"]; ?></h3>
            <span class="descr"><?= $item["TEXT"]; ?></span>
        </div>
    <?php endforeach; ?>
    <?php
        $APPLICATION->IncludeComponent(
            "bitrix:main.pagenavigation",
            ".default",
            array(
                'NAV_TITLE'   => 'Новости',
                "NAV_OBJECT"  => $arResult["NAV"],
                "SEF_MODE" => "N",
            ),
            $component
        );
    ?>
<?php endif; ?>
