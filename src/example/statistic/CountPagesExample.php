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
use oglow\tools\Yacorapi\Data\ItemTypeEnum;
use oglow\tools\Yacorapi\Space\SpaceData;
use oglow\tools\Yacorapi\Space\SpaceTypeEnum;
use oglow\tools\Yacorapi\Statistic\IStatistic;
use oglow\tools\Yacorapi\Statistic\StatisticStatistic;
use oglow\tools\Yacorapi\Statistic\StatisticTypeEnum;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class CountPagesExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    private bool $headerWritten = false;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    public function countPages(SpaceTypeEnum $spaceMode): void
    {
        $spaceData = new SpaceData();
        $spaceKeys = $spaceData->getDataByMode($spaceMode->value);
        $singleFile = false;

        $cntSpaces = count($spaceKeys);
        $cntIdx = 0;
        foreach ($spaceKeys as $spaceKey) {
            echo sprintf("\n\n%s/%s Count in space '%s'\n", ++$cntIdx, $cntSpaces, $spaceKey);
            $this->countItemsInSpace($spaceKey, $singleFile);
        }
    }

    public function countItemsInSpace(string $spaceKey, bool $singleFile = false): void
    {
        $this->logger->debug("START", [$spaceKey]);

        $spaceStatistic = $this->loopPageTypes($spaceKey);
        $this->writeFile($spaceKey, $singleFile, $spaceStatistic);

        $this->logger->debug("END", [$spaceKey]);
    }

    private function loopPageTypes(string $spaceKey): IStatistic
    {
        $this->logger->debug("START", [$spaceKey]);

        $spaceStatistic = new StatisticStatistic($spaceKey, StatisticTypeEnum::SPACE);

        foreach (ItemTypeEnum::TYPES as $pageType) {
            $countPages = $this->apiClient->countItemsinSpace($spaceKey, $pageType);
            $this->logger->info("Count for", [$spaceKey, $pageType->value, $countPages->flatten(false)]);
            $spaceStatistic->addItem($pageType, $countPages);
        }

        $this->logger->debug("END", [$spaceKey]);

        return $spaceStatistic;
    }

    private function writeFile(string $spaceKey, bool $isSingleFile, IStatistic $spaceStatistic): void
    {
        $this->logger->debug("START", [$spaceKey]);

        $fileExtension = $isSingleFile ? '' : $spaceKey;
        $this->logger->notice("Write Data for space to file with extension", [$spaceKey, $fileExtension]);

        if (($isSingleFile && !$this->headerWritten) || !$isSingleFile) {
            $this->storeAsCsv(null, $fileExtension, $spaceStatistic->flattenHeader());
            $this->headerWritten = true;
        }

        foreach ($spaceStatistic->keys() as $itemName) {
            /** @var IStatistic $itemValue */
            $itemValue = $spaceStatistic->getItem($itemName);
            // FIXME: ->flatten must be fixed
            $count = str_replace(['{', '}','count,'], '', $itemValue->flatten(false));
            $count = empty($count) ? '0' : $count;
            $entry = [$spaceKey, $itemName, $count];
            $this->storeAsCsv($entry, $fileExtension);
        }

        $this->logger->debug("END", [$spaceKey]);
    }
}

function main(): void
{
    $spaceMode = SpaceTypeEnum::SPACE_SINGLE;

    $thisClazz = new CountPagesExample();
    $thisClazz->countPages($spaceMode);
}

main();
