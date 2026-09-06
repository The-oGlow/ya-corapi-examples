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

use Ds\Map;
use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\Client\IRapiClientBase;
use oglow\tools\Yacorapi\ConstData;
use oglow\tools\Yacorapi\Data\ItemTypeEnum;
use oglow\tools\Yacorapi\Helper\ContentHelper;
use oglow\tools\Yacorapi\IResponse;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class CreateNewPageExample extends AbstractRestApiExample
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
        string $pageTitle,
        string $pageBody = IRapiClientBase::REQ_VAL_BODY_EMPTY,
        string $parentPageTitle = IRapiClientBase::REQ_VAL_PAGE_TITLE_EMPTY,
        ItemTypeEnum $itemType = IRapiClientBase::REQ_VAL_ITEM_TYPE_PAGE
    ): void {
        $this->logger->info(
            'START spaceKey,parentPageTitle,pageTitle,itemType,strlen(pageBody)',
            [$spaceKey, $parentPageTitle, $pageTitle, $itemType, strlen($pageBody)]
        );

        $parentPageId = $this->apiClient->checkPageExists($spaceKey, $parentPageTitle);

        if (IRapiClientBase::RESP_VAL_PAGE_ID_NO !== $parentPageId) {
            $result = $this->apiClient->createPage($spaceKey, $pageTitle, $pageBody, $parentPageId, IRapiClientBase::REQ_VAL_COMMENT_EMPTY, $itemType);
            if ($result->checkStatus()) {
                $pageId = $result->getValue(IResponse::KEY_ID);
                $this->outputData($result);
                $this->logger->info('Page created', [$spaceKey, $parentPageTitle, $pageTitle, $pageId]);
            } else {
                $this->logger->error('Page not created', [$result->getError()]);
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

    $parapgraph = '<p>This is <br/> a <strong>new</strong> page</p>';
    $macroStatusHtml = '<p><ac:structured-macro ac:name="status" ac:schema-version="1">" .
        "<ac:parameter ac:name="colour">Green</ac:parameter>" .
        "<ac:parameter ac:name="title">Low</ac:parameter></ac:structured-macro></p>';
    $macroStatusGenerated = ContentHelper::prepareMacro('status', new Map(['title' => 'high', 'colour' => 'Red']), '');
    $macroPanelGenerated = ContentHelper::prepareMacro(
        'panel',
        new Map(
            ['borderColor' => 'darkgrey', 'bgColor' => '#efefef', 'titleColor' => 'white', 'borderWidth' => '5',
                'titleBGColor' => 'darkred', 'borderStyle' => 'solid', 'title' => 'Panel Title']
        ),
        '<i>Panel Text</i>'
    );
    $macroSectionGenerated = ContentHelper::prepareMacro(
        'section',
        new Map(['border' => 'true']),
        ContentHelper::prepareMacro('column', new Map(), '<u>left</u>') . ContentHelper::prepareMacro('column', new Map(['width' => '33%']), '<i>right</i>')
    );
    $title = sprintf('%s %s-%s', 'NEW PAGE', ConstData::getTsNow(), 0);
    $body = $parapgraph . $macroStatusHtml . $macroStatusGenerated . $macroPanelGenerated . $macroSectionGenerated;

    $thisClazz = new CreateNewPageExample();

    $thisClazz->createPage($spaceKey, $title, $body, $parentPageTitle);
}

main();
