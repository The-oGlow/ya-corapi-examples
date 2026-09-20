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

use Ds\Vector;
use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\Client\IRapiClientBase;
use oglow\tools\Yacorapi\IResponse;
use oglow\tools\Yacorapi\Response\Response;
use oglow\tools\Yacorapi\Response\ResponseParameter as RP;
use oglow\tools\Yacorapi\Store\CsvFileAdapter;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

/**
 * Example how to create a list of pages for downloading.
 *
 * @author ollily
 */
class BulkCreateDownloadList extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct($outputFileName);

        $this->logger->debug('END');
    }

    public function bulkCreateDownloadList(string $spaceKey, string $searchTerm): string
    {
        $downloadList = '';
        $response = $this->findResults($spaceKey, $searchTerm);

        if ($response->checkStatus()) {
            if ($response->hasResults()) {
                $exportColumns = new Vector(RP::EXPORT_PAGE_LIGHT);
                $header = CsvFileAdapter::prepareExportLine($response, exportColumns: $exportColumns, header: true);
                $downloadList = $this->storeAsCsv(anyData: null, dataHeader: $header);

                $maxResults = $response->getResultsCount();
                $currIdx = 0;
                foreach ($response->getResults() as $result) {
                    $content = $result[RP::KEY_CONTENT];
                    $this->logger->info('Result', [++$currIdx, $maxResults, $content[RP::KEY_ID]]);
                    $line = CsvFileAdapter::prepareExportLine(new Response($content), exportColumns: $exportColumns);
                    $this->storeAsCsv(anyData: $line);
                }
            } else {
                $this->logger->info("Nothing found");
            }
        } else {
            $this->logger->warning("Response is invalud");
        }
        $this->logger->info("Written to", [$downloadList]);

        return $downloadList;
    }

    public function findResults(string $spaceKey, string $searchTerm, int $startPos = IRapiClientBase::REQ_VAL_SEARCH_START): IResponse
    {
        return $this->apiClient->searchPagesWithFilter($searchTerm, $spaceKey, $startPos);
    }
}

function main(): void
{
    /** Space */
    $spaceKey = 'CMMN';

    /** Search/Filter Term */
    $searchTerm = 'REST';

    $thisClazz = new BulkCreateDownloadList();
    $thisClazz->bulkCreateDownloadList($spaceKey, $searchTerm);
}

main();
