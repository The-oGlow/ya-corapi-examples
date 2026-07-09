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

use oglow\example\ExampleErrorCodesEnum;
use oglow\example\Restapi\AbstractRestApiExample;
use ollily\Tools\Emergency;
use ollily\Tools\EnvironmentVariableTrait;

abstract class AbstractPoc extends AbstractRestApiExample
{
    use EnvironmentVariableTrait;

    protected const int EXPECTED_ARGS = 0;

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
