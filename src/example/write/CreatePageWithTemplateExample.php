<?php

/*
 * Copyright 2026 postm.
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

use Ds\Map;
use Monolog\ConsoleLogger;
use oglow\tools\Yacorapi\Store\CsvFileAdapter;
use ollily\Tools\Batch\DataKeyEnum;
use ollily\Tools\Twig\TemplateFactory;
use Psr\Log\LoggerInterface;

/**
 * Creates a new page with the help of a twig template. The data is coming from a csv file.
 *
 * 
 */
class CreatePageWithTemplateExample
{

    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct();

        $this->logger->debug('END');
    }

    public function createPage(
        string $spaceKey,
        string $parentPageTitle,
        string $templateFile,
        string $dataFile,
        ItemTypeEnum $itemType = IRapiClientBase::REQ_VAL_ITEM_TYPE_PAGE
    ): void
    {
        $this->logger->info(
            'START spaceKey,parentPageTitle,templateFile,dataFile,itemType',
            [$spaceKey, $parentPageTitle, $templateFile, $dataFile, $itemType,]
        );    
        
        // Find entryPoint
        $parentPageId = $this->apiClient->checkPageExists($spaceKey, $parentPageTitle);

        if (IRapiClientBase::RESP_VAL_PAGE_ID_NO !== $parentPageId) {
            // Load template factory
            $factory = new TemplateFactory(dirname($templateFile));

            // Load dataFile
            $loadedData = CsvFileAdapter::readData($dataFile, true);

            foreach ($loadedData as $templateData ) {
                $pageTitle= $templateData->get(DataKeyEnum::TITLE, $templateData->get(DataKeyEnum::NAME,''));
                $pageBody = $factory->renderTemplate(basename($templateFile), $templateData); // FIXME

                // Create page
                $result = $this->apiClient->createPage($spaceKey, $pageTitle, $pageBody, $parentPageId, IRapiClientBase::REQ_VAL_COMMENT_EMPTY, $itemType);
                if ($result->checkStatus()) {
                    $pageId = $result->getValue(ResponseParameter::KEY_ID);
                    $this->outputData($result);
                    $this->logger->info('Page created', [$spaceKey, $parentPageTitle, $pageTitle, $pageId]);
                } else {
                    $this->logger->error('Page not created', [$result->getError()]);
                }
            }
        } else {
            $this->logger->error('Parent page not found', [$spaceKey, $parentPageTitle]);
        }

        $this->logger->debug('END');
    }
}

function main(): void
{
    /** Space */
    $spaceKey = 'CMMN';

    /** Parent page title */
    $parentPageTitle = '98-Playground';

    $templateFile = EnvironmentHelper::getProjectRoot() . self::DEMO_PATH . '';
    $dataFile = EnvironmentHelper::getProjectRoot() . self::DEMO_PATH . 'person-one.csv';

    $thisClazz = new CreateNewPageExample();
    $thisClazz->createPage($spaceKey, $parentPageTitle, $templateFile, $dataFile);
}

main();
