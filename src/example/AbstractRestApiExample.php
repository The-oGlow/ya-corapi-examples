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

namespace oglow\example;

use Monolog\ConsoleLogger;
use oglow\tools\Yacorapi\Client\IRapiClientBase;
use oglow\tools\Yacorapi\Client\RapiClient;
use oglow\tools\Yacorapi\IRapiClient;
use oglow\tools\Yacorapi\IResponse;
use Psr\Log\LoggerInterface;

abstract class AbstractRestApiExample extends AbstractExample
{
    protected IRapiClient $apiClient;

    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');
        parent::__construct($outputFileName);

        $this->apiClient = RapiClient::newClient(level: self::LEVEL_DEFAULT);

        $this->logger->debug('END');
    }

    public function createExamplePage(string $spaceKey, string $pageTitle, string $pageBody, int $parentPageId = IRapiClientBase::REQ_VAL_PARENT_ID_NO): int
    {
        $this->logger->info('START spaceKey,pageTitle,parentPageId,strlen(pageBody)', [$spaceKey, $pageTitle, $parentPageId, strlen($pageBody)]);

        $pageId = IRapiClientBase::RESP_VAL_PAGE_ID_NO;

        if (IRapiClientBase::REQ_VAL_PARENT_ID_NO == $parentPageId) {
            $spaceRootPageId = $this->apiClient->spaceHomepage($spaceKey);
            if (IRapiClientBase::RESP_VAL_PAGE_ID_NO !== $spaceRootPageId) {
                $parentPageId = $spaceRootPageId;
            }
        }

        $result = $this->apiClient->createOrUpdatePage($spaceKey, $pageTitle, $pageBody, $parentPageId);
        if ($result->checkStatus()) {
            $pageId = (int) $result->getValue(IResponse::KEY_ID);
            $this->logger->info('Page created/updated', [$spaceKey, $parentPageId, $pageTitle, $pageId]);
        } else {
            $this->logger->error('Page not created/updated', [$result->getError()]);
        }
        $this->logger->info('END pageId', [$pageId]);

        return $pageId;
    }

    protected function createOrUpdateExamplePage(string $spaceKey, string $pageTitle, string $pageBody, int $parentPageId): int
    {
        $this->logger->info('START spaceKey,pageTitle,parentPageId,strlen(pageBody)', [$spaceKey, $pageTitle, $parentPageId, strlen($pageBody)]);

        $pageId = IRapiClientBase::RESP_VAL_PAGE_ID_NO;

        $result = $this->apiClient->createOrUpdatePage($spaceKey, $pageTitle, $pageBody, $parentPageId);
        if ($result->checkStatus()) {
            $pageId = (int) $result->getValue(IResponse::KEY_ID);
            $this->logger->info('Page created', [$spaceKey, $parentPageId, $pageTitle, $pageId]);
        } else {
            $this->logger->error('Page not created', [$result->getError()]);
        }

        $this->logger->info('END pageId', [$pageId]);

        return $pageId;
    }
}
