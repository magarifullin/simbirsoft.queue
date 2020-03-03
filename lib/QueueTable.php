<?php
declare(strict_types=1);

namespace Simbirsoft\Queue;

use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\Entity;

class QueueTable extends Entity\DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'simbirsoft_queue_agent';
    }

    /**
     * @return array
     *
     * @throws Main\SystemException
     */
    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', [
                'primary'      => true,
                'autocomplete' => true,
            ]),
            new Entity\TextField('COMMAND', [
                'required'   => true,
                'serialized' => true,
            ]),
            new Entity\DatetimeField('CREATED_AT', [
                'default_value' => new Type\DateTime(),
            ]),
            new Entity\DatetimeField('NEXT_ATTEMPT_AT', [
                'required' => true,
                'default_value' => new Type\DateTime(),
            ]),
            new Entity\IntegerField('ATTEMPT_COUNT', [
                'default_value' => 0,
            ]),
        ];
    }
}
