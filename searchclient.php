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
    private $_site_array;
    private $_keyword;
    private $_keyword_array;
    private $_max_pages = 10;
    private $_output_file;
    private $_user_agent = '';
    private $_search_engine;
    private $_debugging = false;
    private $_running_multiple_keywords = false;
    private $_engine_results = array();

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

    /**
     * sets the output file to direct output to
     *
     * @param string $outputfile
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function setOutputFile($outputfile)
    {
        if (!is_writable($outputfile))
        {
            throw new Exception("Cannot write to submitted outputfile" . PHP_EOL);
        }
        $this->_output_file = $outputfile;
    }

    /**
     * sets a keyword file to grab keywords from
     *
     * @param string $keywordfile
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function setKeywordFile($keywordfile)
    {
        if (!is_file($keywordfile) || !($file = file_get_contents($keywordfile)))
        {
            throw new Exception("Could not read from keyword file" . PHP_EOL);
        }
        $this->_keyword_array = array_filter(explode("\n", str_replace(array("\r\n", "\r"), "\n", $file)));
    }

    /**
     * sets a site file to grab sites from
     *
     * @param string $sitefile
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function setSiteFile($sitefile)
    {
        if (!is_file($sitefile) || !($file = file_get_contents($sitefile)))
        {
            throw new Exception("Could not read from sitefile" . PHP_EOL);
        }
        $this->_site_array = array_filter(explode("\n", str_replace(array("\r\n", "\r"), "\n", $file)));
    }

    /**
     * runs multiple keyword checks, waiting a minute between each run
     *
     * @access private
     * @return mixed
     */
    private function _run_multiple_keywords()
    {
        $this->_running_multiple_keywords = true;
        $return = array();
        $keycount = count($this->_keyword_array);
        for ($i = 0; $i < $keycount; ++$i)
        {
            $this->_keyword = $this->_keyword_array[$i];
            $result = $this->findRankings(); 
            if (empty($this->_output_file))
            {
                $return[$this->_keyword_array[$i]] = $result;
            }
            else
            {
                $return = $result;
            }

            // avoid final 60 secs wait
            if ($i + 1 == $keycount)
            {
                break;
            }
            sleep(60);
        }
        return $return;
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
        if (!empty($this->_keyword_array) && empty($this->_running_multiple_keywords))
        {
            return $this->_run_multiple_keywords();
        }
        if (empty($this->_keyword) || (empty($this->_site) && empty($this->_site_array)))
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
            $return = $this->_queryEngines();
            if (empty($this->_output_file))
            {
                return $return;
            }
            file_put_contents($this->_output_file, $return, FILE_APPEND);
            return true;
        }
        catch (Exception $e)
        {
            die($e->getMessage() . PHP_EOL);
        }
    }

    /**
     * returns array of rankings per site per engine
     *
     * @throws Exception
     * @access public
     * @return array
     */
    public function getSiteRankings()
    {
        if (empty($this->_engines_running))
        {
            throw new Exception("No engines registered");
        }

        $return = array();
        $sites = empty($this->_site) ? $this->_site_array : array($this->_site);
        foreach ($sites as $site)
        {
            $temp = array();
            foreach ($this->_engines_running as $name => $engine)
            {
                $temp[$name] = $engine->checkResultsForSite($site);
            }
            $return[$site] = $temp;
        }
        return $return;
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
            $engine_object = new $engine($this->_keyword, $this->_max_pages);
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
        while ($this->_max_pages > $i)
        {
            usleep(500000);
            foreach ($this->_engines_running as $name => $engine)
            {
                $engine->getNextResultPage();
                usleep(mt_rand(1, 5) * 50000);
            }
            $i++;
        }
        foreach ($this->_engines_running as $name => $engine)
        {
            $this->_engine_results[$name] = $engine->getResults();
        }
        return $this->_engine_results;
    }
}
