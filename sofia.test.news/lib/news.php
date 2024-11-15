<?php
namespace Sofia\Test\News;

use \Bitrix\Main\Entity;

class NewsTable extends Entity\DataManager
{
    public static $config;

    public function __construct()
    {
        self::$config = \Bitrix\Main\Config\Option::getForModule('sofia.test.news');
    }

    public static function getTableName()
    {
        return "sofia_test_news";
    }
    
    public static function getConnectionName()
    {
        return "default";
    }
    
    public static function getMap()
    {
        return [
            new Entity\IntegerField(
                "ID", 
                [
                    "primary" => true,
                    "autocomplete" => true,
                ]
            ),
            new Entity\BooleanField(
                'ACTIVE',
                [
                    'values' => ['N', 'Y'],
                    'default_value' => "Y"
                ]
            ),
            new Entity\DatetimeField(
                'DATE_INSERT', 
                [
                    'required' => true,
                    'default_value' => new \Bitrix\Main\Type\DateTime()
                ]
            ),
            new Entity\StringField(
                "TITLE", 
                [   
                    "required" => true,
                ]
            ),
            new Entity\TextField("TEXT"),
            new Entity\StringField("AUTHOR_NAME"),
        ];
    }

    public static function onAfterAdd(Entity\Event $event)
    {
        self::clearCache();
    }

    public static function onAfterUpdate(Entity\Event $event)
    {
        self::clearCache();
    }

    public static function onAfterDelete(Entity\Event $event)
    {
        self::clearCache();
    }

    public static function clearCache()
    {
        if (self::$config["cache_clear"] == "Y") {
            $cache = \Bitrix\Main\Data\Cache::createInstance();
            $cache->cleanDir("sofia_news_list");
        }
    }

}