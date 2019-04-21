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

namespace Skyline\Compiler\Predef;


use Skyline\Compiler\AbstractCompiler;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Helper\ComposerPackageDependencyCollection;

/**
 * Searches in vendor directories for all composer packages and brings them into the right order, respecting the dependencies
 *
 * @package Skyline\Compiler\Predef
 */
class ComposerPackagesOrderCompiler extends AbstractCompiler
{
    private $jsonCache = [];
    private $files = [];

    private $scanDir = [];

    /**
     * Reads the composer.json file directly or from directory and adds it to cache.
     *
     * @param $fileOrPackage
     * @return mixed|null
     */
    private function getComposer($fileOrPackage) {
        if(isset($this->jsonCache[$fileOrPackage]))
            return $this->jsonCache[$fileOrPackage];

        if($file = $this->files[$fileOrPackage] ?? NULL) {
            return $this->getComposer($file);
        }

        if(is_dir($fileOrPackage))
            $fileOrPackage .= "/composer.json";

        if(is_file($fileOrPackage)) {
            $json = json_decode( file_get_contents($fileOrPackage), true );
            $name = $json["name"];
            $this->jsonCache[$name] = $json;
            $this->files[$name] = $fileOrPackage;
            return $json;
        }
        return NULL;
    }


    /**
     * @inheritDoc
     */
    public function compile(CompilerContext $context)
    {
        $dependencyCollection = new ComposerPackageDependencyCollection();
        $root = $this->getComposer( $this->scanDir );

        $register = function($json, $path) use ($dependencyCollection) {
            $req = array_keys($json["require"] ?? []);
            $dependencyCollection->add($json["name"], $path, $req);
        };

        foreach(($root["repositories"] ?? []) as $rep) {
            if(($rep["type"] ?? NULL) == 'path') {
                $path = realpath($rep["url"]);
                if($path && is_file("$path/composer.json")) {
                    $js = $this->getComposer($path);
                    $register($js, $path);
                }
            }
        }


        foreach($context->getSourceCodeManager()->yieldSourceFiles("/^composer\.json$/i") as $file) {
            $js = $this->getComposer((string)$file);
            $register($js, (string) $file);
        }

        $register($root, $this->scanDir);

        $ordered = $dependencyCollection->getOrderedElements();

        print_r($ordered);
    }

    /**
     * ComposerPackagesOrderCompiler constructor.
     * @param string $compilerID
     * @param string|NULL $scanDir
     * @param bool $requireDev
     */
    public function __construct(string $compilerID, string $scanDir = NULL)
    {
        parent::__construct($compilerID);
        $this->scanDir = is_dir($scanDir) ? $scanDir : getcwd();
    }
}