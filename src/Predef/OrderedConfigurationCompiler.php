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


use Generator;
use Skyline\Compiler\CompilerContext;

/**
 * Orders all configuration files against their composer packages dependencies
 *
 * @package Skyline\Compiler\Predef
 */
class OrderedConfigurationCompiler extends ConfigurationCompiler
{
    protected function yieldConfigurationFiles(CompilerContext $context): Generator
    {
        $files = [];
        $packages = $context->getValueCache()->fetchValue(ComposerPackagesOrderCompiler::CACHE_PACKAGES_NAME);

        foreach(parent::yieldConfigurationFiles($context) as $file) {
            $pkg = $this->findComposerPackageName($file);
            $idx = array_search($pkg, array_keys($packages));
            if($idx === false)
                $idx = PHP_INT_MAX;
            $files[$idx][] = $file;
        }

        ksort($files);

        foreach($files as $idx => $all) {
            foreach($all as $file)
                yield $file;
        }
    }

    protected function findComposerPackageName($file) {
        while(strlen($file = dirname($file)) > 2) {
            if(is_file("$file/composer.json")) {
                return json_decode( file_get_contents("$file/composer.json"), true )["name"] ?? NULL;
            }
        }
        return NULL;
    }
}