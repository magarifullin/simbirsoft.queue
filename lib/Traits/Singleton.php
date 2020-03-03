<?php
declare(strict_types=1);

namespace Simbirsoft\Queue\Traits;

use RuntimeException;

trait Singleton
{
    /**
     * @return self
     */
    public static function getInstance(): self
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }
        return $instance;
    }

    final private function __construct()
    {
        $this->_instance();
    }

    /**
     * Конструктор для наследников
     *
     * @return void
     */
    protected function _instance()
    {

    }

    final private function __clone()
    {

    }

    /**
     * @throws RuntimeException
     */
    final public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize a singleton.');
    }
}
