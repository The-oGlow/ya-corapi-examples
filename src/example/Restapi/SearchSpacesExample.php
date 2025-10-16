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

namespace oglowa\example\Restapi;

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

use oglowa\tools\Yacorapi\ConstData;
use oglowa\tools\Yacorapi\Data\RequestParameterData;
use oglowa\tools\Yacorapi\Data\SpaceData;
use oglowa\tools\Yacorapi\Response\ResponseSpaceDataDecorate;

class SearchSpacesExample extends AbstractRestApiExample
{
    public function spacesGlobal(): void
    {
        $this->logger->debug("START");

        /** @var ResponseSpaceDataDecorate $response */
        $response = $this->apiClient->listSpaces(RequestParameterData::SPACE_TYPE_GLOBAL);

        $this->storeAsCsv($response->getSpaces(), RequestParameterData::SPACE_TYPE_GLOBAL);
        $this->prepareMySpaces($response->getSpaces());

        $this->logger->debug("END");
    }

    public function spacesPersonal(): void
    {
        $this->logger->debug("START");

        /** @var ResponseSpaceDataDecorate $response */
        $response = $this->apiClient->listSpaces(RequestParameterData::SPACE_TYPE_PERSONAL);

        $this->storeAsCsv($response->getSpaces(), RequestParameterData::SPACE_TYPE_PERSONAL);

        $this->logger->debug("END");
    }

    /**
     * @param string[] $spaces
     */
    public function prepareMySpaces(array $spaces): void
    {
        $this->logger->debug("START");

        $fileContent = SpaceData::prepareMySpacesContent($spaces);
        $fileName    = SpaceData::prepareMySpacesFileName();

        // REFACTOR: Die Angabe von Pfad und Dateiname ist unsauber
        $targetFile = $this->storeAdapter->prepareTargetFileParam(
            $this->constData->c(ConstData::C_TARGET_DIR),
            $fileName
        );
        $this->storeAdapter->storeData($targetFile, $fileContent);

        $this->logger->debug("END");
    }
}

function main(): void
{
    $thisClazz = new SearchSpacesExample();

    $thisClazz->spacesGlobal();
    $thisClazz->spacesPersonal();
}

main();
