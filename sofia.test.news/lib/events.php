<?php
namespace Sofia\Test\News;

// Класс события
// Для примера выводит поля при каком-либо действии (в регистраторе задано перед добавлением)

class Events
{
    public static function modifyAdminMenu(&$adminMenu, &$moduleMenu)
    {
        $adminMenu['global_menu_sofia'] = [
            'menu_id' => 'sofia',
            'text' => 'SOFIA',
            'title' => 'SOFIA',
            'url' => 'sofia_test_news_list.php',
            'sort' => 99999,
            'items_id' => 'global_menu_sofia',
            'help_section' => 'SOFIA',
            'items' => []
        ];
    }
}
