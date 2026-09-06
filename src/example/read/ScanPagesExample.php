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

namespace oglow\example\read;

use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class ScanPagesExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct($outputFileName);

        $this->logger->debug('END');
    }

    public function scanPages(): void
    {
        $this->output->out("\n+++ scanPages()");
        $response = $this->apiClient->scanPages();
        if ($response->checkStatus()) {
            $this->outputDatas($response);
        } else {
            $this->logger->error('Nothing found', [$response->getError()]);
        }
    }

    public function scanPagesWithSpace(string $spaceKey): void
    {
        $this->output->out("\n+++ scanPagesWithSpace($spaceKey)");
        $response = $this->apiClient->scanPages($spaceKey);
        if ($response->checkStatus()) {
            $this->outputDatas($response);
        } else {
            $this->logger->error('Nothing found', [$response->getError()]);
        }
    }
}

function main(): void
{
    /** Space */
    $spaceKey = 'CMMN';

    $thisClazz = new ScanPagesExample();

    // List any pages from the whole instance
    $thisClazz->scanPages();

    // List any pages from the given space only
    $thisClazz->scanPagesWithSpace($spaceKey);
}

main();
