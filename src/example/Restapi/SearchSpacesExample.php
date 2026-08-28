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

use oglow\tools\Yacorapi\Data\RequestParameterData;
use oglow\tools\Yacorapi\Data\SpaceData;
use oglow\tools\Yacorapi\Response\ResponseSpaceDataDecorate;
use oglow\tools\Yacorapi\Store\FileAdapter;
use Psr\Log\LoggerInterface;
use Monolog\ConsoleLogger;
use oglow\tools\Yacorapi\Data\SpaceTypeEnum;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class SearchSpacesExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '') {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    public function spacesGlobal(): void
    {
        $this->logger->debug("START");

        /** @var ResponseSpaceDataDecorate $response */
        $response = $this->apiClient->listSpaces(SpaceTypeEnum::SPACE_TYPE_GLOBAL);

        $this->storeAsCsv($response->getSpaces());
        $this->prepareMySpaces($response);

        $this->logger->debug("END");
    }

    public function spacesPersonal(): void
    {
        $this->logger->debug("START");

        /** @var ResponseSpaceDataDecorate $response */
        $response = $this->apiClient->listSpaces(SpaceTypeEnum::SPACE_TYPE_PERSONAL);

        $this->storeAsCsv($response->getSpaces());

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
