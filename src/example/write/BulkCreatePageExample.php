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
use oglow\tools\Yacorapi\Macro\AddonTypeEnum;
use oglow\tools\Yacorapi\Response\ResponseAddonMacro;
use oglow\tools\Yacorapi\Response\ResponseParameterData;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class BulkCreatePageExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new ConsoleLogger(BulkCreatePageExample::class);

        $this->logger->debug('START');

        parent::__construct();

        $this->logger->debug('END');
    }

    /**
     * Controller function for the bulk process.
     *
     * @param string        $spaceKey  The space for the new pages
     * @param string        $pageTitle The page title of the starting point page
     * @param AddonTypeEnum $dataMode  AddonMode, used for this example only
     */
    public function bulkCreate(string $spaceKey, string $pageTitle, AddonTypeEnum $dataMode): void
    {
        $this->logger->info('START');
        $startingPoint = $this->creatStartingPoint($spaceKey, $pageTitle);

        if (ResponseParameterData::VAL_PAGE_ID_NO !== $startingPoint) {
            $this->logger->info('Starting point', [$spaceKey, $pageTitle, $startingPoint]);

            $allData = $this->prepareAllDataLevel($dataMode);
            if (!$allData->isEmpty()) {
                foreach ($allData as $dataName => $dataItems) {
                    $dataNamePageId = $this->createLevelOne($spaceKey, $startingPoint, $dataName);

                    if ($dataNamePageId == ResponseParameterData::VAL_PAGE_ID_NO) {
                        $this->logger->warning('Level 1 with error processed', [$dataName]);
                    } else {
                        $dataItemPageId = $this->createLevelTwo($spaceKey, $dataNamePageId, $dataName, $dataItems);
                        if ($dataItemPageId == ResponseParameterData::VAL_PAGE_ID_NO) {
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
        $this->logger->info('END');
    }

    /**
     * Creates the entry point (aka root page) for all bulk entries.
     *
     * @param string $spaceKey      The space for the new pages
     * @param string $dataNameTitle The page title of the starting point page
     *
     * @return int The pageId of the starting point or {@link ResponseParameterData::VAL_PAGE_ID_NO}
     */
    protected function creatStartingPoint(string $spaceKey, string $dataNameTitle): int
    {
        $startingPoint = $this->apiClient->checkPageExists($spaceKey, $dataNameTitle);

        if (ResponseParameterData::VAL_PAGE_ID_NO == $startingPoint) {
            // Create Starting point
            $this->logger->info('Starting point must be created', [$spaceKey, $dataNameTitle, $startingPoint]);

            $spaceRootPageId = $this->apiClient->spaceHomepage($spaceKey);
            $this->logger->info('Homepage of space', [$spaceKey, $spaceRootPageId]);

            if (ResponseParameterData::VAL_PAGE_ID_NO !== $spaceRootPageId) {
                // Homepage found
                $pageBody = $this->prepareStartingPointBody();
                $result = $this->apiClient->createPage($spaceKey, $dataNameTitle, $pageBody, $spaceRootPageId);
                if ($result->checkStatus()) {
                    $startingPoint = intval($result->getValue(ResponseParameterData::KEY_ID));
                    $this->logger->info('Starting point created', [$spaceKey, $spaceRootPageId, $dataNameTitle, $startingPoint]);
                }
            } else {
                // Homepage not found
                $this->logger->critical('Homepage not found', [$spaceKey]);
            }
        } else {
            // Starting point exists
            $this->logger->info('Starting point already exists', [$spaceKey, $dataNameTitle, $startingPoint]);
        }

        return $startingPoint;
    }

    /**
     * Creates the page directly under the starting point.
     *
     * @param string $spaceKey     The space for the new pages
     * @param int    $parentPageId The parent pageId for this page (in this case the starting point)
     * @param string $dataName     The name of the page to create
     *
     * @return int The pageId of the create page or {@link ResponseParameterData::VAL_PAGE_ID_NO}
     */
    protected function createLevelOne(string $spaceKey, int $parentPageId, string $dataName): int
    {
        [$dataNameTitle, $dataNameBody] = $this->prepareDataLevelOne($dataName);

        $dataNamePageId = $this->apiClient->checkPageExists($spaceKey, $dataNameTitle);
        if ($dataNamePageId == ResponseParameterData::VAL_PAGE_ID_NO) {
            // Create page
            $result = $this->apiClient->createPage($spaceKey, $dataNameTitle, $dataNameBody, $parentPageId);
            if ($result->checkStatus()) {
                $dataNamePageId = intval($result->getValue(ResponseParameterData::KEY_ID));
                $this->logger->info('Create level 1 page', [$spaceKey, $parentPageId, $dataName, $dataNamePageId]);
            }
        } else {
            // Update page
            $this->logger->info('Level 1 page already exists', [$spaceKey, $parentPageId, $dataName, $dataNamePageId]);
        }

        return $dataNamePageId;
    }

    /**
     * Creates the page under the "level one" page.
     *
     * @param string                  $spaceKey     The space for the new pages
     * @param int                     $parentPageId The parent pageId for this page (in this case the "level one" pageId)
     * @param string                  $dataName     The name of th "level one" page
     * @param Collection<mixed,mixed> $dataItems    A collection of page names to create on "level two"
     *
     * @return int The pageId of the last create "level two" page or {@link ResponseParameterData::VAL_PAGE_ID_NO}
     */
    protected function createLevelTwo(string $spaceKey, int $parentPageId, string $dataName, Collection $dataItems): int
    {
        $dataItemPageId = ResponseParameterData::VAL_PAGE_ID_NO;
        $dataItemsMax = $dataItems->count();
        if ($dataItemsMax > 0) {
            $idxCount = 0;
            foreach ($dataItems as $dataItem) {
                ++$idxCount;
                [$dataItemTitle, $dataItemBody] = $this->prepareDataLevelTwo($dataItem);
                $this->storeOrg($dataItemBody, $dataItemTitle);

                $result = $this->apiClient->createOrUpdatePage($spaceKey, $dataItemTitle, $dataItemBody, $parentPageId);
                $this->storeMod($result->getBody(), $dataItemTitle);

                if ($result->checkStatus()) {
                    $dataItemPageId = intval($result->getValue(ResponseParameterData::KEY_ID));
                    $this->logger->info('CreateOrUpdate level 2 page', [$spaceKey, $parentPageId, $dataName, $dataItemTitle, $dataItemPageId]);
                }
            }
        } else {
            $this->logger->warning('Level 2 has no data defined', [$spaceKey, $parentPageId, $dataName]);
        }

        return $dataItemPageId;
    }

    /**
     * Returns a collection of page names for the "level one", "level two" pages.
     *
     * @param AddonTypeEnum $dataMode AddonMode, used for this example only
     *
     * @return Collection<mixed,mixed> A collection of page names for the "level one", "level two" pages
     */
    protected function prepareAllDataLevel(AddonTypeEnum $dataMode): Collection
    {
        /** @var ResponseAddonMacro $dataSet */
        $dataSet = $this->apiClient->prepareAddonSet($dataMode);

        return $dataSet->getRawData();
    }

    /**
     * Returns the "level one" content : page title (itemName) and body (itemValue).
     *
     * @param string $dataName An item containing the data for the page
     *
     * @return array<mixed,mixed> The "level one" page title (itemName) and body (itemValue)
     */
    protected function prepareDataLevelOne(string $dataName): array
    {
        $dataNameTitle = $dataName;
        $dataNameBody = ContentHelper::prepareHeading($dataName, 2) . $this->prepareToc(0);

        return [$dataNameTitle, $dataNameBody];
    }

    /**
     * Returns the "level two" content : page title (itemName) and body (itemValue).
     *
     * @param string $dataItem An item containing the data for the page
     *
     * @return array<mixed,mixed> The "level two" page title (itemName) and body (itemValue)
     */
    protected function prepareDataLevelTwo(string $dataItem): array
    {
        $dataItemTitle = $dataItem;
        [$dataItemBody] = $this->prepareDataLevelThree($dataItem);

        return [$dataItemTitle, $dataItemBody];
    }

    /**
     * Returns the "level three" content : body (itemValue).
     *
     * @param string $dataItem An item containing the data for the page
     *
     * @return array<mixed,mixed> The "level three" body (itemValue)
     */
    protected function prepareDataLevelThree(string $dataItem): array
    {
        $macroParameter = new Map();
        $macroBody = 'Content of the macro body';
        $dataItemBody = ContentHelper::prepareMacro($dataItem, $macroParameter, $macroBody);

        return [$dataItemBody];
    }

    /**
     * @return string The page body for the starting point
     */
    protected function prepareStartingPointBody(): string
    {
        return $this->prepareToc();
    }

    /**
     * @param int $style level 1-6, 0=disable style (Default: 2)
     *
     * @return string A table of contents macro to show the children pages
     */
    protected function prepareToc(int $style = 2): string
    {
        $parameters = new Map();
        $parameters->put('all', 'true');
        if ($style > 0) {
            $parameters->put('style', "h$style");
        }
        $parameters->put('sort', 'title');

        return ContentHelper::prepareMacro('children', $parameters);
    }
}

function main(): void
{
    /** Space */
    $spaceKey = 'CLOUDMIG';
    /** Starting point title */
    $pageTitle = 'Bulk create pages';

    /** AddonMode, used for this example only */
    $dataMode = AddonTypeEnum::ADDON_ALL;

    $thisClazz = new BulkCreatePageExample();
    $thisClazz->bulkCreate($spaceKey, $pageTitle, $dataMode);
}

main();
