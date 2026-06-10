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

use oglow\tools\Yacorapi\Impl\SpaceData;
use oglow\tools\Yacorapi\RapiClient;
use oglow\tools\Yacorapi\Statistic\IStatistic;
use oglow\tools\Yacorapi\Statistic\SpaceStatistic;

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

class CountPagesExample extends AbstractRestApiExample
{
    /** @var bool */
    private bool $headerWritten = false;

    public function countOneSpaceVolume(string $spaceKey, bool $singleFile = false): void
    {
        $this->logger->debug("START", [$spaceKey]);
        $this->loopPageTypes($spaceKey, $singleFile);
        $this->logger->debug("END", [$spaceKey]);
    }

    private function loopPageTypes(string $spaceKey, bool $singleFile): void
    {
        $this->logger->debug("START", [$spaceKey]);

        $fileNameSuffix = $singleFile ? '' : $spaceKey;
        $spaceStatistic = new SpaceStatistic($spaceKey);

        foreach (RapiClient::ITEM_TYPES as $pageType) {
            $countPages = $this->countPagesInSpace($spaceKey, $pageType);
            $spaceStatistic->addItem($pageType, $countPages);
        }

        if (($singleFile && !$this->headerWritten) || !$singleFile) {
            $this->storeAsCsv(null, $fileNameSuffix, $spaceStatistic->flattenHeader());
            $this->headerWritten = true;
        }
        foreach ($spaceStatistic->getKeys() as $itemName) {
            /** @var IStatistic $value */
            $value = $spaceStatistic->getItem($itemName);
            $entry = [$spaceKey, $itemName, $value->flatten(false)];
            $this->storeAsCsv($entry, $fileNameSuffix);
        }

        $this->logger->debug("END", [$spaceKey]);
    }

    private function countPagesInSpace(string $spaceKey, string $pageType): IStatistic
    {
        return $this->apiClient->countItemsinSpace($spaceKey, $pageType);
    }
}

function main(): void
{
    $thisClazz = new CountPagesExample();

    $spaceData = SpaceData::getI();
    $spaceKeys = $spaceData->getDataByMode(SpaceData::SPACE_SIMPLE);
    $singleFile = false;

    foreach ($spaceKeys as $spaceKey) {
        $thisClazz->countOneSpaceVolume($spaceKey, $singleFile);
    }
}

main();
