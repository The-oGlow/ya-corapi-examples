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

class ReadPageByExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct($outputFileName);

        $this->logger->debug('END');
    }

    public function readPageByPageId(int $pageId): void
    {
        $this->output->out("\n+++ readPageByPageId($pageId)");

        $response = $this->apiClient->readPageByPageId($pageId);
        if ($response->checkStatus()) {
            $this->outputData($response);
            $this->storeAsDump($response);
        } else {
            $this->logger->error('Nothing found', [$response->getError()]);
        }
    }

    public function readPagesByTitle(string $pageTitle): void
    {
        $this->output->out("\n+++ readPagesByTitle($pageTitle)");

        $response = $this->apiClient->readPagesByTitle($pageTitle);
        if ($response->checkStatus()) {
            $this->outputDatas($response);
        } else {
            $this->logger->error('Nothing found', [$response->getError()]);
        }
    }

    public function readPagesByTitleAndSpace(string $pageTitle, string $spaceKey): void
    {
        $this->output->out("\n+++ readPagesByTitleAndSpace($pageTitle,$spaceKey)");

        $response = $this->apiClient->readPagesByTitle($pageTitle, $spaceKey);
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

    /** Page title exists only once on the whole instance*/
    $pageTitleUnique = 'REST-External Documentation';

    /** Page title exists multiple times on the whole instance */
    $pageTitleMultiple = '98-Playground';

    /** PageId of {@link $pageTitleUnique} */
    $pageIdUnique = 178764044;

    $thisClazz = new ReadPageByExample();

    // returns the page in detail and dumps to file
    $thisClazz->readPageByPageId($pageIdUnique);

    // returns one page result
    $thisClazz->readPagesByTitle($pageTitleUnique);

    // returns one page result
    $thisClazz->readPagesByTitleAndSpace($pageTitleUnique, $spaceKey);

    // returns multiple page result
    $thisClazz->readPagesByTitle($pageTitleMultiple);

    // returns one page result
    $thisClazz->readPagesByTitleAndSpace($pageTitleMultiple, $spaceKey);
}

main();
