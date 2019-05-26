<?php /** @noinspection PhpParamsInspection */

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

namespace Skyline\Compiler\Context\Code;


use Generator;
use RecursiveDirectoryIterator;
use Skyline\Compiler\CompilerConfiguration as CC;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Project\Attribute\SearchPathAttribute;
use SplFileInfo;

class SourceCodeManager
{
    /** @var CompilerContext */
    protected $context;


    /** @var array */
    protected $sourceFiles;
    protected $excludedFiles;

    protected $restrictSourcesToProject = false;

    /**
     * SourceCodeManager constructor.
     * @param CompilerContext $context
     */
    public function __construct(CompilerContext $context)
    {
        $this->context = $context;
    }


    /**
     * @return CompilerContext
     */
    public function getContext(): CompilerContext
    {
        return $this->context;
    }

    /**
     * Yields all source files found in declared directories that match the passed regex.
     * NOTE: The first call takes longer to iterate over all source directories collecting the source files.
     *
     * @param string $fileNameRegex If null, yields all source files
     * @param array $searchPaths Define in which search paths to look for files
     * @return Generator
     */
    public function yieldSourceFiles(string $fileNameRegex = NULL, array $searchPaths = NULL) {
        $loadSearchPathIfNeeded = function($path, $name) {
            if(!isset($this->sourceFiles["$name"])) {
                $this->sourceFiles["$name"] = [];

                $iterateOverDirectory = function(RecursiveDirectoryIterator $iterator) use (&$iterateOverDirectory) {
                    /** @var SplFileInfo $item */
                    foreach($iterator as $item) {
                        $file = new SourceFile($item);
                        if($this->shouldIncludeFilename($file)) {
                            if($item->isFile())
                                yield (string)$file => $file;
                            elseif($iterator->hasChildren(true)) {
                                yield from $iterateOverDirectory($iterator->getChildren());
                            }
                        } else {
                            $this->excludedFiles[ (string)$file ] = $file;
                        }
                    }
                };

                $iterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS);

                foreach($iterateOverDirectory($iterator) as $fn => $file) {
                    $this->sourceFiles["$name"]["$fn"] = $file;
                }
            }
        };

        $addSrcDir = function($dirsOrDir, $kind) use (&$sources, $loadSearchPathIfNeeded) {
            $notFound = function($value) {
                trigger_error("Search path $value not found", E_USER_WARNING);
            };

            if(is_array($dirsOrDir)) {
                foreach($dirsOrDir as $ss) {
                    if(is_dir($ss))
                        $loadSearchPathIfNeeded($ss, $kind);
                    else
                        $notFound($ss);
                }
            }
            elseif(is_dir($dirsOrDir))
               $loadSearchPathIfNeeded($dirsOrDir, $kind);
            else
                $notFound($dirsOrDir);
        };


        if(NULL === $searchPaths) {
            $searchPaths = [];

            foreach($this->getDefaultSearchPaths() as $name => $dirsOrDir) {
                if($dirsOrDir)
                    $addSrcDir($dirsOrDir, $name);
                $searchPaths[] = $name;
            }
        } else {
            $defaults = $this->getDefaultSearchPaths();

            foreach($searchPaths as $key => $value) {
                if(is_numeric($key))
                    $key = $value;

                if(isset($defaults["$value"])) {
                    $value = $defaults["$value"];
                }

                $addSrcDir($value, $key);
            }
        }


        foreach($this->sourceFiles as $bank => $files) {
            if(!in_array($bank, $searchPaths))
                continue;

            foreach($files as $fileName => $file) {
                if(NULL == $fileNameRegex || preg_match($fileNameRegex, basename($fileName)))
                    yield $fileName => $file;
            }
        }
    }

    protected function getDefaultSearchPaths(): array {
        return [
            CC::COMPILER_SOURCE_DIRECTORIES => CC::get($this->getContext()->getConfiguration(), CC::COMPILER_SOURCE_DIRECTORIES),
            SearchPathAttribute::SEARCH_PATH_VENDOR => $this->getContext()->getProjectSearchPaths( SearchPathAttribute::SEARCH_PATH_VENDOR ),
            SearchPathAttribute::SEARCH_PATH_CLASSES => $this->getContext()->getProjectSearchPaths( SearchPathAttribute::SEARCH_PATH_CLASSES )
        ];
    }

    /**
     * Decide if a source file should be used for this compilation or not.
     * NOTE: This method is called for every directory and every file.
     * WARNING: Excluding a directory also excludes all its sub files and directories.
     *
     * @param string $filename
     * @return bool
     */
    protected function shouldIncludeFilename(string $filename): bool {
        if($this->restrictSourcesToProject()) {
            $pdir = $this->getContext()->getProject()->getProjectRootDirectory();
            if(stripos($filename, $pdir) !== 0)
                return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function restrictSourcesToProject(): bool
    {
        return $this->restrictSourcesToProject;
    }

    /**
     * @param bool $restrictSourcesToProject
     */
    public function setRestrictSourcesToProject(bool $restrictSourcesToProject): void
    {
        $this->restrictSourcesToProject = $restrictSourcesToProject;
    }
}