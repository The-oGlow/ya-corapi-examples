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

use ollily\Tools\Batch\BatchTaskHelper;
use ollily\Tools\Batch\ITaskItem;
use ollily\Tools\Batch\TaskList;
use ollily\Tools\Emergency;
use ollily\Tools\Batch\ItemConfig;
use Ds\Map;

abstract class AbstractPocProcessTaskItems extends AbstractPoc
{
    public const string DEMO_PATH = '//input//oglow//poc//';

    protected const int EXPECTED_ARGS = 0;

    #[\Override]
    public function startDemo(...$args): void
    {
        $this->logger->info('Starting Demo');

        $itemConfig = new ItemConfig(new Map());
        
        if (count($args) >= self::EXPECTED_ARGS) {
            $fileName = self::getProjectRoot() . self::DEMO_PATH . $args[0];
            $listName = $args[1];

            $this->logger->info('Filename / ListKey', [$fileName, $listName]);

            /** @var TaskList */
            $tasklist = BatchTaskHelper::readTaskList($fileName, $itemConfig, $listName, true);

            $this->logger->info('Found tasks', [$tasklist->count()]);

            /** @var ITaskItem $task */
            while (($task = $tasklist->nextTask()) !== null) {
                $this->startProcess($task);
            }
        } else {
            Emergency::breakSystem(ExampleErrorCodesEnum::ERR_ARGS_MISSING->value, ExampleErrorCodesEnum::ERR_ARGS_MISSING->text());
        }

        $this->logger->info('Ending Demo');
    }
}
