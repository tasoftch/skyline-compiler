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

namespace Skyline\Compiler\Project\Attribute;


use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Exception\CompilerException;

class CompilerContextParameterCollection extends AttributeCollection
{
    private $denyModifications = false;
    /**
     * Denies further modifications
     */
    public function denyModifications() {
        $this->denyModifications = true;
    }

    /**
     * Fetches value from attributes
     * @param $attrName
     * @param null $default
     * @return mixed|null
     * @internal
     */
    private function _fetch($attrName, $default = NULL) {
        return $this->hasAttribute($attrName) ? $this->getAttribute($attrName)->getValue() : $default;
    }

    /**
     * Puts value into attribute
     * @param $attrName
     * @param null $value
     * @internal
     */
    private function _put($attrName, $value = NULL) {
        if($this->denyModifications)
            throw new CompilerException("Can not modify context parameters anymore");

        if($value === NULL)
            $this->removeAttribute($attrName);
        else {
            $this->addAttribute(new Attribute($attrName, $value));
        }
    }

    /**
     * @return string
     */
    public function getContextClass(): string
    {
        return $this->_fetch("contextClass", CompilerContext::class);
    }

    /**
     * @param string $contextClass
     */
    public function setContextClass(string $contextClass): void
    {
        $this->_put("contextClass", $contextClass);
    }

    /**
     * Returns true if the context parameters can be modified.
     * @return bool
     */
    public function canModify(): bool
    {
        return !$this->denyModifications;
    }
}