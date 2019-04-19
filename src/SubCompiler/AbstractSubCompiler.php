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

namespace Skyline\Compiler\SubCompiler;


use Skyline\Compiler\AbstractMainCompiler;
use TASoft\Config\Config;

abstract class AbstractSubCompiler implements SubCompilerInterface
{
    /** @var AbstractMainCompiler */
    private $mainCompiler;

    /** @var Config|null */
    private $configuration;

    /** @var Config */
    private $domain;

    public function __construct(AbstractMainCompiler $compiler)
    {
        $this->mainCompiler = $compiler;
    }

    /**
     * @return AbstractMainCompiler
     */
    public function getMainCompiler(): AbstractMainCompiler
    {
        return $this->mainCompiler;
    }

    /**
     * Define a name for the compiler for human identification
     * @return string
     */
    public function getCompilerName() {
        return "Compiler";
    }

    /**
     * @return null|Config
     */
    public function getConfiguration(): ?Config
    {
        return $this->configuration;
    }

    /**
     * @param null|Config $configuration
     */
    public function setConfiguration(?Config $configuration): void
    {
        $this->configuration = $configuration;
    }


    /**
     * @param Config $domain
     */
    public function setDomain(Config $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @param $domain
     * @return mixed|null
     * @internal
     */
    private function _getDomain($domain) {
        if(!$domain) {
            if(!isset($this->domain['__tasoft_null_domain']))
                $this->domain['__tasoft_null_domain'] = new Config([]);
            return $this->domain['__tasoft_null_domain'];
        }

        if(!isset($this->domain[$domain]))
            $this->domain[$domain] = new Config([]);
        return $this->domain[$domain];
    }

    /**
     * Posts a value into public domain
     *
     * @param $value
     * @param string $name
     * @param string $domain
     */
    protected function postDomainValue($value, string $name, string $domain = "") {
        $dom = $this->_getDomain($domain);
        $dom[$name] = $value;
    }

    /**
     * Fetches a value from public domain
     *
     * @param string $name
     * @param string $domain
     * @return mixed|null
     */
    protected function getDomainValue(string $name, string $domain = "") {
        $dom = $this->_getDomain($domain);
        return $dom[$name] ?? NULL;
    }

    /**
     * Fetches all values inside a domain
     *
     * @param string $domain
     * @return array
     */
    protected function getDomainValues(string $domain = "") {
        /** @var Config $dom */
        $dom = $this->_getDomain($domain);
        return $dom->getValues();
    }

    /**
     * Project's application directory
     *
     * @return string
     */
    protected function getSkylineAppDataDirectory() {
        return $this->getMainCompiler()->getSkylineAppDataDirectory();
    }

    /**
     * @inheritDoc
     */
    abstract public function compile();
}