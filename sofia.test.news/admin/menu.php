<?php

if ($APPLICATION->GetGroupRight("sofia.test.news") > "D") { 
    $aMenu = [
        "parent_menu" => "global_menu_sofia", 
        "sort" => 100,                    
        "url" => "sofia_test_news_list.php",  
        "text" => "SOFIA",       
        "title" => "SOFIA", 
        "icon" => "sofia_menu_icon", 
        "page_icon" => "sofia_menu_icon", 
        "items_id" => "global_menu_sofia",  
        "items" => [
            0 => [
                "title"     => "Новости",
                "text"      => "Новости",
                "url"       => "sofia_test_news_list.php?lang=" . LANGUAGE_ID,
                "icon"      => "update_marketplace",
                "page_icon" => "update_marketplace",
            ]
        ],          
    ];
    
    return $aMenu;
}

return false;

?>
