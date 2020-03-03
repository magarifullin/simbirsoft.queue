<?php
declare(strict_types=1);

namespace Simbirsoft\Queue;

use Exception;
use Bitrix\Main;
use Bitrix\Main\DB;
use Bitrix\Main\Type;
use Simbirsoft\Queue\Traits\Singleton;
use Simbirsoft\Queue\Contracts\CanBeFailed;
use Simbirsoft\Queue\Contracts\QueueableCommand;

#todo: Необходимо разбить на несколько классов
class Queue
{
    use Singleton;

    /**
     * @return QueueableCommand[]
     */
    public function getCommands(): array
    {
        try {
            $commands = [];
            $dbQueue = QueueTable::getList([
                'order'  => ['CREATED_AT' => 'asc'],
                'select' => ['ID', 'COMMAND', 'ATTEMPT_COUNT'],
                'filter' => [
                    '<=NEXT_ATTEMPT_AT' => new Type\DateTime(),
                ],
            ]);
            while ($queueRow = $dbQueue->fetch()) {
                /** @var QueueableCommand $command */
                $command = $queueRow['COMMAND'][0];
                $command->setId((int)$queueRow['ID']);
                $command->setAttemptNumber((int)$queueRow['ATTEMPT_COUNT']);
                $commands[] = $command;
            }
            return $commands;
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * @param QueueableCommand $command
     *
     * @throws Exception
     */
    public function add(QueueableCommand $command)
    {
        QueueTable::add([
            'COMMAND'         => [$command],
            'NEXT_ATTEMPT_AT' => (new Type\DateTime())->add($command->getSleep() .' seconds'),
        ]);
    }

    /**
     * @param QueueableCommand $command
     *
     * @throws Main\ArgumentException
     * @throws Main\ObjectException
     * @throws Exception
     */
    public function update(QueueableCommand $command)
    {
        QueueTable::update($command->getId(), [
            'ATTEMPT_COUNT'   => new DB\SqlExpression('?# + ?i', 'ATTEMPT_COUNT', 1),
            'NEXT_ATTEMPT_AT' => (new Type\DateTime())->add($command->getDelay() .' seconds'),
        ]);
    }

    /**
     * @param QueueableCommand $command
     *
     * @throws Exception
     */
    public function complete(QueueableCommand $command)
    {
        QueueTable::delete($command->getId());
    }

    /**
     * @return void
     *
     * @throws Main\ArgumentException
     * @throws Main\ObjectException
     * @throws Exception
     */
    public function work()
    {
        $commands = $this->getCommands();
        foreach ($commands as $command) {
            try {
                $command->execute();
            } catch (Exception $exception) {
                $continue = true;
                if ($command instanceof CanBeFailed) {
                    $continue = $command->failed($exception);
                }

                if ($continue && $command->canTry()) {
                    $this->update($command);
                    continue;
                }
            }

            $this->complete($command);
        }
    }

    /**
     * Проверяем, можно ли кинуть в очередь
     *
     * @param $command
     * @return bool
     */
    public static function shouldBeQueued($command): bool
    {
        return $command instanceof QueueableCommand;
        // return is_subclass_of($command, ShouldQueue::class);
    }
}
