<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Skyline\Compiler\Context\ValueCache;


use Countable;
use TASoft\Config\Config;

class ValueCache implements ValueCacheInterface, Countable
{
    private $cache = [];

    /**
     * @inheritDoc
     */
    public function count()
    {
        $count = 0;
        foreach($this->cache as $c) {
            $count += count($c);
        }
        return $count;
    }


    /**
     * ValueCache constructor.
     * @param Config|NULL $cache
     */
    public function __construct(Config $cache = NULL)
    {
        $this->cache = $cache ?: new Config([]);
    }

    /**
     * @inheritDoc
     */
    public function postValue($value, string $name, string $domain = "")
    {
        $dom = $this->_getDomain($domain);
        $dom[$name] = $value;
    }

    /**
     * @inheritDoc
     */
    public function fetchValue(string $name, string $domain = "")
    {
        $dom = $this->_getDomain($domain);
        return $dom[$name] ?? NULL;
    }

    /**
     * @inheritDoc
     */
    public function fetchValues(string $domain = "")
    {
        /** @var Config $dom */
        $dom = $this->_getDomain($domain);
        return $dom->getValues();
    }

    public function fetchAll() {
        $values = [];
        foreach($this->cache as $domain => $values) {
            foreach($values as $key => $value)
                $values[ sprintf("%s.%s", $domain!='<NULL>' ?: '', $key) ] = $value;
        }
        return $values;
    }


    /**
     * @param $domain
     * @return mixed|null
     * @internal
     */
    private function _getDomain($domain) {
        if(!$domain) {
            if(!isset($this->cache['<NULL>']))
                $this->cache['<NULL>'] = new Config([]);
            return $this->cache['<NULL>'];
        }

        if(!isset($this->cache[$domain]))
            $this->cache[$domain] = new Config([]);
        return $this->cache[$domain];
    }
}