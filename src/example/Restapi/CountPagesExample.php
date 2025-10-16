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

use oglowa\tools\Yacorapi\Data\RequestParameterData;
use oglowa\tools\Yacorapi\Data\SpaceData;
use oglowa\tools\Yacorapi\Statistic\IStatistic;
use oglowa\tools\Yacorapi\Statistic\SpaceStatistic;

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

class CountPagesExample extends AbstractRestApiExample
{
    /** @var bool */
    private $headerWritten = false;

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

        foreach (RequestParameterData::ITEM_TYPES as $pageType) {
            $countPages = $this->countPagesInSpace($spaceKey, $pageType);
            $spaceStatistic->addItem($pageType, $countPages);
        }

        if (($singleFile && !$this->headerWritten) || !$singleFile) {
            $this->storeAsCsv(null, $fileNameSuffix, $spaceStatistic->header());
            $this->headerWritten = true;
        }
        foreach ($spaceStatistic->keys() as $itemName) {
            $value = $spaceStatistic->getItem($itemName);
            $entry = [$spaceKey, $itemName, is_null($value) ? '' : $value->flatten(false)];
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

    $spaceData = new SpaceData();
    $spaceKeys = $spaceData->getDataByMode(SpaceData::SPACE_SINGLE);
    $singleFile = false
    ;

    foreach ($spaceKeys as $spaceKey) {
        $thisClazz->countOneSpaceVolume($spaceKey, $singleFile);
        sleep(2); // NOSONAR: php:S2964
    }
}

main();
