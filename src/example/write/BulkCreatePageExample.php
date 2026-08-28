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

use oglow\example\AbstractRestApiExample;
use Psr\Log\LoggerInterface;
use Monolog\ConsoleLogger;
use oglow\tools\Yacorapi\IResponse;
use oglow\tools\Yacorapi\Response\ResponseAddonMacroDecorate;
use oglow\tools\Yacorapi\Macro\AddonTypeEnum;

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
            $this->logger->info('Create pages as children of', [$spaceRootPageId]);
            // API CALL

            /** @var ResponseAddonMacroDecorate $addonSet */
            $addonSet = $this->apiClient->prepareAddonSet($addonMode);
            $mapAddons = $addonSet->getResponse();
            if (!$mapAddons->isEmpty()) {
                foreach ($mapAddons as $addOnKey => $macroNames) {
                    $this->logger->info('Create child root addon', [$spaceRootPageId, $addOnKey]);
                    // API CALL
                    $addonPageId = -2;
                    $cntMacros = $macroNames->count();
                    $cntIdx = 0;
                    if ($cntMacros > 0) {
                        foreach ($macroNames as $macroName) {
                            $this->logger->info('Create macro page', [++$cntIdx, $cntMacros, $addonPageId, $addOnKey, $macroName]);
                            // API CALL
                        }
                    } else {
                        $this->logger->info('The addon has no macros defined', [$addonMode->name]);
                    }
                }
            } else {
                $this->logger->info('The addonMode has no addons defined', [$addonMode->name]);
            }
        } else {
            $this->logger->critical('Homepage not found');
        }
    }
}

function main(): void {

    /** Space */
    $spaceKey = 'CMMN';
    $addonMode = AddonTypeEnum::ADDON_BLOCKER;

    $thisClazz = new BulkCreatePageExample();

    $thisClazz->bulkCreate($spaceKey, $addonMode);
}

main();
