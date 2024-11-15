<?php
namespace Sofia\Test\News;

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
