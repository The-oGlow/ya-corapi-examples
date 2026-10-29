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
use oglow\tools\Addon\Projectdoc\Traits\ProjectdocTrait;
use oglow\tools\Yacorapi\Response\Response;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

/**
 * FIXME:Remove.
 *
 * @SuppressWarnings(PHPMD)
 */
class ReadDocumentExample extends AbstractRestApiExample
{
    use ProjectdocTrait;

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
        $response = $this->pdtReadDocument(\oglow\tools\Yacorapi\Projectdoc\PDT_PROP_ALL_DEFAULT, $spaceKey, $where);

        if ($this->checkDataPdtDocument($response)) {
            foreach ($response->getValue('document') as $document) {
                $this->logger->debug($document['key']);
                foreach ($document['property'] as $property) {
                    $this->showResultsPdt($property, '');
                }
            }
        } else {
            $this->logger->debug('', [$response]);
        }
    }

    public function readDefaultProperties(int $pageId): void
    {
        //        $this->prepareFilesystem();
        $this->storeAsCsv(TARGET_DIR, TARGET_FILENAME, CSV_LINE_PDT_PROPERTY_HEADER . "\n");
        //        $curlSession = $this->apiClient->prepareCurl();

        foreach (PDT_PROP_ALL_DEFAULT as $property) {
            $searchUrl = $this->preparePdtPropertyReadUrl($pageId, $property);
            echo $searchUrl;
            //            $response = $this->execCurl($curlSession, $searchUrl);
            $response = new Response();
            if ($this->checkDataPdtProperty($response)) {
                $this->storeAsCsv(TARGET_DIR, TARGET_FILENAME, $this->prepareCsvLinePdtProperty($response, $property));
            } else {
                $this->storeAsCsv(TARGET_DIR, TARGET_FILENAME, $this->prepareCsvLinePdtProperty($response, $property));
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
