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
use Skyline\Kernel\Bootstrap;

class CompilerContextParameterCollection extends AttributeCollection
{
    private $denyModifications = false;
    /**
     * Denies further modifications
     */
    final public function denyModifications() {
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
     * Internal method to check if the class allows modifications
     * @internal
     */
    private function _checkModify() {
        if($this->canModify() == false)
            throw new CompilerException("Can not modify context parameters anymore");
    }

    /**
     * Puts value into attribute
     * @param $attrName
     * @param null $value
     * @internal
     */
    private function _put($attrName, $value = NULL) {
        $this->_checkModify();

        if($value === NULL)
            $this->removeAttribute($attrName);
        elseif($this->hasAttribute($attrName) && method_exists($attr = $this->getAttribute($attrName), "setValue")) {
            /** @var Attribute $attr */
            $attr->setValue($value);
        } else {
            $this->addAttribute(new Attribute($attrName, $value));
        }
    }

    /**
     * Returns true if the context parameters can be modified.
     * @return bool
     */
    final public function canModify(): bool
    {
        return !$this->denyModifications;
    }

    /**
     * @inheritDoc
     */
    public function setAttributes(array $attributes)
    {
        $this->_checkModify();
        parent::setAttributes($attributes);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        $this->_checkModify();
        parent::setValue($value);
    }

    /**
     * @inheritDoc
     */
    public function removeAttribute(string $attributeName)
    {
        $this->_checkModify();
        parent::removeAttribute($attributeName);
    }

    /**
     * @inheritDoc
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        $this->_checkModify();
        parent::addAttribute($attribute);
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
     * Sets the class of the main application
     * @param string $class
     */
    public function setApplicationClass(string $class): void {
        $this->_put("applicationClass", $class);
    }

    /**
     * @return string
     */
    public function getApplicationClass(): string {
        return $this->_fetch("applicationClass", "Skyline\Kernel\Application");
    }

    /**
     * Declare all compiler factoriy class names that should be performed.
     *
     * @param array $factories
     */
    public function setCompilerFactories(array $factories): void {
        $this->_put("factories", $factories);
    }

    /**
     * @return array
     */
    public function getCompilerFactories(): array {
        return $this->_fetch("factories", []);
    }

    /**
     * Declare the class to bootstrap your Skyline CMS Application
     *
     * @param string $class
     */
    public function setBootstrapClass(string $class): void {
        $this->_put("bootstrapClass", $class);
    }

    /**
     * @return string
     */
    public function getBootstrapClass(): string {
        return $this->_fetch("bootstrapClass", Bootstrap::class);
    }
}