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

namespace Skyline\Compiler\Context\Code;


use Generator;
use RecursiveDirectoryIterator;
use Skyline\Compiler\CompilerConfiguration as CC;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Project\Attribute\AttributeInterface;
use Skyline\Compiler\Project\Attribute\SearchPathAttribute;
use Skyline\Compiler\Project\Attribute\SearchPathCollection;

class SourceCodeManager
{
    /** @var CompilerContext */
    protected $context;


    /** @var array */
    protected $sourceFiles;
    protected $excludedFiles;

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
     * @return Generator
     */
    public function yieldSourceFiles(string $fileNameRegex = NULL) {
        if(NULL === $this->sourceFiles) {
            $this->sourceFiles = [];

            $sources = [];
            $addSrcDir = function($dirsOrDir) use (&$sources) {
                if(is_array($dirsOrDir)) {
                    foreach($dirsOrDir as $ss) {
                        if(is_dir($ss))
                            $sources[] = $ss;
                    }
                }
                elseif(is_dir($dirsOrDir))
                    $sources[] = $dirsOrDir;
            };

            //  Fetch source directories from configuration
            if($src = CC::get($this->getContext()->getConfiguration(), CC::COMPILER_SOURCE_DIRECTORIES)) {
                $addSrcDir($src);
            }

            /** @var SearchPathCollection $searchPaths */
            $searchPaths = $this->getContext()->getProject()->getAttribute( AttributeInterface::SEARCH_PATHS_ATTR_NAME );
            if($searchPaths instanceof SearchPathCollection) {
                if($dirs = $searchPaths->getSearchPaths( SearchPathAttribute::SEARCH_PATH_VENDOR )) {
                    $addSrcDir($dirs);
                }
                if($dirs = $searchPaths->getSearchPaths( SearchPathAttribute::SEARCH_PATH_CLASSES )) {
                    $addSrcDir($dirs);
                }
            }


            $iterateOverDirectory = function(RecursiveDirectoryIterator $iterator) use (&$iterateOverDirectory) {
                /** @var \SplFileInfo $item */
                foreach($iterator as $item) {
                    $file = new SourceFile($item);
                    if($this->shouldIncludeFilename($file)) {
                        if($item->isFile())
                            $this->sourceFiles[ (string)$file ] = $file;
                        elseif($iterator->hasChildren(true)) {
                            /** @noinspection PhpParamsInspection */
                            $iterateOverDirectory($iterator->getChildren());
                        }
                    } else {
                        $this->excludedFiles[ (string)$file ] = $file;
                    }
                }
            };


            foreach($sources as $source) {
                $iterator = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
                $iterateOverDirectory($iterator);
            }
        }

        foreach($this->sourceFiles as $fileName => $file) {
            if(NULL == $fileNameRegex || preg_match($fileNameRegex, $file))
                yield $fileName => $file;
        }
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
        return true;
    }
}