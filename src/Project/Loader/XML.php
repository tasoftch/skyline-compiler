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

namespace Skyline\Compiler\Project\Loader;


use Skyline\Compiler\Exception\ProjectLoaderException;
use SimpleXMLElement;

class XML extends AbstractFileLoader
{
    private $XML;

    /**
     * @inheritDoc
     */
    protected function getProjectRootDirectory(): string
    {
        return (string) $this->XML->directory;
    }

    /**
     * @inheritDoc
     */
    protected function getProjectInstanceClass(): string
    {
        return (string) $this->XML['class'];
    }

    protected function getConstructorArguments(): ?array
    {
        $arguments = [];
        if($args = $this->XML->argument) {
            foreach($args as $arg) {
                $arguments[] = $this->getXMLElementValue($arg);
            }
        }
        return $arguments;
    }


    /**
     * @inheritDoc
     */
    protected function loadDidBegin()
    {
        libxml_clear_errors();
        $xml = @simplexml_load_file($this->getFilename());
        $error = libxml_get_last_error();
        if($error) {
            $e = new ProjectLoaderException($error->message);
            $e->setLoader($this);
            throw $e;
        }
        $this->XML = $xml;
    }



    /**
     * Unpacks an XML element into a value <... type="string|int|bool|float|list">....</...>
     * While the type list expects children like <item key="a key">...</item>
     *
     * @param \SimpleXMLElement $element
     * @return bool|float|int|string|array
     */
    public static function getXMLElementValue(SimpleXMLElement $element) {
        $normalize = function(SimpleXMLElement $element) use (&$normalize) {
            $type = (string) ($element['type'] ?? 'string');
            if($type == 'list') {
                $list = [];
                foreach($element->children() as $child) {
                    $key = (string)$child["key"];
                    $value = $normalize($child);

                    if($key)
                        $list[$key] = $value;
                    else
                        $list[] = $value;
                }
                return $list;
            }

            $value = (string) $element;
            switch ($type) {
                case 'int': return (int) $value;
                case 'float': return (float) $value;
                case 'bool': return (bool) $value;
                default: break;
            }
            return $value;
        };

        return $normalize($element);
    }
}