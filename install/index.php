<?php
declare(strict_types=1);

use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Simbirsoft\Queue\Options;
use Simbirsoft\Queue\QueueAgent;
use Simbirsoft\Queue\QueueTable;

Loc::loadMessages(__FILE__);

class simbirsoft_queue extends CModule
{
    /** @var string */
    public $MODULE_ID = 'simbirsoft.queue';

    /**
     * simbirsoft_queue constructor.
     */
    public function __construct()
    {
        if (is_file(__DIR__.'/version.php')) {
            /** @var array $arModuleVersion */
            include __DIR__ .'/version.php';

            $this->MODULE_VERSION = $arModuleVersion['VERSION'] ?? '0.0.1';
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'] ?? '2019-10-01';

            $this->MODULE_NAME = Loc::getMessage('QUEUE_NAME');
            $this->MODULE_DESCRIPTION = Loc::getMessage('QUEUE_DESCRIPTION');
            $this->MODULE_GROUP_RIGHTS = 'N';

            $this->PARTNER_NAME = Loc::getMessage('QUEUE_PARTNER_NAME');
            $this->PARTNER_URI = 'https://www.simbirsoft.com/';
        } else {
            (new CAdminMessage(Loc::getMessage('QUEUE_FILE_NOT_FOUND', ['#FILE#' => 'version.php'])))->Show();
        }
    }

    /**
     * @return bool
     */
    public function DoInstall(): bool
    {
        try {
            ModuleManager::registerModule($this->MODULE_ID);

            if (!Loader::includeModule($this->MODULE_ID)) {
                throw new RuntimeException('Module "'. $this->MODULE_ID .'" not loaded!');
            }

            $this->InstallEvents();
            $this->InstallDB();
            Options::init();

            return true;
        } catch(Throwable $exception) {
            (new CAdminMessage($exception->getMessage()))->Show();
            return false;
        }
    }

    /**
     * @return bool
     */
    public function DoUninstall(): bool
    {
        try {
            if (!Loader::includeModule($this->MODULE_ID)) {
                throw new RuntimeException('Module "'. $this->MODULE_ID .'" not loaded!');
            }

            Options::destroy();
            $this->UnInstallDB();
            $this->UnInstallEvents();
            ModuleManager::unRegisterModule($this->MODULE_ID);

            return true;
        } catch(Throwable $exception) {
            (new CAdminMessage($exception->getMessage()))->Show();
            return false;
        }
    }

    /**
     * @return void
     *
     * @throws Main\ArgumentException
     * @throws Main\SystemException
     */
    public function InstallDB()
    {
        $connection = Application::getConnection();
        $table = Base::getInstance(QueueTable::class);
        if (!$connection->isTableExists($table->getDBTableName())) {
            $table->createDBTable();
        }
        CAgent::AddAgent(
            QueueAgent::getAgent(),
            $this->MODULE_ID,
            'N',
            10,
            new Type\DateTime(),
            'Y',
            new Type\DateTime()
        );
    }

    /**
     * @return void
     *
     * @throws Main\ArgumentException
     * @throws Main\Db\SqlQueryException
     * @throws Main\SystemException
     */
    public function UnInstallDB()
    {
        $connection = Application::getConnection();
        $table = Base::getInstance(QueueTable::class);
        if ($connection->isTableExists($table->getDBTableName())) {
            $connection->dropTable($table->getDBTableName());
        }
        CAgent::RemoveModuleAgents($this->MODULE_ID);
    }
}
