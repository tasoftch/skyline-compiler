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


use Skyline\Compiler\CompilerFactoryInterface;
use Skyline\Compiler\CompilerInterface;
use TASoft\Collection\DependencyCollection;

/**
 * Uses a list of class names to create the compilers
 *
 * @package Skyline\Compiler\Factory
 */
abstract class AbstractBasicCompilerFactory implements CompilerFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function registerCompilerInstances(DependencyCollection $dependencyCollection)
    {
        foreach($this->getCompilerDescriptions() as $key => $class) {
            /** @var CompilerInterface $compiler */
            $compiler = new $class($key);
            $dependencyCollection->add($key, $compiler, $compiler->getDependsOnCompilerIDs());
        }
    }

    /**
     * Returns the compiler classes and ids
     * @return array
     * @example [
     *      'My-Compiler-ID' => My\Compiler::class  // Passes the array's key into the compiler's constructor
     * ]
     */
    abstract protected function getCompilerDescriptions(): array;
}