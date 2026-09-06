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
use oglow\example\AbstractExample;
use oglow\example\ExampleErrorCodesEnum;
use ollily\Tools\Emergency;
use ollily\Tools\EnvironmentVariableTrait;
use Psr\Log\LoggerInterface;

abstract class AbstractPoc extends AbstractExample
{
    use EnvironmentVariableTrait;

    protected const int EXPECTED_ARGS = 0;

    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    /**
     * @param mixed $args Start arguments
     */
    public function startDemo(...$args): void
    {
        $this->logger->info('Starting Demo');

        if (count($args) >= self::EXPECTED_ARGS) { // @phpstan-ignore greaterOrEqual.alwaysTrue
            $this->startProcess();
        } else {
            Emergency::breakSystem(ExampleErrorCodesEnum::ERR_ARGS_MISSING->intValue(), ExampleErrorCodesEnum::ERR_ARGS_MISSING->text());
        }

        $this->logger->info('Ending Demo');
    }

    /**
     * @param mixed $processData
     */
    abstract protected function startProcess(mixed $processData = null): void;
}
