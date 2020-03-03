<?php
declare(strict_types=1);

namespace Simbirsoft\Queue\Contracts;

interface QueueableCommand extends Command
{
    /**
     * Получить ID в очереди
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Установить ID в очереди
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id);

    /**
     * Установить количество попыток
     *
     * @param int $attemptNumber
     * @return void
     */
    public function setAttemptNumber(int $attemptNumber);

    /**
     * Можно попытаться
     *
     * @return bool
     */
    public function canTry(): bool;

    /**
     * Количество попыток
     *
     * @return int
     */
    public function getTries(): int;

    /**
     * Время до первого запуска
     *
     * @return int
     */
    public function getSleep(): int;

    /**
     * Время между попытками
     *
     * @return int
     */
    public function getDelay(): int;
}
