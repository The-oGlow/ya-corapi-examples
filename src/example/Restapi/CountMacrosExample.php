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

use oglowa\tools\Yacorapi\Data\SpaceData;
use oglowa\tools\Yacorapi\IResponse;
use oglowa\tools\Yacorapi\Macro\BlockerMacro;
use oglowa\tools\Yacorapi\Macro\SingleMacro;
use oglowa\tools\Yacorapi\Response\ResponseAddonMacroDecorate;
use oglowa\tools\Yacorapi\Statistic\AddonStatistic;
use oglowa\tools\Yacorapi\Statistic\IStatistic;
use oglowa\tools\Yacorapi\Statistic\MacroStatistic;
use oglowa\tools\Yacorapi\Statistic\SpaceStatistic;
use oglowa\tools\Yacorapi\Statistic\ValueStatistic;

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

class CountMacrosExample extends AbstractRestApiExample
{
    public function countOneSpaceOneAddon(string $spaceKey, int $mode = SingleMacro::MACRO_SINGLE): void
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
            $spaceKey      = $space->getStatisticName();
            $fileExtension = "$spaceKey-$mode";
            $this->storeAsCsv(null, $fileExtension, $space->header());
            foreach ($space->keys() as $addonName) {
                /** @var AddonStatistic $addon */
                $addon = $space->getItem($addonName);
                foreach ($addon->keys() as $macroName) {
                    /** @var MacroStatistic $macro */
                    $macro = $addon->getItem($macroName);
                    /** @var ValueStatistic $value */
                    $value   = $macro->getItem(IResponse::KEY_COUNT);
                    $csvLine = [$spaceKey, $addonName, $macroName, $value->getValue()];
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
    $spaceKeys = $spaceData->getDataByMode(SpaceData::SPACE_SINGLE);
    $mode      = BlockerMacro::MACRO_BLOCKER;

    foreach ($spaceKeys as $spaceKey) {
        $thisClazz->countOneSpaceOneAddon($spaceKey, $mode);
        sleep(2); // NOSONAR: php:S2964
    }
}

main();
