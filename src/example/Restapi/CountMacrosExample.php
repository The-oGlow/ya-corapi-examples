<?php

declare(strict_types=1);

/*
 * This file is part of ya-corapi-examles
 *
 * (c) 2024 Oliver Glowa, coding.glowa.com
 *
 * This source file is subject to the Apache-2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace oglowa\example\Restapi;

use oglowa\tools\Yacorapi\Impl\AddonMacroData;
use oglowa\tools\Yacorapi\Impl\ResponseAddonMacroDecorate;
use oglowa\tools\Yacorapi\Impl\SpaceData;
use oglowa\tools\Yacorapi\Statistic\AddonStatistic;
use oglowa\tools\Yacorapi\Statistic\IStatistic;
use oglowa\tools\Yacorapi\Statistic\MacroStatistic;
use oglowa\tools\Yacorapi\Statistic\SpaceStatistic;

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

class CountMacrosExample extends AbstractRestApiExample
{
    public function countOneSpaceOneAddon(string $spaceKey, int $mode = AddonMacroData::MACRO_SINGLE): void
    {
        $this->logger->debug("START", [$spaceKey]);

        /** @var ResponseAddonMacroDecorate $addonSet */
        $addonSet     = $this->apiClient->prepareAddonSet($mode);
        $outputMatrix = new SpaceStatistic($spaceKey);// [];
        $anyData      = $this->apiClient->countMacrosInSpace($spaceKey, $addonSet, $outputMatrix);

        $this->flattenData($anyData, $mode);

        $this->logger->debug("END", [$spaceKey]);
    }

    /**
     * @param IStatistic|IStatistic[] $anyData
     * @param int                     $mode
     */
    private function flattenData($anyData, int $mode): void
    {
        if (!is_array($anyData)) {
            $anyData = [$anyData];
        }

        /** @var SpaceStatistic $space */
        foreach ($anyData as $space) {
            $spaceKey      = $space->getSpaceKey();
            $fileExtension = "$spaceKey-$mode";
            $this->storeAsCsv(null, $fileExtension, $space->header());
            foreach ($space->getKeys() as $addonName) {
                /** @var AddonStatistic $addon */
                $addon = $space->getItem($addonName);
                foreach ($addon->getKeys() as $macroName) {
                    /** @var MacroStatistic $macro */
                    $macro = $addon->getItem($macroName);

                    $csvLine = [$spaceKey, $addonName, $macroName, $macro->getCount()];
                    $this->storeAsCsv($csvLine, $fileExtension);
                }
            }
        }
    }
}

function main(): void
{
    $thisClazz = new CountMacrosExample();
    $spaceData = SpaceData::getI();
    $spaceKeys = $spaceData->getDataByMode(SpaceData::SPACE_SIMPLE);

    foreach ($spaceKeys as $spaceKey) {
        $thisClazz->countOneSpaceOneAddon($spaceKey);
        $thisClazz->countOneSpaceOneAddon($spaceKey, AddonMacroData::MACRO_BLOCKER);
    }
}

main();
