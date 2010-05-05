#!/usr/bin/env php
<?php
    /**
     * Copyright 2010 Peter Lind. All rights reserved.

     * Redistribution and use in source and binary forms, with or without modification, are
     * permitted provided that the following conditions are met:

     *    1. Redistributions of source code must retain the above copyright notice, this list of
     *       conditions and the following disclaimer.

     *    2. Redistributions in binary form must reproduce the above copyright notice, this list
     *       of conditions and the following disclaimer in the documentation and/or other materials
     *       provided with the distribution.

     * THIS SOFTWARE IS PROVIDED BY Peter Lind ``AS IS'' AND ANY EXPRESS OR IMPLIED
     * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
     * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL Peter Lind OR
     * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
     * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
     * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
     * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
     * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
     * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

     * The views and conclusions contained in the software and documentation are those of the
     * authors and should not be interpreted as representing official policies, either expressed
     * or implied, of Peter Lind.
     *
     * PHP version 5
     *
     * @package   Client
     * @author    Peter Lind <peter@plphp.dk>
     * @copyright 2010 Peter Lind
     * @license   http://plind.dk/plseo/#license New BSD License
     * @link      http://www.github.com/Fake51/PLSEO
     */

    $versiontext = <<<TXT
PLSEO - Search Engine Position Checker
Version: 0.8.3
Author: Peter Lind <peter@plphp.dk>

TXT;

    $helptext = <<<TXT
Usage:
    <run_script.sh> -d domain -k keyword [-spuoed]
    <run_script.sh> -f domain_file -l keyword_file [-spuoed]
Options:
    -d domain
        domain is the site that will be checked for in the SERPs. PLSEO will output
        a one-dimensional array of results, unless -l is used. Note that -d takes
        precedence over -f
    -f domain_file
        PLSEO will attempt to read domain_file and will check SERPs for all domains
        found in the file. Put every domain on it's own line in the file. Output
        will be a multidimensional array, with first dimension being keyword (if -l
        is used) or domain (if -l is not used). Note that -d takes precedence over -f
    -k keyword
        The search engines will be checked for just the one keyword, and the script
        will return when all search engines have been checked. Note that -k takes
        precedence over -l
    -l keyword_file
        PLSEO will attempt to read keyword_file and will check SERPs for all
        keywords found in the file. Put every keyword on it's own line in the file;
        keywords consisting of multiple words need not be put in quotes. Output
        will be a multidimensional array, with first dimension being keyword. Note
        that -k takes precedence over -l
    -s search_engine
        If used, PLSEO will only query that particular searchengine. To see which
        options are available for this option, us -e
    -e
        Lists all available engines
    -p page_count
        Number of SERPS to retrieve and search, defaults to 10. Note that beyond 18
        PLSEO will pause for a minute, to avoid getting branded as a bot by engines
    -u user_agent
        User agent string to use. Currently defaults to Chrome
    -o output_file
        Write results to file instead of just outputting to STDOUT
    -v
        Switches the debug flag on, providing more verbose output
    -h
        Outputs this help text

Example:
    ./run_script.sh -d plphp.dk -k "freelance php"

TXT;

require_once dirname(__FILE__) . '/searchclient.php';

$opts = getopt('d:k:f:l:s:p:u:o:hve');

if (isset($opts['h']))
{
    echo $versiontext;
    echo $helptext;
    exit;
}

$client = new SearchClient();

if (isset($opts['e']))
{
    echo $versiontext;
    echo "Available engines:" . PHP_EOL;
    foreach ($client->getEngines() as $engine)
    {
        echo "  " . $engine . PHP_EOL;
    }
    exit;
}

try
{
    if (empty($opts['d']) && empty($opts['f']))
    {
        echo $versiontext;
        echo "Error: Missing one of -d or -f. See helptext (use -h)" . PHP_EOL;
        exit;
    }
    elseif (!empty($opts['d']))
    {
        $client->setSite($opts['d']);
    }
    else
    {
        $client->setSiteFile($opts['f']);
    }

    if (empty($opts['k']) && empty($opts['l']))
    {
        echo $versiontext;
        echo "Error: Missing one of -k or -l. See helptext (use -h)" . PHP_EOL;
        exit;
    }
    elseif (!empty($opts['k']))
    {
        $client->setKeyword($opts['k']);
    }
    else
    {
        $client->setKeywordFile($opts['l']);
    }

    if (!empty($opts['s']))
    {
        $client->setSearchEngine($opts['s']);
    }

    if (!empty($opts['p']))
    {
        $client->setPages($opts['p']);
    }

    if (!empty($opts['o']))
    {
        $client->setOutputFile($opts['o']);
    }

    if (!empty($opts['u']))
    {
        $client->setUserAgent($opts['u']);
    }

    if (isset($opts['v']))
    {
        $client->setDebugFlag();
    }

    var_dump($client->findRankings());
    var_dump($client->getSiteRankings());
}
catch (Exception $e)
{
    echo $versiontext;
    echo "PLSEO failed with an exception. Message:" . PHP_EOL;
    echo "  " . $e->getMessage();
    exit;
}
