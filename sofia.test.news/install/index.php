<?php
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class sofia_test_news extends CModule
{
    public $arResponse = [
        "STATUS" => true,
        "MESSAGE" => ""
    ];

    public function setResponse($status, $message = "")
    {
        $this->arResponse["STATUS"] = $status;
        $this->arResponse["MESSAGE"] = $message;
    }

    public function __construct()
    {
        $arModuleVersion = [];
        require (__DIR__ . "/version.php");
                
        $this->MODULE_ID = "sofia.test.news"; 

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("SOFIA_TEST_NEWS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("SOFIA_TEST_NEWS_MODULE_DESCRIPTION");
        
        $this->PARTNER_NAME = Loc::getMessage("SOFIA_TEST_NEWS_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("SOFIA_TEST_NEWS_PARTNER_URI");
        
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";
        $this->MODULE_GROUP_RIGHTS = "Y";
    }
    
    public function installFiles()
    {
        $this->unInstallFiles();
        
        $resMsg = "";
        $res = CopyDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin",
            true, 
            true  
        );
        $res = CopyDirFiles(
            __DIR__ . '/themes', 
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes",
            true,
            true
        );
        if (!$res) {
            $resMsg = Loc::getMessage("SOFIA_TEST_NEWS_INSTALL_ERROR_FILES_ADM");
        }
        
        $res = CopyDirFiles(
            __DIR__ . "/components",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components",
            true, 
            true  
        );
        if (!$res) {
            $resMsg = ($resMsg) ? $resMsg . "; " . Loc::getMessage("SOFIA_TEST_NEWS_INSTALL_ERROR_FILES_COM") : Loc::getMessage("SOFIA_TEST_NEWS_INSTALL_ERROR_FILES_COM");
        }

        if ($resMsg) {
            $this->setResponse(false, $resMsg);
            return false;
        }

        $this->setResponse(true);
        return true;
    }
    
    public function unInstallFiles()
    {
        $res = true;
        $resMsg = "";
        DeleteDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
        DeleteDirFiles(
            __DIR__ . "/themes",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes"
        );
        if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/" . $this->MODULE_ID))
            $res = DeleteDirFilesEx("/bitrix/components/" . $this->MODULE_ID);
        if (!$res)
            $resMsg = Loc::getMessage("SOFIA_TEST_NEWS_UNINSTALL_ERROR_FILES_COM");
        if ($resMsg) {
            $this->setResponse(false, $resMsg);
            return false;
        }
        $this->setResponse(true);
        return true;
    }
    
    public function installDB()
    {
        Loader::includeModule($this->MODULE_ID);
        
        if (!Application::getConnection(\Sofia\Test\News\NewsTable::getConnectionName())->isTableExists(Base::getInstance("\Sofia\Test\News\NewsTable")->getDBTableName())) {
            Base::getInstance("\Sofia\Test\News\NewsTable")->createDbTable(); 
        }
    }
    
    public function unInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);
        Application::getConnection(\Sofia\Test\News\NewsTable::getConnectionName())->queryExecute('DROP TABLE IF EXISTS ' . Base::getInstance("\Sofia\Test\News\NewsTable")->getDBTableName());
    }
        
    public function DoInstall()
    {
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        
        if ($request["step"] < 2) {
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/sofia.test.news/install/step1.php")) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("SOFIA_TEST_NEWS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/sofia.test.news/install/step1.php");
            } else {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("SOFIA_TEST_NEWS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sofia.test.news/install/step1.php");
            }
        } elseif ($request["step"] == 2) {
            ModuleManager::registerModule($this->MODULE_ID);
            $this->installDB();
            $this->installAgents();
            $this->InstallEvents();

            if (!$this->installFiles()) {
                $APPLICATION->ThrowException($this->arResponse["MESSAGE"]);
            }
            if ($request["add_data"] == "Y") {
                $result = $this->installDemoNews();
                if ($result !== true) {
                    $APPLICATION->ThrowException($result);
                }
            }
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/sofia.test.news/install/step2.php")) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("SOFIA_TEST_NEWS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/sofia.test.news/install/step2.php");
            } else {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("SOFIA_TEST_NEWS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sofia.test.news/install/step2.php");
            }
        }
    }
        
    public function DoUninstall()
    {
        global $APPLICATION;
        
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        
        if ($request["step"] < 2) {
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/sofia.test.news/install/unstep1.php")) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("SOFIA_TEST_NEWS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/sofia.test.news/install/unstep1.php");
            } else {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("SOFIA_TEST_NEWS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sofia.test.news/install/unstep1.php");
            }
        } elseif ($request["step"] == 2) {
            $this->unInstallAgents();
            $this->UnInstallEvents();
            
            if ($request["save_data"] != "Y") {
                Option::delete($this->MODULE_ID);
                $this->unInstallDB();
            }

            if (!$this->unInstallFiles()) {
                $APPLICATION->ThrowException($this->arResponse["MESSAGE"]);
            }

            ModuleManager::unRegisterModule($this->MODULE_ID);

            if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/modules/sofia.test.news/install/unstep2.php")) {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("SOFIA_TEST_NEWS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/sofia.test.news/install/unstep2.php");
            } else {
                $APPLICATION->IncludeAdminFile(Loc::getMessage("SOFIA_TEST_NEWS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sofia.test.news/install/unstep2.php");
            }
        }
    }

    public function installAgents()
    {
        \CAgent::AddAgent("\Sofia\Test\News\Agent::checkEmptyAuthor();", $this->MODULE_ID, "N", 86400, "", "Y", "", 100);
    }

    public function unInstallAgents()
    {
        \CAgent::RemoveModuleAgents($this->MODULE_ID);
    }

    public function InstallEvents()
    {
            RegisterModuleDependences('main', 'OnBuildGlobalMenu', 'sofia.test.news', '\Sofia\Test\News\Events', 'modifyAdminMenu');
    }

    public function UnInstallEvents()
    {
            UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', 'sofia.test.news', '\Sofia\Test\News\Events', 'modifyAdminMenu');
    }
    
    public function installDemoNews()
    {
        Loader::includeModule($this->MODULE_ID);
        for ($i = 0; $i < 50; $i++) {
            $curDate = new \Bitrix\Main\Type\DateTime;
            $dateInsert = $curDate->add('-'.rand(1,50).' days');
            $authors = ["Лёша","Федя","Вася"];
            $text = "Описание тестовой новости " . $i . " ";
            $result = \Sofia\Test\News\NewsTable::add(
                array(
                    "ACTIVE" => "Y",
                    "DATE_INSERT" => $dateInsert,
                    "TITLE" => "Тестовая новость " . $i,
                    "TEXT" => $text.$text.$text.$text,
                    "AUTHOR_NAME" => $authors[rand(0,2)],
                )
            );
            $result = $this->checkAddResult($result);
            if (is_array($result) && !$result[0]) {
                return $result[1];
            } elseif (!is_array($result)) {
                return "Ошибка добавления демо записи";
            }
        }
        return true;
    }

    public function checkAddResult($result)
    {
        if ($result->isSuccess()) {
            return [true, $result->getId()];
        }
        return [false, $result->getErrorMessages()];
    }
    
    public function GetModuleRightList()
    {
        return array(
            "reference_id" => Array("D", "K", "S", "W"),
            "reference" => Array(
                "[D] " . Loc::getMessage("SOFIA_TEST_NEWS_DENIED"),
                "[K] " . Loc::getMessage("SOFIA_TEST_NEWS_READ_COMPONENT"),
                "[S] " . Loc::getMessage("SOFIA_TEST_NEWS_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("SOFIA_TEST_NEWS_FULL"),
            )
        );
    }

}
?>
