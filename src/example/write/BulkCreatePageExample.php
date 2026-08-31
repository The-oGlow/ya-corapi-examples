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

namespace oglow\example\write;

use Ds\Collection;
use Ds\Map;
use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\Helper\ContentHelper;
use oglow\tools\Yacorapi\IResponse;
use oglow\tools\Yacorapi\Macro\AddonTypeEnum;
use oglow\tools\Yacorapi\Response\ResponseAddonMacroDecorate;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class BulkCreatePageExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct();

        $this->logger->debug('END');
    }

    public function bulkCreate(string $spaceKey, string $pageTitle, AddonTypeEnum $dataMode): void
    {
        $startingPoint = $this->creatStartingPoint($spaceKey, $pageTitle);

        if (IResponse::VAL_PAGE_ID_NO !== $startingPoint) {
            $this->logger->info('Starting point', [$spaceKey, $pageTitle, $startingPoint]);

            $allData = $this->prepareDataLevelOne($dataMode);
            if (!$allData->isEmpty()) {
                foreach ($allData as $dataName => $dataItems) {
                    $dataNamePageId = $this->createLevelOne($spaceKey, $startingPoint, $dataName);

                    if ($dataNamePageId == IResponse::VAL_PAGE_ID_NO) {
                        $this->logger->warning('Level 1 with error processed', [$dataName]);
                    } else {
                        $dataItemPageId = $this->createLevelTwo($spaceKey, $dataNamePageId, $dataName, $dataItems);
                        if ($dataItemPageId == IResponse::VAL_PAGE_ID_NO) {
                            $this->logger->warning('Level 2 with error processed', [$dataName]);
                        } else {
                            $this->logger->info('Level 2 processed', [$dataName, $dataItems->count()]);
                        }
                        $this->logger->info('Level 1 processed', [$dataName]);
                    }
                }
                $this->logger->info('Starting point processed', [$spaceKey, $pageTitle, $startingPoint]);
            } else {
                $this->logger->warning('No data defined', [$dataMode->name]);
            }
        } else {
            $this->logger->critical('Starting point not defined');
        }
    }

    protected function creatStartingPoint(string $spaceKey, string $pageTitle): int
    {
        $startingPoint = $this->apiClient->checkPageExists($spaceKey, $pageTitle);

        if (IResponse::VAL_PAGE_ID_NO == $startingPoint) {
            // Create Starting point
            $this->logger->info('Starting point must be created', [$spaceKey, $pageTitle, $startingPoint]);

            $spaceRootPageId = $this->apiClient->spaceHomepage($spaceKey);
            $this->logger->info('Homepage of space', [$spaceKey, $spaceRootPageId]);

            if (IResponse::VAL_PAGE_ID_NO !== $spaceRootPageId) {
                // Homepage found
                $pageBody = '';
                $result = $this->apiClient->createPage($spaceKey, $pageTitle, $pageBody, $spaceRootPageId);
                if ($result->checkStatus()) {
                    $startingPoint = $result->getValue(IResponse::KEY_ID);
                    $this->logger->info('Starting point created', [$spaceKey, $spaceRootPageId, $pageTitle, $startingPoint]);
                }
            } else {
                // Homepage not found
                $this->logger->critical('Homepage not found', [$spaceKey]);
            }
        } else {
            // Starting point exists
            $this->logger->info('Starting point already exists', [$spaceKey, $pageTitle, $startingPoint]);
        }

        return $startingPoint;
    }

    protected function createLevelOne(string $spaceKey, int $parentPageId, string $dataName): int
    {
        $pageTitle = $dataName;
        $pageBody = $dataName;

        $dataNamePageId = $this->apiClient->checkPageExists($spaceKey, $pageTitle);
        if ($dataNamePageId == IResponse::VAL_PAGE_ID_NO) {
            // Create page
            $result = $this->apiClient->createPage($spaceKey, $pageTitle, $pageBody, $parentPageId);
            if ($result->checkStatus()) {
                $dataNamePageId = $result->getValue(IResponse::KEY_ID);
                $this->logger->info('Create level 1 page', [$spaceKey, $parentPageId, $dataName, $dataNamePageId]);
            }
        } else {
            // Update page
            $this->logger->info('Level 1 page already exists', [$spaceKey, $parentPageId, $dataName, $dataNamePageId]);
        }

        return $dataNamePageId;
    }

    protected function createLevelTwo(string $spaceKey, int $parentPageId, string $dataName, Collection $dataItems): int
    {
        $dataItemPageId = IResponse::VAL_PAGE_ID_NO;
        $dataItemsMax = $dataItems->count();
        if ($dataItemsMax > 0) {
            $idxCount = 0;
            foreach ($dataItems as $dataItem) {
                ++$idxCount;
                [$dataItemName, $dataItemValue] = $this->prepareDataLevelTwo($dataItem);
                $result = $this->apiClient->createOrUpdatePage($spaceKey, $dataItemName, $dataItemValue, $parentPageId);
                if ($result->checkStatus()) {
                    $dataItemPageId = $result->getValue(IResponse::KEY_ID);
                    $this->logger->info('CreateOrUpdate level 2 page', [$spaceKey, $parentPageId, $dataName, $dataItemName, $dataItemPageId]);
                }
            }
        } else {
            $this->logger->warning('Level 2 has no data defined', [$spaceKey, $parentPageId, $dataName]);
        }

        return $dataItemPageId;
    }

    /**
     * @param AddonTypeEnum $dataMode
     *
     * @return Collection
     */
    protected function prepareDataLevelOne(AddonTypeEnum $dataMode): Collection
    {
        /** @var ResponseAddonMacroDecorate $dataSet */
        $dataSet = $this->apiClient->prepareAddonSet($dataMode);

        return $dataSet->getResponse();
    }

    /**
     * @param string $dataItem
     *
     * @return array<mixed,mixed>
     */
    protected function prepareDataLevelTwo(string $dataItem): array
    {
        $dataItemName = $dataItem;
        [$dataItemValue] = $this->prepareDataLevelThree($dataItem);

        return [$dataItemName, $dataItemValue];
    }

    /**
     * @param string $dataItem
     *
     * @return array<mixed,mixed>
     */
    protected function prepareDataLevelThree(string $dataItem): array
    {
        $parameters = new Map();
        $bodyContent = 'Content of the macro body';
        $dataItemValue = ContentHelper::prepareMacro($dataItem, $parameters, $bodyContent);

        return [$dataItemValue];
    }
}

function main(): void
{
    /** Space */
    $spaceKey = 'CLOUDMIG';
    /** Starting point title */
    $pageTitle = 'Bulk create pages';

    /** AddonMode, used for this example only */
    $dataMode = AddonTypeEnum::ADDON_SINGLE;

    $thisClazz = new BulkCreatePageExample();
    $thisClazz->bulkCreate($spaceKey, $pageTitle, $dataMode);
}

main();
