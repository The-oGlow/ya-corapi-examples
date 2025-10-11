<?php

declare(strict_types=1);

/*
 * This file is part of ya-corapi-examples
 *
 * (c) 2025 Oliver Glowa, coding.glowa.com
 *
 * This source file is subject to the Apache-2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace oglowa\example\Restapi\Projectdoc;

use oglowa\example\Restapi\AbstractRestApiExample;
use oglowa\tools\Yacorapi\Projectdoc\TraitProjectdoc;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class ReadDocumentExample extends AbstractRestApiExample
{
    use TraitProjectdoc;

    public function readDocument(string $spaceKey, string $where): void
    {
        $response = $this->apiClient->pdtReadDocument(\oglowa\tools\Yacorapi\Projectdoc\PDT_PROP_ALL_DEFAULT, $spaceKey, $where);

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
        $this->storeAsCsv(null, null, \oglowa\tools\Yacorapi\Projectdoc\CSV_LINE_PDT_PROPERTY_HEADER);

        foreach (\oglowa\tools\Yacorapi\Projectdoc\PDT_PROP_ALL_DEFAULT as $property) {
            $response = $this->apiClient->pdtReadProperty($pageId, $property);

            if ($this->checkDataPdtProperty($response)) {
                $this->storeAsCsv($this->prepareCsvLinePdtProperty($response, $property));
            } else {
                $this->storeAsCsv($this->prepareCsvLinePdtProperty(null, $property));
            }
        }
    }
}

function main(): void
{
    $pageId   = 532951146;
    $spaceKey = "NMAS";
    $where    = "Name=TOOL";

    $thisClazz = new ReadDocumentExample();
    $thisClazz->readDocument($spaceKey, $where);
    $thisClazz->readDefaultProperties($pageId);
}

main();
