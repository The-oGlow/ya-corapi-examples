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

use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\Client\IRapiClientBase;
use oglow\tools\Yacorapi\Data\ItemTypeEnum;
use oglow\tools\Yacorapi\Response\ResponseParameter;
use oglow\tools\Yacorapi\Store\CsvFileAdapter;
use ollily\Tools\Batch\DataKeyEnum;
use ollily\Tools\EnvironmentHelper;
use ollily\Tools\Twig\TemplateFactory;
use Psr\Log\LoggerInterface;

/**
 * Creates a new page with the help of a twig template. The data is coming from a csv file.
 */
class CreatePageWithTemplateExample extends AbstractRestApiExample
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
    ): void {
        $this->logger->info(
            'START spaceKey,parentPageTitle,templateFile,dataFile,itemType',
            [$spaceKey, $parentPageTitle, $templateFile, $dataFile, $itemType]
        );

        // Find entryPoint
        $parentPageId = $this->apiClient->checkPageExists($spaceKey, $parentPageTitle);

        if (IRapiClientBase::RESP_VAL_PAGE_ID_NO !== $parentPageId) {
            // Load template factory
            $factory = new TemplateFactory(dirname($templateFile));

            // Load dataFile
            $loadedData = CsvFileAdapter::readData($dataFile, true);

            foreach ($loadedData as $templateData) {
                $pageTitle = $templateData->get(DataKeyEnum::TITLE, $templateData->get(DataKeyEnum::NAME, ''));
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

    $demoPath = '//input//oglow//poc//';

    $templateFile = EnvironmentHelper::getProjectRoot() . $demoPath . '';
    $dataFile = EnvironmentHelper::getProjectRoot() . $demoPath . 'person-one.csv';

    $thisClazz = new CreateNewPageExample();
    $thisClazz->createPage($spaceKey, $parentPageTitle, $templateFile, $dataFile);
}

main();
