<?php
declare(strict_types=1);

namespace Simbirsoft\Queue\Traits;

use Simbirsoft\Queue\Options;

trait Queueable
{
    /** @var int ID в очереди */
    protected $id = 0;
    /** @var int Количество попыток */
    protected $attemptNumber = 0;

    /**
     * Получить ID в очереди
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Установить ID в очереди
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * Установить количество попыток
     *
     * @param int $attemptNumber
     * @return void
     */
    public function setAttemptNumber(int $attemptNumber)
    {
        $this->attemptNumber = $attemptNumber;
    }

    /**
     * Можно попытаться
     *
     * @return bool
     */
    public function canTry(): bool
    {
        $tries = $this->getTries();
        return 0 === $tries || $this->attemptNumber < $tries;
    }

    /**
     * Количество попыток
     *
     * @return int
     */
    public function getTries(): int
    {
        return (int)Options::get('tries', '0');
    }

    /**
     * Время до первого запуска
     *
     * @return int
     */
    public function getSleep(): int
    {
        return (int)Options::get('sleep', '0');
    }

    /**
     * Время между попытками
     *
     * @return int
     */
    public function getDelay(): int
    {
        return (int)Options::get('delay', '300');
    }
}
