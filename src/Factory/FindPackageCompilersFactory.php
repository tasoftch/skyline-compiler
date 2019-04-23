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

use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Project\Attribute\SearchPathAttribute;
use TASoft\Collection\DependencyCollection;

/**
 * Use this compiler to search files in your project containing same data structure as returned from getCompilerDescriptions
 * Then load this data by default extended factory behaviour.
 *
 * @package Skyline\Compiler\Factory
 * @see AbstractExtendedCompilerFactory::getCompilerDescriptions()
 */
class FindPackageCompilersFactory extends AbstractExtendedCompilerFactory
{
    /** @var string[] */
    private $searchPaths;
    /** @var string */
    private $compilerFilePattern;

    /**
     * @inheritDoc
     */
    public function registerCompilerInstances(DependencyCollection $dependencyCollection, CompilerContext $context)
    {

    }

    /**
     * @param string[] $searchPaths
     */
    public function setSearchPaths(array $searchPaths): void
    {
        $this->searchPaths = $searchPaths;
    }

    /**
     * @param string $compilerFilePattern
     */
    public function setCompilerFilePattern(string $compilerFilePattern): void
    {
        $this->compilerFilePattern = $compilerFilePattern;
    }

    /**
     * Do not return nothing because this method won't be called.
     */
    protected function getCompilerDescriptions(): array
    {
        return [];
    }

    /**
     * FindPackageCompilersFactory constructor.
     * @param array|NULL $searchPaths
     * @param string $compilerFilePattern
     */
    public function __construct(array $searchPaths = NULL, string $compilerFilePattern = '/^compiler\.cfg\.php$/i')
    {
        $this->searchPaths = $searchPaths === NULL ? [SearchPathAttribute::SEARCH_PATH_VENDOR] : $searchPaths;
        $this->compilerFilePattern = $compilerFilePattern;
    }
}