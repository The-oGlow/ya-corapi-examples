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

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\Client\IRapiClientBase;
use oglow\tools\Yacorapi\ConstData;
use oglow\tools\Yacorapi\Response\ResponseParameterData;
use Psr\Log\LoggerInterface;

/**
 * Description of CreateOrUpdatePageExample.
 *
 * @author GLO03
 */
class CreateOrUpdatePageExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct();

        $this->logger->debug('END');
    }

    public function createOrUpdate(
        string $spaceKey,
        string $pageTitle,
        string $newPageBody,
        string $updatePageBody,
        string $parentPageTitle = IRapiClientBase::REQ_VAL_PAGE_TITLE_EMPTY
    ) {
        $this->logger->info('START spaceKey,pageTitle,parentPageTitle', [$spaceKey, $pageTitle, $parentPageTitle]);

        $parentPageId = $this->apiClient->checkPageExists($spaceKey, $parentPageTitle);

        if (IRapiClientBase::RESP_VAL_PAGE_ID_NO !== $parentPageId) {
            $result = $this->apiClient->createOrUpdatePage($spaceKey, $pageTitle, $newPageBody, $parentPageId);
            $this->storeOrg($result->getBody(), $pageTitle);

            if ($result->checkStatus()) {
                $pageId = intval($result->getValue(ResponseParameterData::KEY_ID));
                $this->logger->info('Created new page', [$spaceKey, $parentPageId, $pageTitle, $pageId]);
            }

            $result = $this->apiClient->createOrUpdatePage($spaceKey, $pageTitle, $updatePageBody, $parentPageId);
            $this->storeMod($result->getBody(), $pageTitle);

            if ($result->checkStatus()) {
                $pageId = intval($result->getValue(ResponseParameterData::KEY_ID));
                $this->logger->info('Updated page', [$spaceKey, $parentPageId, $pageTitle, $pageId]);
            }
        } else {
            $this->logger->error('Parent page not found', [$spaceKey, $parentPageTitle]);
        }
        $this->logger->debug('END');
    }

    // put your code here
}

function main(): void
{
    /** Space */
    $spaceKey = 'CMMN';

    /** Parent page title */
    $parentPageTitle = '98-Playground';

    $title = sprintf('%s %s-%s', 'Create or Update Page', ConstData::getTsNow(), 0);
    $newBody = "Body of new created page";
    $updateBody = "Body of updated page";

    $thisClazz = new CreateOrUpdatePageExample();

    $thisClazz->createOrUpdate($spaceKey, $title, $newBody, $updateBody, $parentPageTitle);
}

main();
