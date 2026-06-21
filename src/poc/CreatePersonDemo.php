<?php

declare(strict_types=1);

/*
 * This file is part of yacorapi-examles
 *
 * (c) 2024 Oliver Glowa, coding.glowa.com
 *
 * This source file is subject to the Apache-2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace oglow\poc;

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

use ollily\Tools\Batch\BatchTaskHelper;
use ollily\Tools\Batch\ITaskItem;

class CreatePersonDemo extends AbstractPoc
{
    #[\Override]
    protected function startProcess(ITaskItem $task): void
    {
        if (!$task->empty()) {
            $this->logger->notice('Creating person for', [$task->getData()[BatchTaskHelper::COL_INFO]]);
        }
    }
}

function main(): void
{
    $obj = new CreatePersonDemo();
    $obj->startDemo('person-crossfun.csv', 'csvperson');
}

main();
