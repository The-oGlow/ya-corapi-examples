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

use Monolog\ConsoleLogger;
use oglow\example\ExampleErrorCodesEnum;
use ollily\Tools\Batch\DataKeyEnum;
use ollily\Tools\Batch\ITaskItem;
use ollily\Tools\Emergency;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

class CreatePersonDemo extends AbstractPocProcessTaskItems
{
    public const string FILE_NAME = 'person-one.csv';

    public const string LIST_KEY = 'csvperson';

    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    #[\Override]
    protected function startProcess(mixed $processData = null): void
    {
        if ($processData instanceof ITaskItem) {
            /** @var ITaskItem $task */
            $task = $processData;
            if (!$task->empty()) {
                $title = $task->getDataValue(DataKeyEnum::NAME->value);
                $this->logger->notice('Creating person for', [$title]);
            }
        } else {
            Emergency::breakSystem(ExampleErrorCodesEnum::ERR_PROCESSDATA_WRONG->intValue(), ExampleErrorCodesEnum::ERR_PROCESSDATA_WRONG->text());
        }
    }
}

function main(): void
{
    $obj = new CreatePersonDemo();
    $obj->startDemo(CreatePersonDemo::FILE_NAME, CreatePersonDemo::LIST_KEY);
}

main();
