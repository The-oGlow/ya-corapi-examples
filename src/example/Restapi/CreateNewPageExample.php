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

namespace oglow\example\Restapi;

use oglow\tools\Yacorapi\Helper\ContentHelper;
use Ds\Map;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

/**
 * FIXME:Remove.
 *
 * @SuppressWarnings(PHPMD)
 */
class CreateNewPageExample extends AbstractRestApiExample
{
    public const       int C_PLAYGROUND_ID = 532951146;

    public const string C_SPACE = 'NMAS';

    public const string C_NEW_TITLE = 'NEW PAGE %s-%s';

    public const string C_NEW_BODY = "<p>This is <br/> a new page</p>\n";

    public static string $C_NEW_MACRO_1;

    public static string $C_NEW_MACRO_2;

    public static string $C_NEW_MACRO_3;

    public static string $C_NEW_MACRO_4;

    public function __construct()
    {
        parent::__construct();
        self::$C_NEW_MACRO_1 =
        "<p><ac:structured-macro ac:name=\"status\" ac:schema-version=\"1\">" .
        "<ac:parameter ac:name=\"colour\">Green</ac:parameter>" .
        "<ac:parameter ac:name=\"title\">Low</ac:parameter></ac:structured-macro></p>";
        self::$C_NEW_MACRO_2 = ContentHelper::prepareMacro('status', new Map( ['title' => 'high', 'colour' => 'Red']), '');
        self::$C_NEW_MACRO_3 = ContentHelper::prepareMacro('html', null, "<style>span{border: 1pt solid darkred !important;}</style>");
        self::$C_NEW_MACRO_4 = ContentHelper::prepareMacro(
            'section',
            null,
            ContentHelper::prepareMacro('column', null, 'left') . ContentHelper::prepareMacro('column', null, 'right')
        );
    }

    public function createPage(string $spaceKey, string $pageTitle, string $pageBody = '', ?int $parentId = null, string $pageType = 'page'): void
    {
        $this->logger->debug("START spaceKey,pageTitle,parentId,pageType,empty(pageBody)", [$spaceKey, $pageTitle, $parentId, $pageType, empty($pageBody)]);

        $response = $this->apiClient->createPage($spaceKey, $pageTitle, $pageBody, $parentId, $pageType);
        $this->outputData($response);
        $this->logger->debug("END");
    }
}

function main(): void
{
    $idx = 0;

    $thisClazz = new CreateNewPageExample();

    $title = sprintf(CreateNewPageExample::C_NEW_TITLE, \oglow\tools\Yacorapi\ConstData::getTsNow(), $idx++);
    $body  =
    CreateNewPageExample::C_NEW_BODY .
    CreateNewPageExample::$C_NEW_MACRO_1 .
    CreateNewPageExample::$C_NEW_MACRO_2 .
    CreateNewPageExample::$C_NEW_MACRO_3 .
    CreateNewPageExample::$C_NEW_MACRO_4;
    $thisClazz->createPage(CreateNewPageExample::C_SPACE, $title, $body, CreateNewPageExample::C_PLAYGROUND_ID);
}

main();
