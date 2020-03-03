<?php
declare(strict_types=1);

use Bitrix\Main\Loader;
use Simbirsoft\Queue\Options;

Loader::includeModule('simbirsoft.queue');

new Options();
