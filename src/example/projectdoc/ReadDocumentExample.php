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

namespace oglow\example\projectdoc;

use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

/**
 * FIXME:Remove.
 *
 * @SuppressWarnings(PHPMD)
 */
class ReadDocumentExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    public function readDocument(string $spaceKey, string $where): void
    {
        $response = $this->apiClient->pdtReadDocument(\oglow\tools\Yacorapi\Projectdoc\PDT_PROP_ALL_DEFAULT, $spaceKey, $where);

        if ($this->apiClient->checkDataPdtDocument($response)) {
            foreach ($response->getValue('document') as $document) {
                $this->logger->debug($document['key']);
                foreach ($document['property'] as $property) {
                    $this->apiClient->showResultsPdt($property, '');
                }
            }
        } else {
            var_dump($response);
        }
    }

    public function readDefaultProperties($pageId): void
    {
        $this->prepareFilesystem();
        $this->storeCsv(TARGET_DIR, TARGET_FILENAME, CSV_LINE_PDT_PROPERTY_HEADER . "\n");
        $curlSession = prepareCurl();

        foreach (PDT_PROP_ALL_DEFAULT as $property) {
            $searchUrl = preparePdtPropertyReadUrl($pageId, $property);
            echo $searchUrl;
            $response = execCurl($curlSession, $searchUrl);

            if (checkDataPdtProperty($response)) {
                storeCsv(TARGET_DIR, TARGET_FILENAME, prepareCsvLinePdtProperty($response, $property));
            } else {
                storeCsv(TARGET_DIR, TARGET_FILENAME, prepareCsvLinePdtProperty([], $property));
            }
        }
    }
}

/**
 * FIXME:Remove.
 *
 * @SuppressWarnings(PHPMD)
 */
function main(): void
{
    $pageId   = 532951146;
    $spaceKey = "NMAS";
    $where    = "Name=TOOL";

    $thisClazz = new ReadDocumentExample();
    $thisClazz->readDocument($spaceKey, $where);
    //    $thisClazz->readDefaultProperties($pageId);
}

main();
