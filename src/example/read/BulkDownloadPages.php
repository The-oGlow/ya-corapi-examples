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

use ArrayAccess;
use Ds\Vector;
use Monolog\ConsoleLogger;
use oglow\tools\Yacorapi\IResponse;
use oglow\tools\Yacorapi\Response\Response;
use oglow\tools\Yacorapi\Response\ResponseParameter as RP;
use oglow\tools\Yacorapi\Store\CsvFileAdapter;
use oglow\tools\Yacorapi\Store\StoreParameter;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

/**
 * Description of BulkDownloadPages.
 *
 * @author ollily
 */
class BulkDownloadPages extends BulkCreateDownloadList
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct($outputFileName);

        $this->logger->debug('END');
    }

    public function bulkDownloadPages(string $fileName): void
    {
        $results = CsvFileAdapter::readData($fileName, true);

        if ($results->count() > 0) {
            $maxResults = $results->count();
            $currIdx = 0;
            foreach ($results as $currentResult) {
                $this->logger->info('Processing row', [++$currIdx, $maxResults]);
                if (array_key_exists(RP::KEY_CONTENT, $currentResult)) {
                    $this->exportItem($currentResult[RP::KEY_CONTENT], $currIdx);
                } else {
                    $this->exportItem($currentResult, $currIdx);
                }
            }
        } else {
            $this->logger->info("Nothing found");
        }
    }

    /**
     * @param ArrayAccess|array<mixed> $currentResult
     * @param int   $currIdx
     */
    public function exportItem(ArrayAccess|array $currentResult, int $currIdx): void
    {

        if (count($currentResult) > 0) {
            $bodyResponse = $this->apiClient->readPageByPageId($currentResult[RP::KEY_ID]);

            if ($bodyResponse->checkStatus()) {

                $infoLine = sprintf(
                    "%03d-%s-%s-%s-%s, Body size: %d",
                    $currIdx,
                    $bodyResponse->getItemId(),
                    $bodyResponse->getValue(RP::KEY_SPACE, ['key' => 'no key'])[RP::KEY_KEY],
                    $bodyResponse->getValue(RP::KEY_TITLE, 'no title'),
                    $bodyResponse->getValue(RP::KEY_TYPE, 'unknown'),
                    strlen($bodyResponse->getBody())
                );
                $this->logger->debug($infoLine);
                $exportColumns = new Vector(RP::EXPORT_PAGE_FULL);
                $fileSuffix = sprintf(
                    '%03d-%s-%s',
                    $currIdx,
                    $bodyResponse->getItemId(),
                    str_replace(StoreParameter::C_ILLEGAL_FILE_CHARS, '_', substr($bodyResponse->getValue(RP::KEY_TITLE), 0, 50))
                );
                $header = CsvFileAdapter::prepareExportLine($bodyResponse, exportColumns: $exportColumns, header:true);
                $line = CsvFileAdapter::prepareExportLine($bodyResponse, exportColumns: $exportColumns);
                $this->storeAsCsv(anyData: $line, dataHeader: $header, fileSuffix: $fileSuffix);
            } else {
                $this->logger->warning("Response is invalid", [$bodyResponse->getError()]);
            }
        } else {
            $this->logger->info("Nothing dumped");
        }
    }
}

function main2(): void
{
    /** Space */
    $spaceKey = 'CMMN';

    /** Search/Filter Term */
    $searchTerm = 'REST';

    $listFileName = 'download';

    $thisClazz = new BulkDownloadPages($listFileName);

    $fileName = $thisClazz->bulkCreateDownloadList($spaceKey, $searchTerm);
    $thisClazz->bulkDownloadPages($fileName);
}

main2();
