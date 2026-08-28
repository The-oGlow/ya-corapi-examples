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
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

/**
 * FIXME:Remove.
 *
 * @SuppressWarnings(PHPMD)
 */
class CreateNewPageExample extends AbstractRestApiExample
{
    public const int C_PLAYGROUND_ID = 532951146;

    public const string C_SPACE = 'NMAS';

    public const string C_NEW_TITLE = 'NEW PAGE %s-%s';

    public const string C_NEW_BODY = "<p>This is <br/> a new page</p>\n";

    public static string $C_NEW_MACRO_1;

    public static string $C_NEW_MACRO_2;

    public static string $C_NEW_MACRO_3;

    public static string $C_NEW_MACRO_4;

    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");

        parent::__construct();
        $this->init();

        $this->logger->debug("END");
    }

    public function init(): void
    {
        self::$C_NEW_MACRO_1 = "<p><ac:structured-macro ac:name=\"status\" ac:schema-version=\"1\">" .
        "<ac:parameter ac:name=\"colour\">Green</ac:parameter>" .
        "<ac:parameter ac:name=\"title\">Low</ac:parameter></ac:structured-macro></p>";
        self::$C_NEW_MACRO_2 = ContentHelper::prepareMacro('status', new Map(['title' => 'high', 'colour' => 'Red']), '');
        self::$C_NEW_MACRO_3 = ContentHelper::prepareMacro(
            'panel',
            new Map(
                ['borderColor' => 'red', 'bgColor' => '#eeeeee', 'titleColor' => 'white', 'borderWidth' => '2',
                    'titleBGColor' => 'blue', 'borderStyle' => 'solid', 'title' => 'Panel Title']
            ),
            "Panel Text"
        );
        self::$C_NEW_MACRO_4 = ContentHelper::prepareMacro(
            'section',
            new Map(),
            ContentHelper::prepareMacro('column', new Map(), 'left') . ContentHelper::prepareMacro('column', new Map(['width' => '33%']), 'right')
        );
    }

    public function createPage(
        string $spaceKey,
        string $pageTitle,
        string $pageBody = '',
        int $parentId = IRapiClientBase::REQ_NO_PARENT,
        ItemTypeEnum $itemType = IRapiClientBase::REQ_ITEM_TYPE_PAGE
    ): void {
        $this->logger->debug("START spaceKey,pageTitle,parentId,itemType,empty(pageBody)", [$spaceKey, $pageTitle, $parentId, $itemType, empty($pageBody)]);

        $response = $this->apiClient->createPage($spaceKey, $pageTitle, $pageBody, $parentId, $itemType);
        $this->outputData($response);
        $this->logger->debug("END");
    }
}

function main(): void
{
    $idx = 0;

    $thisClazz = new CreateNewPageExample();

    $title = sprintf(CreateNewPageExample::C_NEW_TITLE, ConstData::getTsNow(), $idx++);
    $body = CreateNewPageExample::C_NEW_BODY .
    CreateNewPageExample::$C_NEW_MACRO_1 .
    CreateNewPageExample::$C_NEW_MACRO_2 .
    CreateNewPageExample::$C_NEW_MACRO_3 .
    CreateNewPageExample::$C_NEW_MACRO_4;
    $thisClazz->createPage(CreateNewPageExample::C_SPACE, $title, $body, CreateNewPageExample::C_PLAYGROUND_ID);
}

main();
