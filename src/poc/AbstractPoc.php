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

use oglow\example\Restapi\AbstractRestApiExample;
use ollily\Tools\Batch\BatchTaskHelper;
use ollily\Tools\Batch\ITaskItem;
use ollily\Tools\Emergency;
use ollily\Tools\EnvironmentVariableTrait;

abstract class AbstractPoc extends AbstractRestApiExample
{
    use EnvironmentVariableTrait;

    public const int ERR_ARGS_MISSING = 254;

    public const string ERR_ARGS_MISSING_MSG = 'Not enough arguments given';

    private const int EXPECTED_ARGS = 2;

    public const string DEMO_PATH = '//input//oglow//poc//';

    /**
     * @param mixed $args Start arguments
     */
    public function startDemo(...$args): void
    {
        if (count($args) >= self::EXPECTED_ARGS) {
            $fileName = self::getProjectRoot() . self::DEMO_PATH . $args[0];
            $listName = $args[1];

            $this->logger->info('Starting Demo with', [$fileName, $listName]);

            $tasklist = BatchTaskHelper::readTaskList($fileName, $listName);

            // FIXME: remove when withHeader is working
            $idx = 0;
            while (($task = $tasklist->nextTask()) !== null) {
                if ($idx++ > 0) {
                    $this->startProcess($task);
                }
            }
        } else {
            Emergency::breakSystem(self::ERR_ARGS_MISSING, self::ERR_ARGS_MISSING_MSG);
        }
    }

    /**
     * @param ITaskItem $task
     */
    abstract protected function startProcess(ITaskItem $task): void;
}
