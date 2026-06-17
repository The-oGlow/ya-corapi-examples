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

use oglow\tools\Yacorapi\Data\AddonMacroData;
use oglow\tools\Yacorapi\Response\ResponseAddonMacroDecorate;
use oglow\tools\Yacorapi\Data\SpaceData;
use oglow\tools\Yacorapi\Macro\SingleAddon;
use oglow\tools\Yacorapi\Statistic\AddonStatistic;
use oglow\tools\Yacorapi\Statistic\IStatistic;
use oglow\tools\Yacorapi\Statistic\MacroStatistic;
use oglow\tools\Yacorapi\Statistic\SpaceStatistic;
use oglow\tools\Yacorapi\Macro\BlockerAddon;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class CountMacrosExample extends AbstractRestApiExample
{
    public function countOneSpaceOneAddon(string $spaceKey, int $mode = SingleAddon::ADDON_SINGLE): void
    {
        $this->logger->debug("START", [$spaceKey]);

        /** @var ResponseAddonMacroDecorate */
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
    private function flattenData(IStatistic|array $anyData, int $mode): void
    {
        if (!is_array($anyData)) {
            $anyData = [$anyData];
        }

        /** @var SpaceStatistic $space */
        foreach ($anyData as $space) {
            $spaceKey      = $space->getStatisticName();
            $fileExtension = "$spaceKey-$mode";
            $this->storeAsCsv(null, $fileExtension, $space->header());
            foreach ($space->keys() as $addonName) {
                /** @var AddonStatistic */
                $addon = $space->getItem($addonName);
                foreach ($addon->keys() as $macroName) {
                    /** @var MacroStatistic */
                    $macro = $addon->getItem($macroName);

                    $csvLine = [$spaceKey, $addonName, $macroName, $macro->getItem($macroName)];
                    $this->logger->info("",[var_export($macro,true)]);
                    $this->logger->info("",[var_dump($csvLine)]);
                    $this->storeAsCsv($csvLine, $fileExtension);
                }
            }
        }
    }
}

function main(): void
{
    $thisClazz = new CountMacrosExample();
    $spaceData = new SpaceData();
    $spaceKeys = $spaceData->getDataByMode(SpaceData::SPACE_SIMPLE);

    $cntSpaces = count($spaceKeys);
    $cntIdx = 0;
    foreach ($spaceKeys as $spaceKey) {
        echo sprintf("\n\n%s/%s Count in space '%s'\n", ++$cntIdx, $cntSpaces, $spaceKey);
        $thisClazz->countOneSpaceOneAddon($spaceKey);
        die(1);
        echo sprintf("\n\n%s/%s Count blocker in space '%s'\n", $cntIdx, $cntSpaces, $spaceKey);
        $thisClazz->countOneSpaceOneAddon($spaceKey, BlockerAddon::ADDON_BLOCKER);
    }
}

main();
