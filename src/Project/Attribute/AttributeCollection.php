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

/**
 * Class AttributeCollection
 * @package Skyline\Compiler\Project\Attribute
 */
class AttributeCollection extends Attribute implements AttributeCollectionInterface
{
    private $attributes = [];

    /**
     * @return AttributeInterface[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return AttributeInterface|null
     */
    public function getAttribute(string $name): ?AttributeInterface {
        return $this->attributes[$name] ?? NULL;
    }

    /**
     * Returns true, if an attribute exists
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool {
        return isset($this->attributes[$name]);
    }

    /**
     * Adds an attribute to the list
     * Existing attributes are replaced.
     *
     * @param AttributeInterface $attribute
     */
    public function addAttribute(AttributeInterface $attribute) {
        if(isset($this->attributes[$attribute->getName()]))
            trigger_error("Attribute " . $attribute->getName() . " already exists", E_USER_NOTICE);

        $this->attributes[ $attribute->getName() ] = $attribute;
    }

    /**
     * Set new attributes
     * @param array $attributes
     */
    public function setAttributes(array $attributes) {
        foreach($attributes as $key => $attribute) {
            if(!($attribute instanceof AttributeInterface)) {
                if(is_array($attribute)) {
                    $attr = new AttributeCollection($key);
                    $attr->setAttributes($attribute);
                    $attribute = $attr;
                } else {
                    $attribute = new Attribute($key, $attribute);
                }
            }

            $this->attributes[ $attribute->getName() ] = $attribute;
        }
    }

    /**
     * Removes an attribute from collection
     *
     * @param string $attributeName
     */
    public function removeAttribute(string $attributeName) {
        if(isset($this->attributes[$attributeName]))
            unset($this->attributes[$attributeName]);
    }
}