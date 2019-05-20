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


use Skyline\Compiler\CompilerConfiguration as CC;
use Skyline\Compiler\Predef\ComposerPackagesOrderCompiler;
use Skyline\Compiler\Predef\CreateDirectoriesIfNotExistCompiler;
use Skyline\Compiler\Predef\CreatePublicDirectoryCompiler;

class BasicCompilersFactory extends AbstractExtendedCompilerFactory
{
    protected function getCompilerDescriptions(): array
    {
        return [
            'create-directories' => [
                self::COMPILER_CLASS_KEY => CreateDirectoriesIfNotExistCompiler::class,
                self::COMPILER_ARGUMENTS_KEY => [
                    'directoryNames' => [
                        CC::get([], CC::SKYLINE_DIR_CLASSES),
                        CC::get([], CC::SKYLINE_DIR_COMPILED),
                        CC::get([], CC::SKYLINE_DIR_CONFIG),
                        CC::get([], CC::SKYLINE_DIR_MODULES),
                        CC::get([], CC::SKYLINE_DIR_LOGS)
                    ]
                ]
            ],
            'create-public-directory' => CreatePublicDirectoryCompiler::class,
            'composer-packages-order' => [
                self::COMPILER_CLASS_KEY => ComposerPackagesOrderCompiler::class,
                self::COMPILER_ARGUMENTS_KEY => [
                    'rootComposerDirectory' => './'
                ],
                self::COMPILER_DEPENDENCIES_KEY => [
                    'create-directories'
                ]
            ]
        ];
    }
}