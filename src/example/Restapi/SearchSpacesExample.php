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

namespace oglow\example\Restapi;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

use oglow\tools\Yacorapi\Response\ResponseSpaceDataDecorate;
use oglow\tools\Yacorapi\RapiClient;
use oglow\tools\Yacorapi\Data\SpaceData;
use oglow\tools\Yacorapi\Data\RequestParameterData;

/**
 * FIXME:Remove.
 *
 * @SuppressWarnings(PHPMD)
 */
class SearchSpacesExample extends AbstractRestApiExample
{
    public function spacesGlobal(bool $asCsv = true): void
    {
        $this->logger->debug("START");

        /** @var ResponseSpaceDataDecorate */
        $response = $this->apiClient->listSpaces(RequestParameterData::SPACE_TYPE_GLOBAL);

        $this->storeAsCsv($response->getSpaces(), RequestParameterData::SPACE_TYPE_GLOBAL);
        $this->prepareMySpaces($response);

        $this->logger->debug("END");
    }

    public function spacesPersonal(bool $asCsv = true): void
    {
        $this->logger->debug("START");

        /** @var ResponseSpaceDataDecorate */
        $response = $this->apiClient->listSpaces(RequestParameterData::SPACE_TYPE_PERSONAL);

        $this->storeAsCsv($response->getSpaces(), RequestParameterData::SPACE_TYPE_PERSONAL);

        $this->logger->debug("END");
    }

    public function prepareMySpaces(ResponseSpaceDataDecorate $response): void
    {
        $this->logger->debug("START");

        $fileContent = SpaceData::prepareMySpacesContent($response->getSpaces());
        $fileName    = SpaceData::prepareMySpacesFileName();

        // FIXME: Die Angabe von Pfad und Dateiname ist unsauber
//        $targetFile = $this->storeAdapter->prepareTargetFileParam(
//            \oglow\tools\Yacorapi\TARGET_DIR,
//            $fileName
//        );
        $this->storeAdapter->storeData($fileContent);

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
