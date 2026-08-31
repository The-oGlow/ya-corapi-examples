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
use oglow\tools\Yacorapi\Client\IRapiClientBase;
use oglow\tools\Yacorapi\IResponse;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class SearchPageExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    private int $totalSize = 0;

    private int $currentPos = 0;

    private int $nextPos = 0;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    public function readPageByPageId(int $pageId): void
    {
        $this->output->out("\n+++ readPageByPageId($pageId)");
        $response = $this->apiClient->readPageByPageId($pageId);
        $this->outputData($response);
        $this->storeAsDump($response, ".txt");
    }

    public function readPagesByTitle(string $pageTitle): void
    {
        $this->output->out("\n+++ readPagesByTitle($pageTitle)");
        $response = $this->apiClient->readPagesByTitle($pageTitle);
        $this->outputDatas($response);
    }

    public function readPagesByTitleAndSpace(string $pageTitle, string $spaceKey): void
    {
        $this->output->out("\n+++ readPagesByTitleAndSpace($pageTitle,$spaceKey)");
        $response = $this->apiClient->readPagesByTitle($pageTitle, $spaceKey);
        $this->outputDatas($response);
    }

    public function scanPages(): void
    {
        $this->output->out("\n+++ scanPages()");
        $response = $this->apiClient->scanPages();
        $this->outputDatas($response);
    }

    public function scanPagesWithSpace(string $spaceKey): void
    {
        $this->output->out("\n+++ scanPagesWithSpace($spaceKey)");
        $response = $this->apiClient->scanPages($spaceKey);
        $this->outputDatas($response);
    }

    public function searchPagesWithFilter(string $filterTerm, string $spaceKey, int $searchLimit = IRapiClientBase::REQ_SEARCH_LIMIT): void
    {
        $this->output->out("\n+++ searchPagesWithFilter($filterTerm,$spaceKey,$searchLimit)");
        $fallbackIdx = 0;
        do {
            $this->loopThruSearchResults($spaceKey, $filterTerm, $this->nextPos, $searchLimit);
            $fallbackIdx++;
            if ($fallbackIdx >= 10) {
                $this->output->out("+++ searchPagesWithFilter() - fallback exit after 10 iterations +++");
                break;
            }
        } while ($this->totalSize > $this->currentPos);
    }

    public function loopThruSearchResults(string $spaceKey, string $filterTerm, int $searchFromPos, int $searchLimit): void
    {
        $response = $this->apiClient->searchPagesWithFilter($filterTerm, $spaceKey, $searchFromPos, $searchLimit);

        $idx = 1;
        /**
         * FIXME: IResponse liefert falschen Wert.
         *
         * @var IResponse|mixed[] $singleResult
         */
        foreach ($response->getResults() as $singleResult) {
            if ($singleResult instanceof IResponse) {
                $this->outputData($singleResult->getValue(IResponse::KEY_CONTENT), $idx++);
            } else {
                $singleResult = $singleResult[IResponse::KEY_CONTENT];
                $this->outputData([$singleResult[IResponse::KEY_ID],
                    $singleResult[IResponse::KEY_SPACE][IResponse::KEY_KEY],$singleResult[IResponse::KEY_TITLE]], $idx++);
            }
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
    /** 98-Playground on CMMN (TEST) */
    $pageId = 631573347;

    // space
    $spaceKey = 'CMMN';

    /** Page title */
    $pageTitle = 'REST-External%20Documentation';

    /** Search/Filter Term*/
    $searchTerm = 'REST';

    $thisClazz = new SearchPageExample();

    $thisClazz->readPageByPageId($pageId);

    $thisClazz->readPagesByTitle($pageTitle);
    $thisClazz->readPagesByTitleAndSpace($pageTitle, $spaceKey);

    $thisClazz->scanPages();
    $thisClazz->scanPagesWithSpace($spaceKey);

    $thisClazz->searchPagesWithFilter($searchTerm, $spaceKey);
}

main();
