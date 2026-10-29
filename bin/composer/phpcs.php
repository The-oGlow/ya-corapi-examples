#!/usr/bin/env php
<?php

namespace ollily\Composer;

include_once __DIR__ . DIRECTORY_SEPARATOR . 'script.inc';

callScript('target/analysis/phpcs', 'phpcsniffer-report.txt', 'phpcs', '--colors');
