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

namespace Skyline\Compiler\Factory;


use Skyline\Compiler\Predef\ConfigurationCompiler;
use Skyline\Compiler\Predef\OrderedConfigurationCompiler;

class ConfigPluginsCompilterFactory extends AbstractExtendedCompilerFactory
{
    protected function getCompilerDescriptions(): array
    {
        return [
            'parameter-config' => [
                self::COMPILER_CLASS_KEY                            => OrderedConfigurationCompiler::class,
                ConfigurationCompiler::INFO_TARGET_FILENAME_KEY     => 'plugins.php',
                ConfigurationCompiler::INFO_PATTERN_KEY             => '/^.*?\.plugins\.php$/i',
                ConfigurationCompiler::INFO_CUSTOM_FILENAME_KEY     => 'plugins.php',
                ConfigurationCompiler::INFO_DEV_FILENAME_KEY        => "plugins.dev.php",
                ConfigurationCompiler::INFO_TEST_FILENAME_KEY       => "plugins.test.php",
                self::COMPILER_DEPENDENCIES_KEY => [
                    'composer-packages-order'
                ]
            ]
        ];
    }
}