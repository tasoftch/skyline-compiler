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

use Skyline\Compiler\CompilerInterface;
use TASoft\Collection\DependencyCollection;

/**
 * Uses a complex list to create compilers, by passing compiler descriptions into the constructors
 * After that the compiler id and dependencies are obtained from the description.
 * NOTE: The compiler's getCompilerID and getDependsOnCompilerIDs() methods are not called anymore!
 *
 * @package Skyline\Compiler\Factory
 */
abstract class AbstractExtendedCompilerFactory extends AbstractBasicCompilerFactory
{
    const COMPILER_ID_KEY = 'id';
    const COMPILER_CLASS_KEY = 'class';
    const COMPILER_ARGUMENTS_KEY = 'arguments';
    const COMPILER_DEPENDENCIES_KEY = 'dependencies';


    /**
     * @inheritDoc
     */
    public function registerCompilerInstances(DependencyCollection $dependencyCollection)
    {
        foreach($this->getCompilerDescriptions() as $className => $description) {
            $compiler = $this->instantiateCompiler($className, $description);

            $id = $description[ self::COMPILER_ID_KEY ];
            $deps = $description[ self::COMPILER_DEPENDENCIES_KEY ] ?? [];

            $dependencyCollection->add($id, $compiler, $deps);
        }
    }

    /**
     * Method is called with the key and value pair of compiler description. See getCompilerDescriptions to learn how it is formatted.
     *
     * @param string $className
     * @param $description
     * @return CompilerInterface
     * @see AbstractExtendedCompilerFactory::getCompilerDescriptions()
     */
    protected function instantiateCompiler(string &$className, &$description): CompilerInterface {
        if(is_array($description)) {
            $class = $description[ self::COMPILER_CLASS_KEY ] ?? $className;
            if(!isset($description[ self::COMPILER_ID_KEY ]))
                $description[ self::COMPILER_ID_KEY ] = $className;

            $id = $description[ self::COMPILER_ID_KEY ];
            if($args = $description[ self::COMPILER_ARGUMENTS_KEY ] ?? NULL) {
                return new $class($id, ...array_values($args));
            } else {
                return new $class($id, $description);
            }
        } else {
            // $className is compiler id and $description is class name
            return new $description($className);
        }
    }


    /**
     * Returns the compiler descriptions
     * @return array
     * @example [
     *      'My-Compiler-ID' => My\Compiler::class,
     *      My\Compiler::class => [
     *          self::COMPILER_ID_KEY => 'My-Compiler-ID'       // required if array key is a class name
     *          self::COMPILER_CLASS_KEY => My\Compiler::class  // required if array key is the id
     *          self::COMPILER_ARGUMENTS_KEY => [1, 2, 3]       // Optional; if not set, pass everything as compiler constructor's 1st argument
     *          self::COMPILER_DEPENDENCIES_KEY => [id-of-compiler1, id-of-compiler2, ...] // Optional
     *      ]
     * ]
     * NOTE: The ID is always passed into the constructor, you don't need to repeat it in COMPILER_ARGUMENTS_KEY!
     */
    abstract protected function getCompilerDescriptions(): array;
}