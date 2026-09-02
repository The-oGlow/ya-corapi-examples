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

use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\Data\SpaceData;
use oglow\tools\Yacorapi\Data\SpaceTypeEnum;
use oglow\tools\Yacorapi\Response\ResponseSpaceDataDecorate;
use oglow\tools\Yacorapi\Store\FileAdapter;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class SearchSpacesExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');
        parent::__construct($outputFileName);

        $this->logger->debug('END');
    }

    public function spacesGlobal(): void
    {
        $this->logger->debug('START');

        /** @var ResponseSpaceDataDecorate $response */
        $response = $this->apiClient->listSpaces(SpaceTypeEnum::SPACE_TYPE_GLOBAL);

        $spaces = $response->getSpaces();
        $this->logger->info('Found global spaces', [count($spaces)]);

        $this->storeAsCsv($spaces);
        $this->prepareMySpaces($spaces);

        $this->logger->debug('END');
    }

    public function spacesPersonal(): void
    {
        $this->logger->debug('START');

        /** @var ResponseSpaceDataDecorate $response */
        $response = $this->apiClient->listSpaces(SpaceTypeEnum::SPACE_TYPE_PERSONAL);

        $spaces = $response->getSpaces();
        $this->logger->info('Found personal spaces', [count($spaces)]);

        $this->storeAsCsv($response->getSpaces());

        $this->logger->debug('END');
    }

    /**
     * @param array<mixed,mixed> $spaces
     */
    public function prepareMySpaces(array $spaces): void
    {
        $this->logger->debug('START');

        $fileContent = SpaceData::prepareMySpacesContent($spaces);
        $fileName    = SpaceData::prepareMySpacesFileName();

        $storeAdapter = new FileAdapter($fileName);
        $this->logger->info('Writing file', [$storeAdapter->getStoreItem()]);
        $storeAdapter->storeData($fileContent);

        $this->logger->debug('END');
    }
}

function main(): void
{
    $thisClazz = new SearchSpacesExample();

    // Search site spaces and write to file
    $thisClazz->spacesGlobal();

    // Search personal spaces
    $thisClazz->spacesPersonal();
}

main();
