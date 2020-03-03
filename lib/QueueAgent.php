<?php
declare(strict_types=1);

namespace Simbirsoft\Queue;

use Throwable;
use CEventLog;

class QueueAgent
{
    /**
     * @return string
     */
    public static function handle(): string
    {
        try {
            Queue::getInstance()->work();
        } catch (Throwable $exception) {
            CEventLog::Add([
                'SEVERITY' => 'ERROR',
                'AUDIT_TYPE_ID' => self::class .'::handle',
                'MODULE_ID' => Options::MODULE_ID,
                'ITEM_ID' => 'Queue::work',
                'DESCRIPTION' => $exception->getMessage(),
            ]);
        }

        return static::getAgent();
    }

    /**
     * @return string
     */
    public static function getAgent(): string
    {
        return __CLASS__ .'::handle();';
    }
}
