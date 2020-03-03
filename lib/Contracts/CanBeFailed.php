<?php
declare(strict_types=1);

namespace Simbirsoft\Queue\Contracts;

use Exception;

interface CanBeFailed
{
    /**
     * @param Exception $exception
     * @return bool
     */
    public function failed(Exception $exception): bool;
}
