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

use Ds\Map;
use oglow\example\ExampleErrorCodesEnum;
use ollily\Tools\Batch\BatchTaskHelper;
use ollily\Tools\Batch\ItemConfig;
use ollily\Tools\Emergency;
use Psr\Log\LoggerInterface;
use Monolog\ConsoleLogger;

abstract class AbstractPocProcessTaskItems extends AbstractPoc
{
    public const string DEMO_PATH = '//input//oglow//poc//';

    protected const int EXPECTED_ARGS = 0;

    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '') {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    #[\Override]
    public function startDemo(...$args): void
    {
        $this->logger->info('Starting Demo');

        $itemConfig = new ItemConfig(new Map());

        if (count($args) >= self::EXPECTED_ARGS) { // @phpstan-ignore greaterOrEqual.alwaysTrue
            $fileName = self::getProjectRoot() . self::DEMO_PATH . $args[0];
            $listName = $args[1];

            $this->logger->info('Filename / ListKey', [$fileName, $listName]);

            $tasklist = BatchTaskHelper::readTaskList($fileName, $itemConfig, $listName, true);

            $this->logger->info('Found tasks', [$tasklist->count()]);

            while (($task = $tasklist->nextTask()) !== null) {
                $this->startProcess($task);
            }
        } else {
            Emergency::breakSystem(ExampleErrorCodesEnum::ERR_ARGS_MISSING->intValue(), ExampleErrorCodesEnum::ERR_ARGS_MISSING->text());
        }

        $this->logger->info('Ending Demo');
    }
}
