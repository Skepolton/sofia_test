<?php
namespace Sofia\Test\News;

class Agent
{
    public static function checkEmptyAuthor()
    {   $email_to = \COption::GetOptionString("main", "email_from");
        $res = \Sofia\Test\News\NewsTable::getList([
            "select" => ["ID"],
            "filter" => ["AUTHOR_NAME" => NULL],
        ]);
        $result = $res->fetchAll();
        foreach ($result as $news) {
            $ids[] = $news["ID"];
        }
        $newsID = implode(", ",$ids);
        $text = "На сайте таком то присутствуют новости без указания Автора статьи, id статей: ".$newsID;
        
        //Закомментирую что бы не спамить
        //mail($email_to,"Новости без указания автора","На сайте таком то присутствуют новости без указания Автора статьи, id статей: ".$newsID);
        return "\Sofia\\Test\\News\Agent::checkEmptyAuthor();";
    }
}
