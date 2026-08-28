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

namespace oglow\example;

use ollily\Tools\Arrays\IDoubleBackedEnum;

enum ExampleErrorCodesEnum: string implements IDoubleBackedEnum
{
    case ERR_ARGS_MISSING = '254';
    case ERR_PROCESSDATA_WRONG = '253';
    case ERR_MAX_ITERATION = '252';

    #[\Override]
    public function intValue(): int
    {
        return (int) $this->value;
    }

    #[\Override]
    public function text(): string
    {
        return match ($this) {
            self::ERR_ARGS_MISSING => 'Not enough arguments given',
            self::ERR_PROCESSDATA_WRONG => 'Data for processing is invalid',
            self::ERR_MAX_ITERATION => 'Maximum of iteration reached'
        };
    }

    #[\Override]
    public function objectValue(): mixed
    {
        return null;
    }
}
