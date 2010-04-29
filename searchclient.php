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

require_once dirname(__FILE__) . '/searchengine.php';

    /**
     * client class for querying search engines
     *
     * @package Client
     * @author  Peter Lind <peter@plphp.dk>
     */
class SearchClient
{
    private $_site;
    private $_keyword;
    private $_max_pages = 10;
    private $_user_agent = '';
    private $_search_engine;
    private $_debugging = false;

    const GOOGLECOM = 'GoogleComEngine';
    const GOOGLEDK = 'GoogleDkEngine';
    const GOOGLEUK = 'GoogleUkEngine';
    const YAHOOCOM = 'YahooComEngine';
    const YAHOODK = 'YahooDkEngine';
    const YAHOOUK = 'YahooUkEngine';
    const BINGUK = 'BingUkEngine';
    const BINGUS = 'BingUsEngine';
    const BINGDK = 'BingDkEngine';

    private $_engines = array(
        self::GOOGLECOM,
        self::GOOGLEDK,
        self::GOOGLEUK,
        self::YAHOOCOM,
        self::YAHOODK,
        self::YAHOOUK,
        self::BINGDK,
        self::BINGUK,
        self::BINGUS,
    );

    /**
     * sets the search engine to use. Reduces the number of engines to use from
     * all to just this one
     *
     * @param string $engine
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function setSearchEngine($engine)
    {
        if (!in_array($engine, $this->_engines))
        {
            throw new Exception("No such engine" . PHP_EOL);
        }
        $this->_search_engine = $engine;
    }

    public function getEngines()
    {
        return $this->_engines;
    }

    /**
     * sets the number of SERPs to retrieve - defaults to 10
     *
     * @param int $pages
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function setPages($pages)
    {
        if (intval($pages) == 0)
        {
            throw new Exception("Invalid variable for pages specified" . PHP_EOL);
        }
        $this->_max_pages = $pages;
    }

    /**
     * sets the user agent to use when querying search engines
     *
     * @param string $useragent
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function setUserAgent($useragent)
    {
        if (!is_string($useragent) || strlen($useragent) == 0)
        {
            throw new Exception("Invalid user agent specified" . PHP_EOL);
        }
        $this->_user_agent = $useragent;
    }

    /**
     * turns on debugging mode, meaning more output
     *
     * @access public
     * @return void
     */
    public function setDebugFlag()
    {
        $this->_debugging = true;
    }

    /**
     * sets a single site to query SERPs for
     *
     * @param string $site
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function setSite($site)
    {
        if (!is_string($site) || strlen($site) == 0)
        {
            throw new Exception("Invalid site specified" . PHP_EOL);
        }
        $this->_site = $site;
    }

    /**
     * sets a single keyword to query SERPs for
     *
     * @param string $keyword
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function setKeyword($keyword)
    {
        if (!is_string($keyword) || strlen($keyword) == 0)
        {
            throw new Exception("Invalid keyword specified" . PHP_EOL);
        }
        $this->_keyword = $keyword;
    }

    public function setOutputFile($outputfile)
    {
        throw new Exception("Functionality not implemented yet" . PHP_EOL);
    }

    public function setKeywordFile($keywordfile)
    {
        throw new Exception("Functionality not implemented yet" . PHP_EOL);
    }

    public function setSiteFile($sitefile)
    {
        throw new Exception("Functionality not implemented yet" . PHP_EOL);
    }

    /**
     * main function - registers engine(s) and starts the querying
     *
     * @param string $engine - engine to use, defaults to all known
     *
     * @access public
     * @return array
     */
    public function findRankings()
    {
        if (empty($this->_keyword) || empty($this->_site))
        {
            throw new Exception("Lacking keyword or site" . PHP_EOL);
        }
        if (!empty($this->_search_engine))
        {
            $this->_registerEngine($engine);
        }
        else
        {
            foreach ($this->_engines as $e)
            {
                try
                {
                    $this->_registerEngine($e);
                }
                catch (Exception $e)
                {
                    echo $e->getMessage() . PHP_EOL;
                }
            }
        }
        try
        {
            return $this->_queryEngines();
        }
        catch (Exception $e)
        {
            die($e->getMessage() . PHP_EOL);
        }
    }

    /**
     * registers an engine for use for querying page ranks
     *
     * @param string $engine
     *
     * @throws Exception
     * @access private
     * @return void
     */
    private function _registerEngine($engine)
    {
        try
        {
            require_once dirname(__FILE__) . '/engines/' . strtolower($engine) . '.php';
            $engine_object = new $engine($this->_site, $this->_keyword, $this->_max_pages);
            $this->_engines_running[$engine] = $engine_object;
            if ($this->_user_agent)
            {
                $engine_object->setUserAgent($this->_user_agent);
            }
            if (!empty($this->_debugging))
            {
                $engine_object->setDebugMode(true);
            }
        }
        catch (Exception $e)
        {
            throw new Exception("Failed to register engine. Reason: " . $e->getMessage() . PHP_EOL);
        }
    }

    /**
     * does the actual querying using the engines registered with _registerEngine
     * does querying in batches with .25 seconds in between batches and 0.0-0.2
     * seconds between each engine
     *
     * @throws Exception
     * @access private
     * @return array
     */
    private function _queryEngines()
    {
        if (empty($this->_engines_running))
        {
            throw new Exception("No engines to query");
        }
        $i = 0;
        $break = false;
        while ($this->_max_pages > $i && !$break)
        {
            usleep(500000);
            foreach ($this->_engines_running as $name => $engine)
            {
                if (is_object($engine) && ($rank = $engine->getNextResultPage()))
                {
                    $this->_engines_running[$name] = $rank;
                }
                usleep(mt_rand(0, 2) * 100000);
            }
            $break = true;
            foreach ($this->_engines_running as $engine)
            {
                if (is_object($engine))
                {
                    $break = false;
                    break;
                }
            }
            $i++;
        }
        foreach ($this->_engines_running as $name => $engine)
        {
            if (is_object($engine)) $this->_engines_running[$name] = null;
        }
        return $this->_engines_running;;
    }
}
