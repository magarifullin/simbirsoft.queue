<?php
declare(strict_types=1);

namespace Simbirsoft\Queue\Contracts;

interface Command
{
    /**
     * @return void
     */
    public function execute();
}
