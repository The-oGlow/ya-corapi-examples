#!/usr/bin/env php
<?php

namespace ollily\Composer;

/**
 * @param string $folder Where the output file is stored
 * @param string $file Name of the output file 
 * @param string $binFile the composer bin file
 * @param string $binFileOpts options for the binFile
 */
function callScript(string $folder, string $file, string $binFile, string $binFileOpts='')
{
    echo "\nScript '$binFile' calling...\n";
    $root = realpath(__DIR__ . str_repeat(DIRECTORY_SEPARATOR . '..', 2)) . DIRECTORY_SEPARATOR;
    $vendorBin = $root . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR;
    $folder = $root . $folder . DIRECTORY_SEPARATOR;
    $errFile = $folder . 'err-' . $file;
    $file = $folder . $file;
    $binFile = $vendorBin . $binFile;
    if (!file_exists($folder)) {
        mkdir($folder, recursive: true);
    }
    if (file_exists($file) && is_file($file)) {
        unlink($file);
    }
    if (file_exists($errFile) && is_file($errFile)) {
        unlink($errFile);
    }

    $ex = 0;
    if (file_exists($binFile) && is_file($binFile)) {
        $result_code = null;
        $execCmd="$binFile $binFileOpts";
        system($execCmd, $result_code);
        if (file_exists($file) && filesize($file) > 0) {
            echo "\nReport\n" . file_get_contents($file);
        }
        if (file_exists($errFile) && filesize($errFile) > 0) {
            echo "\nErrors\n" . file_get_contents($errFile);
        }
        $ex=$result_code;
    } else {
        echo "\n'$binFile' not callable\n";
        $ex = 10;
    }
    exit($ex);
}
