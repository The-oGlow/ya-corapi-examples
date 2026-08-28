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

use oglow\tools\Yacorapi\IResponse;

use Psr\Log\LoggerInterface;
use Monolog\ConsoleLogger;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class SearchPageExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '') {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    public function readPageByPageId(int $pageId): void
    {
        $this->output->out("+++ searchByPageId()");
        $response = $this->apiClient->readPageByPageId($pageId);
        $this->outputData($response);
        $this->storeAsDump($response, ".txt");
    }

    public function readPagesWithFilter(string $filterTerm): void
    {
        $this->output->out("+++ searchByPageId()");
        $response = $this->apiClient->readPagesWithFilter($filterTerm);
        $this->outputDatas($response);
    }

    public function readPagesWithFilterAndSpace(string $filterTerm, string $spaceKey): void
    {
        $this->output->out("+++ searchTermAndSpaceByBrowse()");
        $response = $this->apiClient->readPagesWithFilter($filterTerm, $spaceKey);
        $this->outputDatas($response);
    }

    public function scanPagesWithFilter(string $filterTerm): void
    {
        $this->output->out("+++ scanPagesWithFilter()");
        $response = $this->apiClient->scanPagesWithFilter($filterTerm);
        $this->outputDatas($response);
    }

    public function scanPagesWithFilterAndSpace(string $filterTerm, string $spaceKey): void
    {
        $this->output->out("+++ scanPagesWithFilterAndSpace()");
        $response = $this->apiClient->scanPagesWithFilter($filterTerm, $spaceKey);
        $this->outputDatas($response);
    }

    public function searchPagesWithFilter(): void
    {
        global $spaceKey;
        global $searchTerm;

        $this->output->out("+++ searchPagesWithFilter()");
        $fallbackIdx = 0;
        do {
            $this->loopThruSearchResults($spaceKey, $searchTerm, $this->nextPos);
            $fallbackIdx++;
            if ($fallbackIdx >= 10) {
                $this->output->out("+++ searchPagesWithFilter() - fallback exit after 10 iterations +++");
                break;
            }
        } while ($this->totalSize > $this->currentPos);
    }

    private int $totalSize = 0;

    private int $currentPos = 0;

    private int $nextPos = 0;

    public function loopThruSearchResults(string $spaceKey, string $filterTerm, int $searchFromPos): void
    {
        $response = $this->apiClient->searchPagesWithFilter($filterTerm, $spaceKey, $searchFromPos);

        $idx = 0;
        /**
         * FIXME: IResponse liefert falschen Wert.
         *
         * @var IResponse|mixed[] $singleResult
         */
        foreach ($response->getResults() as $singleResult) {
            $this->outputData($singleResult, $idx++);
        }
        $this->resultPosUpdate(
            (int)$response->getValue(IResponse::KEY_START),
            (int)$response->getValue(IResponse::KEY_SIZE),
            (int)$response->getValue(IResponse::KEY_TOTAL_SIZE)
        );
    }

    public function resultPosUpdate(int $startNow, int $sizeNow, int $totalsNow): void
    {
        $this->logger->info(sprintf("CURRENT: currentPos: %s / nextPos: %s / totalSize: %s\n", $this->currentPos, $this->nextPos, $this->totalSize));
        $this->totalSize  = $totalsNow;
        $this->currentPos = $startNow;
        $this->nextPos    = $startNow + $sizeNow;
        $this->logger->info(sprintf("NEW    : currentPos: %s / nextPos: %s / totalSize: %s\n", $this->currentPos, $this->nextPos, $this->totalSize));
    }
}

function main(): void
{
    /** NMAS space */
    $spaceKey = 'NMAS';
    /** Page title */
    $searchTerm = 'title=REST-API%2001';
    /** 98-Playground on NMAS (TEST) */
    $pageId = 532951146;

    $thisClazz = new SearchPageExample();

    $thisClazz->readPageByPageId($pageId);

    $thisClazz->readPagesWithFilter($searchTerm);
    $thisClazz->readPagesWithFilterAndSpace($searchTerm, $spaceKey);

    $thisClazz->scanPagesWithFilter($searchTerm);
    $thisClazz->scanPagesWithFilterAndSpace($searchTerm, $spaceKey);

    $thisClazz->searchPagesWithFilter();
}

main();
