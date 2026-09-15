<?php

/*
 * Copyright 2026 GLO03.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace oglow\example\read;

use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\IResponse;
use oglow\tools\Yacorapi\Response\Response;
use oglow\tools\Yacorapi\Response\ResponseParameter as RP;
use oglow\tools\Yacorapi\Store\StoreParameter;
use Psr\Log\LoggerInterface;
use Monolog\ConsoleLogger;
use oglow\tools\Yacorapi\Client\IRapiClientBase;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

/**
 * Description of BulkDownloadPages
 *
 * @author ollily
 */
class BulkDownloadPages extends AbstractRestApiExample {

    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '') {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct($outputFileName);

        $this->logger->debug('END');
    }

    public function bulkDownload(string $spaceKey, string $searchTerm): void {


        $response = $this->prepareResults($spaceKey, $searchTerm);

        if ($response->checkStatus()) {
            if ($response->isResultsAvailable()) {
                $maxResults = $response->getResultsCount();
                $currIdx = 0;
                foreach ($response->getResults() as $currentResult) {
                    $this->logger->info('Result of All',[++$currIdx, $maxResults]);
                    if (array_key_exists('content', $currentResult)) {
                        $this->exportItem($currentResult['content'], $currIdx);
                    } else {
                        $this->exportItem($currentResult, $currIdx);
                    }
                }
            } else {
                $this->logger->info("Nothing found");
            }
        } else {
            $this->logger->warning("Response is invalud");
        }
    }

    public function prepareResults(string $spaceKey, string $searchTerm, int $startPos = IRapiClientBase::REQ_VAL_SEARCH_START): IResponse {
        return $this->apiClient->searchPagesWithFilter($searchTerm, $spaceKey, $startPos);
    }

    /**
     * 
     * @param mixed $currentResult
     * @param int $currIdx
     */
    public function exportItem(mixed $currentResult, int $currIdx): void {
        if (is_array($currentResult)) {
            $currentResult = new Response($currentResult);
        }

        if ($currentResult instanceof IResponse && $currentResult->checkStatus()) {
            $bodyResponse = $this->apiClient->readPageByPageId($currentResult->getItemId());
            if ($bodyResponse->checkStatus()) {
                $infoLine = sprintf(
                        "%03d-%s-%s-%s-%s",
                        $currIdx,
                        $bodyResponse->getItemId(),
                        $bodyResponse->getValue(RP::KEY_SPACE, ['key' => 'no key'])[RP::KEY_KEY],
                        $bodyResponse->getValue(RP::KEY_TITLE, 'no title'),
                        $bodyResponse->getValue(RP::KEY_TYPE, 'unknown')
                );
                $this->logger->info($infoLine,[strlen($bodyResponse->getBody())]);
                $fileSuffix = sprintf(
                        '%03d-%s-%s', 
                        $currIdx, 
                        $bodyResponse->getItemId(), 
                        str_replace(StoreParameter::C_ILLEGAL_FILE_CHARS,'_', substr($bodyResponse->getValue(RP::KEY_TITLE), 0, 50)));
                $fileExtension = 'xml';
                $this->storeAsDump($bodyResponse->getBody(), fileSuffix: $fileSuffix,fileExtension: $fileExtension);
            }
        } else {
            $this->logger->info("Nothing dumped");
        }
    }
}

function main():void {

    /** Space */
    $spaceKey = 'CMMN';

    /** Search/Filter Term */
    $searchTerm = 'REST';

    $thisClazz = new BulkDownloadPages();
    $thisClazz->bulkDownload($spaceKey, $searchTerm);
}

main();
