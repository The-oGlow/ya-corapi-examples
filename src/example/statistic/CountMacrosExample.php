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

namespace oglow\example\statistic;

use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\Macro\AddonTypeEnum;
use oglow\tools\Yacorapi\Space\SpaceData;
use oglow\tools\Yacorapi\Space\SpaceTypeEnum;
use oglow\tools\Yacorapi\Statistic\IStatistic;
use oglow\tools\Yacorapi\Statistic\StatisticStatistic;
use oglow\tools\Yacorapi\Statistic\StatisticTypeEnum;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class CountMacrosExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    public function countMacros(SpaceTypeEnum $spaceMode, AddonTypeEnum $addonMode): void
    {
        $spaceData = new SpaceData();
        $spaceKeys = $spaceData->getDataByMode($spaceMode->value);

        $cntSpaces = count($spaceKeys);
        $cntIdx = 0;
        foreach ($spaceKeys as $spaceKey) {
            ++$cntIdx;
            echo sprintf("\n\n%s/%s Count all in space '%s'\n", $cntIdx, $cntSpaces, $spaceKey);
            $this->countMacrosInSpace($spaceKey, $addonMode);
        }
    }

    public function countMacrosInSpace(string $spaceKey, AddonTypeEnum $addonMode = AddonTypeEnum::ADDON_SINGLE): void
    {
        $this->logger->debug("START", [$spaceKey]);

        $addonSet = $this->apiClient->prepareAddonSet($addonMode);
        $outputMatrix = new StatisticStatistic($spaceKey, StatisticTypeEnum::SPACE);
        $anyData = $this->apiClient->countMacrosInSpace($spaceKey, $addonSet, $outputMatrix);

        $this->writeFile($anyData, $addonMode);

        $this->logger->debug("END", [$spaceKey]);
    }

    /**
     * @param array<mixed,IStatistic>|IStatistic $anyData
     * @param AddonTypeEnum                      $mode
     */
    private function writeFile(IStatistic|array $anyData, AddonTypeEnum $mode): void
    {
        $this->logger->debug("START");

        if (!is_array($anyData)) {
            $anyData = [$anyData];
        }

        foreach ($anyData as $space) {
            $spaceKey = $space->getStatisticName();
            $fileExtension = "$spaceKey-" . $mode->value;
            $this->logger->notice("Write Data for space to file with extension", [$spaceKey, $fileExtension]);

            $this->storeAsCsv(null, $fileExtension, $space->flattenHeader());

            foreach ($space->keys() as $addonName) {
                /** @var IStatistic $addon */
                $addon = $space->getItem($addonName);
                foreach ($addon->keys() as $macroName) {
                    /** @var IStatistic $macro */
                    $macro = $addon->getItem($macroName);
                    // FIXME: ->flatten must be fixed
                    $count = str_replace(['{', '}', 'count,'], '', $macro->flatten(false));
                    $count = empty($count) ? '0' : $count;
                    $csvLine = [$spaceKey, $addonName, $macroName, $count];
                    $this->storeAsCsv($csvLine, $fileExtension);
                }
            }
        }

        $this->logger->debug("END");
    }
}

function main(): void
{
    $spaceMode = SpaceTypeEnum::SPACE_SINGLE;
    $addonMode = AddonTypeEnum::ADDON_SINGLE;

    $thisClazz = new CountMacrosExample();
    $thisClazz->countMacros($spaceMode, $addonMode);
}

main();
