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

namespace oglow\example\Restapi;

use Monolog\ConsoleLogger;
use oglow\tools\Yacorapi\Client\RapiClient;
use oglow\tools\Yacorapi\IRapiClient;
use Psr\Log\LoggerInterface;
use oglow\example\AbstractExample;

abstract class AbstractRestApiExample extends AbstractExample
{

    protected IRapiClient $apiClient;

    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);
        
        $this->apiClient      = RapiClient::newClient(level: self::LEVEL_DEFAULT);

        $this->logger->debug("END");
    }

}
