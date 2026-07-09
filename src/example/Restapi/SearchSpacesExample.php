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

use oglow\tools\Yacorapi\Data\RequestParameterData;
use oglow\tools\Yacorapi\Data\SpaceData;
use oglow\tools\Yacorapi\Response\ResponseSpaceDataDecorate;
use oglow\tools\Yacorapi\Store\FileAdapter;

class SearchSpacesExample extends AbstractRestApiExample
{
    public function spacesGlobal(): void
    {
        $this->logger->debug("START");

        /** @var ResponseSpaceDataDecorate $response */
        $response = $this->apiClient->listSpaces(RequestParameterData::SPACE_TYPE_GLOBAL);

        $this->storeAsCsv($response->getSpaces(), RequestParameterData::SPACE_TYPE_GLOBAL);
        $this->prepareMySpaces($response);

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

    public function prepareMySpaces(ResponseSpaceDataDecorate $response): void
    {
        $this->logger->debug("START");

        $fileContent = SpaceData::prepareMySpacesContent($response->getSpaces());
        $fileName    = SpaceData::prepareMySpacesFileName();
        $storeAdapter = new FileAdapter($fileName);
        $storeAdapter->storeData($fileContent);

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
