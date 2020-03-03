<?php
declare(strict_types=1);

namespace Simbirsoft\Queue;

use CAllMain;
use CEventLog;
use Exception;
use Bitrix\Main;
use CAdminTabControl;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/options.php');

class Options
{
    /** Название модуля */
    const MODULE_ID = 'simbirsoft.queue';

    /** @var HttpRequest Объект запроса */
    protected $request;
    /** @var array Вкладки и их поля */
    protected $tabs;

    /**
     * Options constructor.
     *
     * @throws Main\SystemException
     */
    public function __construct()
    {
        $this->checkPermission();

        $this->request = Application::getInstance()->getContext()->getRequest();
        $this->tabs = static::getTabs();

        if ($this->request->isPost() && check_bitrix_sessid()) {
            if ($this->request['apply']) {
                $this->save();
            } elseif ($this->request['default']) {
                static::init();
            }
            LocalRedirect($this->request->getRequestUri());
        }

        $this->draw();
    }

    /**
     * Проверка прав.
     *
     * @return void
     */
    protected function checkPermission()
    {
        global $USER, $APPLICATION;
        if (!$USER->isAdmin() || CAllMain::GetGroupRight(static::MODULE_ID) === 'D') {
            $APPLICATION->authForm(Loc::getMessage('ACCESS_DENIED'));
        }
    }

    /**
     * Получить вкладки и их поля.
     *
     * @return array
     */
    protected static function getTabs() : array
    {
        return [
            [
                'DIV'     => 'edit1',
                'TAB'     => Loc::getMessage('QUEUE_OPTIONS_TAB_GENERAL'),
                'TITLE'   => Loc::getMessage('QUEUE_OPTIONS_TAB_GENERAL'),
                'OPTIONS' => [
                    [
                        'worker',
                        Loc::getMessage('QUEUE_OPTIONS_QUEUE_WORKER'),
                        Queue::class,
                        [
                            'selectbox',
                            [
                                Queue::class => Loc::getMessage('QUEUE_OPTIONS_QUEUE_WORKER_AGENT'),
                            ],
                        ],
                    ],
                    [
                        'delay',
                        Loc::getMessage('QUEUE_OPTIONS_QUEUE_DELAY'),
                        '300',
                        ['text', 5],
                    ],
                    [
                        'tries',
                        Loc::getMessage('QUEUE_OPTIONS_QUEUE_TRIES'),
                        '0',
                        ['text', 5],
                    ],
                    [
                        'sleep',
                        Loc::getMessage('QUEUE_OPTIONS_QUEUE_SLEEP'),
                        '0',
                        ['text', 5],
                    ],
                ],
            ],
        ];
    }

    /**
     * Выводим форму.
     *
     * @return void
     */
    protected function draw()
    {
        $tabControl = new CAdminTabControl('tabControl', $this->tabs);
        $tabControl->begin();
        ?>
        <form method="post">
            <?=bitrix_sessid_post()?>
            <?php
            foreach ($this->tabs as $tab) {
                if ($tab['OPTIONS']) {
                    $tabControl->beginNextTab();
                    __AdmSettingsDrawList(static::MODULE_ID, $tab['OPTIONS']);
                }
            }
            $tabControl->buttons();
            ?>
            <input type="submit" name="apply" class="adm-btn-save"
                   value="<?=Loc::getMessage('MAIN_SAVE')?>"
                   title="<?=Loc::getMessage('MAIN_OPT_SAVE_TITLE')?>"
            />
            <input type="submit" name="default"
                   value="<?=Loc::getMessage('MAIN_RESTORE_DEFAULTS')?>"
                   title="<?=Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS')?>"
                   onclick="return confirm('<?=AddSlashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')"
            />
        </form>
        <?php
        $tabControl->end();
    }

    /**
     * Сохраняем данные формы.
     *
     * @return void
     */
    protected function save()
    {
        foreach ($this->tabs as $tab) {
            foreach ($tab['OPTIONS'] as $option) {
                if (!is_array($option)) {
                    continue;
                }
                if ($option['note']) {
                    continue;
                }

                $optionValue = $this->request->getPost($option[0]);
                $optionValue = is_array($optionValue) ? implode(',', $optionValue) : $optionValue;
                static::set($option[0], $optionValue);
            }
        }
    }

    /**
     * Инициализация настроек.
     *
     * @return void
     */
    public static function init()
    {
        foreach (static::getTabs() as $tab) {
            foreach ($tab['OPTIONS'] as $option) {
                if (!is_array($option)) {
                    continue;
                }
                if ($option['note']) {
                    continue;
                }

                static::set($option[0], $option[2]);
            }
        }
    }

    /**
     * Удаление настроек.
     *
     * @return void
     */
    public static function destroy()
    {
        try {
            Option::delete(static::MODULE_ID);
        } catch (Exception $exception) {
            CEventLog::Add([
                'SEVERITY' => 'ERROR',
                'AUDIT_TYPE_ID' => 'Options::destroy',
                'MODULE_ID' => static::MODULE_ID,
                'ITEM_ID' => '',
                'DESCRIPTION' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Установить настройку модуля.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public static function set(string $name, string $value)
    {
        try {
            Option::set(static::MODULE_ID, $name, $value);
        } catch (Exception $exception) {
            CEventLog::Add([
                'SEVERITY' => 'ERROR',
                'AUDIT_TYPE_ID' => 'Options::set',
                'MODULE_ID' => static::MODULE_ID,
                'ITEM_ID' => $name,
                'DESCRIPTION' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Получить настройку модуля.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public static function get(string $name, string $default = ''): string
    {
        try {
            return Option::get(static::MODULE_ID, $name, $default);
        } catch (Exception $exception) {
            CEventLog::Add([
                'SEVERITY' => 'ERROR',
                'AUDIT_TYPE_ID' => 'Options::get',
                'MODULE_ID' => static::MODULE_ID,
                'ITEM_ID' => $name,
                'DESCRIPTION' => $exception->getMessage(),
            ]);

            return $default;
        }
    }
}
