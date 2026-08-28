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

namespace oglowa\example\projectdoc;

use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\ConstData;
use oglow\tools\Yacorapi\IResponse;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class FixEmptyPageExample extends AbstractRestApiExample
{
    public const int BODYSIZE_MIN = 10;

    private ConstData $constData;

    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    public function scanPagesInSpace(string $spaceKey): void
    {
        $this->constData = new ConstData(get_class($this));

        $start      = ConstData::PAGE_START;
        $pageLimit  = ConstData::PAGE_LIMIT;
        $filterTerm = 'type:page AND -macroName:projectdoc-properties-marker';

        $idxLoop = 0;
        $bLoop   = true;

        /** @psalm-suppress RedundantCondition */
        while ($bLoop) {
            $response = $this->apiClient->searchPagesWithFilter($filterTerm, $spaceKey, $start, $pageLimit);
            if ($response->isResultsAvailable()) {
                $results = $response->getResults();
                if ($results->hasKey(IResponse::KEY_CONTENT)) {
                    $results = $results->get(IResponse::KEY_CONTENT);
                }
                foreach ($results as $resultValue) {
                    $bodySize = strlen($resultValue[IResponse::KEY_BODY][IResponse::KEY_STORAGE][IResponse::KEY_VALUE]);
                    if ($bodySize <= self::BODYSIZE_MIN) {
                        $line = [
                            $idxLoop,
                            $resultValue[IResponse::KEY_ID],
                            $resultValue[IResponse::KEY_TYPE],
                            $resultValue[IResponse::KEY_TITLE],
                            $bodySize,
                            $this->constData->c(ConstData::KEY_WEB_SHOW_PAGEID) . $resultValue['id'],
                        ];
                        $this->logger->debug("$idxLoop.", [$line]);
                        $this->storeAsCsv($line);
                        $idxLoop++;
                    }
                }
            } else {
                $this->logger->notice("No results found.");
                $bLoop = false;
                break;
            }
            if ($idxLoop > ConstData::PAGE_MAX_RESULTS) {
                $this->logger->notice("After at least results, I stop.", [ConstData::PAGE_MAX_RESULTS]);
                $bLoop = false;
                break;
            }
            $start += $pageLimit;
        }
    }
}

function main(): void
{
    $spaceKey  = 'NMVSSUP';
    $thisClazz = new FixEmptyPageExample();
    $thisClazz->scanPagesInSpace($spaceKey);
}

main();
