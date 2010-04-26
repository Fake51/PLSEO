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
     * @package   Engine
     * @author    Peter Lind <peter@plphp.dk>
     * @copyright 2010 Peter Lind
     * @license   http://plind.dk/plseo/#license New BSD License
     * @link      http://www.github.com/Fake51/PLSEO
     */

    /**
     * Abstract base engine class - all individual engine classes should inherit
     * from here
     *
     * @package    Engine
     * @author     Peter Lind <peter@plphp.dk>
     */
abstract class SearchEngine
{
    protected $max_pages;
    protected $site;
    protected $keyword;
    private $_current_page = 0;

    protected $debug = false;

    private $_user_agent = "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/532.5 (KHTML, like Gecko) Chrome/4.0.249.43 Safari/532.5";

    public function __construct($site, $keyword, $pages = 10)
    {
        if (!is_string($site) || empty($site)) throw new Exception ("Bad site query for");
        $this->site = strpos($site, 'http') !== false ? substr($site, strpos($site, '://') + 3) : $site;
        $this->site = substr($this->site, -1) == '/' ? substr($this->site, 0, -1) : $this->site;

        $this->max_pages = $pages;
        $this->keyword = $keyword;
    }

    /**
     * returns the user agent used for querying search engines
     *
     * @access public
     * @return string
     */
    public function getUserAgent()
    {
        return $this->_user_agent;
    }

    /**
     * sets the user agent used for querying search engines
     *
     * @param string $useragent
     *
     * @access public
     * @return $this
     */
    public function setUserAgent($useragent)
    {
        if (!is_string($useragent)) throw new Exception("New user agent is not a string");
        $this->_user_agent = $useragent;
        return $this;
    }

    /**
     * returns a filename for a cookie based on Engine name
     *
     * @access protected
     * @return string
     */
    protected function getCookieFileName()
    {
        return get_class($this) . '_cookie.txt';
    }

    /**
     * returns an array consisting of
     * page => item on page
     *
     * null returned indicates the site wasn't
     * found within the specified number of pages
     *
     * @param string $site    - site to look for
     * @param string $keyword - keyword to check
     *
     * @access public
     * @return array|null
     */
    abstract public function getNextResultPage();

    public function getCurrentPage()
    {
        return $this->_current_page;
    }

    public function nextPage()
    {
        return ++$this->_current_page;
    }

    public function setDebugMode($bool)
    {
        $this->debug = !!$bool;
    }
}
