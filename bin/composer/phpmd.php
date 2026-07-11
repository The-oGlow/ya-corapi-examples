#!/usr/bin/env php
<?php

namespace ollily\Composer;

include_once __DIR__ . DIRECTORY_SEPARATOR . 'script.inc';

callScript('target/analysis/phpmd', 'phpmd-report.txt', 'phpmd', 'src,tests text .phpmd.xml.dist --report-file=target/analysis/phpmd/phpmd-report.txt --error-file=target/analysis/phpmd/err-phpmd-report.txt --color --cache --ignore-errors-on-exit --ignore-violations-on-exit');
