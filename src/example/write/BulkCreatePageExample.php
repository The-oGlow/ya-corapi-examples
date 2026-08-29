<?php

/*
 * Copyright 2026 GLO03.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace oglow\example\write;

use Ds\Collection;
use Ds\Map;
use oglow\example\AbstractRestApiExample;
use Psr\Log\LoggerInterface;
use Monolog\ConsoleLogger;
use oglow\tools\Yacorapi\IResponse;
use oglow\tools\Yacorapi\Response\ResponseAddonMacroDecorate;
use oglow\tools\Yacorapi\Macro\AddonTypeEnum;
use oglow\tools\Yacorapi\Helper\ContentHelper;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class BulkCreatePageExample extends AbstractRestApiExample {

    private LoggerInterface $logger;

    public function __construct() {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");

        parent::__construct();

        $this->logger->debug("END");
    }

    public function bulkCreate(String $spaceKey, AddonTypeEnum $addonMode): void {

        $spaceRootPageId = $this->apiClient->spaceHomepage($spaceKey);

        if (IResponse::NO_PAGE_ID != $spaceRootPageId) {
            $this->logger->info('Create pages as children of', [$spaceKey, $spaceRootPageId]);
            // API CALL

            /** @var ResponseAddonMacroDecorate $addonSet */
            $addonSet = $this->apiClient->prepareAddonSet($addonMode);
            $mapAddons = $addonSet->getResponse();
            if (!$mapAddons->isEmpty()) {
                foreach ($mapAddons as $addonName => $macros) {
                    $this->createAddon($spaceKey, $spaceRootPageId, $addonName, $macros);
                }
            } else {
                $this->logger->warning('The addonMode has no addons defined', [$addonMode->name]);
            }
        } else {
            $this->logger->critical('Homepage not found');
        }
    }

    protected function createAddon(string $spaceKey, int $parentPageId, string $addonName, Collection $macros): void {
        $pageTitle = $addonName;
        $pageBody = $addonName;

        $addonPageId = $this->checkPageExists($spaceKey, $pageTitle);
        if ($addonPageId == IResponse::NO_PAGE_ID) {
            $result = $this->apiClient->createPage($spaceKey, $pageTitle, $pageBody, $parentPageId);
            if ($result->checkStatus()) {
                $addonPageId = $result->getValue(IResponse::KEY_ID);
                $this->logger->info('Create addon page', [$spaceKey, $parentPageId, $addonName, $addonPageId]);
            }
        } else {
            $this->logger->info('Addon page already exists', [$spaceKey, $parentPageId, $addonName, $addonPageId]);
        }
        if ($addonPageId !== IResponse::NO_PAGE_ID) {
            $this->createMacros($spaceKey, $addonPageId, $addonName, $macros);
        } else {
            $this->logger->warning('Addon page not created', [$addonName]);
        }
    }

    protected function createMacros(string $spaceKey, int $parentPageId, string $addonName, Collection $macros): void {
        $cntMacros = $macros->count();
        if ($cntMacros > 0) {
            $cntIdx = 0;
            foreach ($macros as $macro) {
                ++$cntIdx;
                $macroPageId = $this->createOrUpdateMacro($spaceKey, $parentPageId, $addonName, $macro);
                if ($macroPageId == IResponse::NO_PAGE_ID) {
                    $this->logger->warning('Macro page not processed', [$cntIdx, $cntMacros, $spaceKey, $parentPageId, $addonName, $macro]);
                }
            }
        } else {
            $this->logger->warning('The addon has no macros defined', [$spaceKey, $parentPageId, $addonName,]);
        }
    }

    protected function createOrUpdateMacro(string $spaceKey, int $parentPageId, string $addonName, string $macro): int {
        $pageTitle = $macro;
        $pageBody = $this->prepareMacroBody($macro);
        
        $macroPageId = $this->checkPageExists($spaceKey, $pageTitle);
        if ($macroPageId == IResponse::NO_PAGE_ID) {
            $result = $this->apiClient->createPage($spaceKey, $pageTitle, $pageBody, $parentPageId);
            if ($result->checkStatus()) {
                $macroPageId = $result->getValue(IResponse::KEY_ID);
                $this->logger->info('Create macro page', [$spaceKey, $parentPageId, $addonName, $macro, $macroPageId]);
            } else {
                $this->logger->warning('Macro page not created', [$spaceKey, $parentPageId, $addonName, $macro]);
            }
        } else {
            $result = $this->apiClient->updatePage($macroPageId, $pageBody, $pageTitle, 'Updated the page body');
            if ($result->checkStatus()) {
                $macroPageId = $result->getValue(IResponse::KEY_ID);
                $this->logger->warning('Updated macro page', [$spaceKey, $parentPageId, $addonName, $macro, $macroPageId]);
            } else {
                $macroPageId = IResponse::NO_PAGE_ID;
                $this->logger->warning('Macro page not updated', [$spaceKey, $parentPageId, $addonName, $macro, $macroPageId]);
            }
        }
        return $macroPageId;
    }

    protected function prepareMacroBody(string $macro): string {
        $body = '';
        $parameters = new Map();
        $body = ContentHelper::prepareMacro($macro, $parameters, $body);

        return $body;
    }

    public function checkPageExists($spaceKey, $pageTitle) {
        $pageId = IResponse::NO_PAGE_ID;
        $result = $this->apiClient->readPagesByTitle($pageTitle, $spaceKey);
        if ($result->checkStatus() && $result->isResultsAvailable()) {
            $firstResult = $result->getResult(0);
            $pageId = $firstResult[IResponse::KEY_ID];
            $this->logger->info('Found the page', [$spaceKey, $pageTitle, $pageId]);
        } else {
            $this->logger->info('Not found the page', [$spaceKey, $pageTitle]);
        }
        return $pageId;
    }
}

function main(): void {

    /** Space */
    $spaceKey = 'CLOUDMIG';
    $addonMode = AddonTypeEnum::ADDON_SINGLE;

    $thisClazz = new BulkCreatePageExample();

    $thisClazz->bulkCreate($spaceKey, $addonMode);
}

main();
