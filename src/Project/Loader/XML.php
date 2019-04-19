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


use Generator;
use Skyline\Compiler\Exception\ProjectLoaderException;
use SimpleXMLElement;
use Skyline\Compiler\Project\Attribute\Attribute;
use Skyline\Compiler\Project\Attribute\FilterAttribute;
use Skyline\Compiler\Project\Attribute\FilterConditionAttribute;

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
     * @inheritDoc
     */
    protected function yieldAttributes(): Generator
    {
        if($attributes = $this->XML->attributes->attr) {
            foreach($attributes as $attr) {
                $name = (string)$attr["name"];
                $value = self::getXMLElementValue($attr);

                yield $name => $value;
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function yieldSearchPaths(): Generator
    {
        if($searchPaths = $this->XML->searchPaths->dir) {
            foreach($searchPaths as $path) {
                $type = (string)$path["type"];
                $value = (string)$path;

                yield $type => $value;
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function yieldCrossOriginResourceSharingHosts(): Generator
    {
        if($hosts = $this->XML->CORS->host) {

            foreach($hosts as $host) {
                $name = (string)$host["name"];
                $accepts = [];
                foreach($host->accepts as $acc) {
                    $accepts[] = (string) $acc;
                }

                yield new Attribute($name, $accepts);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function yieldWhitelistAccess(): Generator
    {
        if($whitelist = $this->XML->whitelist->ip) {
            foreach ($whitelist as $white) {
                yield (string)$white;
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function yieldFilters(): Generator
    {
        if($filterList = $this->XML->filter) {
            $filters = [];
            /** @var SimpleXMLElement $filter */
            foreach ($filterList as $filter) {
                $action = "";
                if($redir = $filter["redirect"] ?? NULL)
                    $action = "header(\"Location: $redir\")";

                if(!$action) {
                    trigger_error("Filter does not provide a known action", E_USER_NOTICE);
                    continue;
                }

                $filters[] = $FILTER = new FilterAttribute('redirect', $action);
                $conds = [];

                $concat = " || ";
                if(strtolower($filter["operator"]) == 'and')
                    $concat = " && ";

                $FILTER->setConditionConcat($concat);

                foreach ($filter->children() as $child) {
                    $bank = $child["bank"] ?? "_SERVER";
                    $name = $child->getName();


                    $cond = new FilterConditionAttribute($name, (string) $child, $bank);

                    $mods = explode(" ", $child["mode"]);
                    $modifiers = 0;
                    foreach($mods as $mod) {
                        if($mod) {
                            switch (strtolower($mod)) {
                                case "not":
                                    $modifiers |= FilterConditionAttribute::NOT_MODIFIER;
                                    break;
                                default:
                                    trigger_error("Unknown modifier $mod", E_USER_NOTICE);
                            }
                        }
                    }

                    $cond->setModifier($modifiers);
                    $conds[] = $cond;
                }

                $FILTER->setConditions($conds);
                yield $FILTER;
            }
        }
    }


    /**
     * Unpacks an XML element into a value <... type="string|int|bool|float|list">....</...>
     * While the type list expects children like <item key="a key">...</item>
     *
     * @param \SimpleXMLElement $element
     * @return bool|float|int|string|array
     */
    public static function getXMLElementValue(SimpleXMLElement $element, string $forceType = NULL, bool $forceAll = false) {
        $normalize = function(SimpleXMLElement $element, $forced) use (&$normalize, $forceAll) {
            $type = (string) ($element['type'] ?? 'string');
            if($forced)
                $type = $forced;

            if($type == 'list') {
                $list = [];
                foreach($element->children() as $child) {
                    $key = (string)$child["key"];
                    $value = $normalize($child, $forceAll ? $forced : "");

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

        return $normalize($element, $forceType);
    }
}