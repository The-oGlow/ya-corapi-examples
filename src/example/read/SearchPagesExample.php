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

class SearchPagesExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    private const int VAL_LOOP_MAX = 20;

    private int $totalSize = 0;

    private int $currentPos = 0;

    private int $nextPos = 0;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct($outputFileName);

        $this->logger->debug('END');
    }

    public function searchPagesWithFilter(string $filterTerm, string $spaceKey, int $searchLimit = IRapiClientBase::REQ_VAL_SEARCH_LIMIT_MIN): void
    {
        $this->output->out("\n+++ searchPagesWithFilter($filterTerm,$spaceKey,$searchLimit)");
        $this->initCounter();

        $fallbackIdx = 1;
        do {
            $this->loopSearchResults($spaceKey, $filterTerm, $this->nextPos, $searchLimit);
            if ($fallbackIdx >= self::VAL_LOOP_MAX) {
                $this->output->out('+++ searchPagesWithFilter() - fallback exit after ' . SearchPagesExample::VAL_LOOP_MAX . ' iterations +++');
                break;
            }
            $fallbackIdx++;
        } while ($this->totalSize > $this->currentPos);
    }

    public function loopSearchResults(string $spaceKey, string $filterTerm, int $searchFromPos, int $searchLimit): void
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
        $this->logger->info(sprintf("CURRENT: currentPos: %s / nextPos: %s / totalSize: %s", $this->currentPos, $this->nextPos, $this->totalSize));

        $this->totalSize  = $totalsNow;
        $this->currentPos = $startNow;
        $this->nextPos    = $startNow + $sizeNow;

        $this->logger->info(sprintf("NEW    : currentPos: %s / nextPos: %s / totalSize: %s\n", $this->currentPos, $this->nextPos, $this->totalSize));
    }

    protected function initCounter(): void
    {
        $this->totalSize = 0;
        $this->currentPos = 0;
        $this->nextPos = 0;
    }
}

function main(): void
{
    /** Space */
    $spaceKey = 'CMMN';

    /** Search/Filter Term*/
    $searchTerm = 'REST';

    $thisClazz = new SearchPagesExample();

    // Returns default entries per page
    $thisClazz->searchPagesWithFilter($searchTerm, $spaceKey);

    // Returns less results per page as the whole results
    $thisClazz->searchPagesWithFilter($searchTerm, $spaceKey, 10);

    // Returns more results per page as the whole results and allowed
    $thisClazz->searchPagesWithFilter($searchTerm, $spaceKey, 50);

    // Returns more results per page as the whole results and not allowed
    $thisClazz->searchPagesWithFilter($searchTerm, $spaceKey, 100000);
}

main();
