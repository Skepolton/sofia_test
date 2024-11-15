<?php
namespace Sofia\Test\News;

class Agent
{
    public static function checkEmptyAuthor()
    {

        return "\Sofia\\Test\\News\Agent::checkEmptyAuthor();"; // Функция обязательно должна возвращать имя по которому вызывается, иначе битрикс её удаляет
    }
}
