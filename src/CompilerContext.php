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

namespace Skyline\Compiler;

use Skyline\Compiler\CompilerConfiguration as CC;
use Skyline\Compiler\Context\Code\SourceCodeManager;
use Skyline\Compiler\Context\FileCache\FileCacheInterface;
use Skyline\Compiler\Context\FileCache\LocalFileCache;
use Skyline\Compiler\Context\Logger\LoggerInterface;
use Skyline\Compiler\Context\Logger\OutputLogger;
use Skyline\Compiler\Context\ValueCache\ValueCache;
use Skyline\Compiler\Context\ValueCache\ValueCacheInterface;
use Skyline\Compiler\Exception\CompilerException;
use Skyline\Compiler\Project\Attribute\AttributeInterface;
use Skyline\Compiler\Project\Attribute\SearchPathCollection;
use Skyline\Compiler\Project\ProjectInterface;
use Skyline\Kernel\Service\Error\AbstractErrorHandlerService;
use TASoft\Collection\DependencyCollection;
use TASoft\Config\Config;
use Throwable;

class CompilerContext
{
    /** @var ProjectInterface */
    private $project;

    /** @var LoggerInterface */
    private $logger;

    /** @var ValueCacheInterface */
    private $valueCache;

    /** @var FileCacheInterface */
    private $fileCache;

    /** @var Config */
    private $configuration;

    /** @var SourceCodeManager */
    private $sourceCodeManager;

    /** @var array  */
    private $compilers = [];

    /**
     * CompilerContext constructor.
     * @param ProjectInterface $project
     */
    public function __construct(ProjectInterface $project)
    {
        $this->project = $project;
    }

    /**
     * Makes a compiler ready to execute
     *
     * @param CompilerInterface|CompilerFactoryInterface $compiler
     */
    public function addCompiler($compiler) {
        if($compiler instanceof CompilerFactoryInterface || $compiler instanceof CompilerInterface)
            $this->compilers[] = $compiler;
        else
            throw new CompilerException("Can only add objects that implement CompilerInterface or CompilerFactoryInterface to context");
    }

    /**
     * Removes a compiler from list if exists
     *
     * @param $compiler
     */
    public function removeCompiler($compiler) {
        if(($idx = array_search($compiler, $this->compilers)) !== false)
            unset($this->compilers[$idx]);
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        if(!$this->logger)
            $this->logger = new OutputLogger();
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return FileCacheInterface
     */
    public function getFileCache(): FileCacheInterface
    {
        if(!$this->fileCache) {
            $fn = CC::get($this->getConfiguration(), CC::COMPILER_CACHE_FILENAME);
            $this->fileCache = new LocalFileCache($fn);
        }
        return $this->fileCache;
    }

    /**
     * @param FileCacheInterface $fileCache
     */
    public function setFileCache(FileCacheInterface $fileCache): void
    {
        $this->fileCache = $fileCache;
    }

    /**
     * @return ValueCacheInterface
     */
    public function getValueCache(): ValueCacheInterface
    {
        if(!$this->valueCache)
            $this->valueCache = new ValueCache();
        return $this->valueCache;
    }

    /**
     * @param ValueCacheInterface $valueCache
     */
    public function setValueCache(ValueCacheInterface $valueCache): void
    {
        $this->valueCache = $valueCache;
    }

    /**
     * Returns the project.
     * While compiling, it never returns NULL!
     *
     * @return ProjectInterface|null
     */
    public function getProject(): ?ProjectInterface
    {
        return $this->project;
    }

    /**
     * @return Config
     */
    public function getConfiguration(): Config
    {
        if(!$this->configuration)
            $this->configuration = new Config();
        return $this->configuration;
    }

    /**
     * @param Config $configuration
     */
    public function setConfiguration(Config $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * Returns the Skyline CMS Application data directory (absolute)
     * @return string
     */
    public function getSkylineAppDataDirectory() {
        $skylineTarget = CC::get($this->getConfiguration(), CC::SKYLINE_APP_DATA_DIR);
        $projDir = $this->getProject()->getProjectRootDirectory();
        return "$projDir/$skylineTarget/";
    }

    /**
     * Returns the Skyline CMS Application data directory (absolute)
     * @return string
     */
    public function getSkylinePublicDataDirectory() {
        $skylineTarget = CC::get($this->getConfiguration(), CC::SKYLINE_PUBLIC_DATA_DIR);
        $projDir = $this->getProject()->getProjectRootDirectory();
        return "$projDir/$skylineTarget/";
    }

    /**
     *  Returns the required Skyline CMS Application data sub directory
     *
     * @param string $dirName
     * @return string
     * @see CompilerConfiguration::SKYLINE_DIR_* constants
     */
    public function getSkylineAppDirectory(string $dirName) {
        $name = CC::get([], $dirName);
        if($name) {
            return $this->getSkylineAppDataDirectory() . "/$name";
        }
        return NULL;
    }

    /**
     * Obtaining search paths from project
     *
     * @param string $name
     * @return array
     */
    public function getProjectSearchPaths(string $name): array {
        $srcPaths = $this->getProject()->getAttribute(AttributeInterface::SEARCH_PATHS_ATTR_NAME);
        if($srcPaths instanceof SearchPathCollection) {
            return $srcPaths->getSearchPaths($name) ?? [];
        }
        return [];
    }

    /**
     * Resolves the compilers against their dependencies and creates an iterator
     *
     * @return array
     * @internal
     */
    private function getOrganizedCompilersIterator() {
        $depCollection = new DependencyCollection(false);
        $depCollection->setAcceptsDuplicates(false);

        foreach($this->compilers as $compiler) {
            if($compiler instanceof CompilerInterface) {
                $id = $compiler->getCompilerID();
                $deps = $compiler->getDependsOnCompilerIDs();
                if($deps)
                    $depCollection->add($id, $compiler, $deps);
                else
                    $depCollection->add($id, $compiler);
            }
            elseif($compiler instanceof CompilerFactoryInterface) {
                $compiler->registerCompilerInstances($depCollection, $this);
            }
        }

        return $depCollection->getOrderedElements();
    }

    /**
     * Main compile command.
     * Call this function to resolve any compiler factories and dependents.
     * Then every compiler is called to compile its stuff
     *
     * The compilation does not throw any error or notification. Everything is redirected to the logger.
     * To handle errors, use a different logger.
     */
    public function compile() {
        if(!($project = $this->getProject())) {
            $project = CC::get($this->getConfiguration(), CC::COMPILER_PROJECT);
            if(!$project)
                throw new CompilerException("Compilation without project settings is not possible");
        }

        $this->project = $project;

        try {
            set_error_handler(function($code, $msg, $file, $line) {
                switch(AbstractErrorHandlerService::detectErrorLevel($code)) {
                    case AbstractErrorHandlerService::NOTICE_ERROR_LEVEL: return $this->getLogger()->logNotice($msg, [$file, $line]);
                    case AbstractErrorHandlerService::DEPRECATED_ERROR_LEVEL:
                    case AbstractErrorHandlerService::WARNING_ERROR_LEVEL: return $this->getLogger()->logWarning($msg, [$file, $line]);
                    default: return $this->getLogger()->logError($msg, [$file, $line]);
                }
            });



            /** @var CompilerInterface $compiler */
            foreach($this->getOrganizedCompilersIterator() as $compiler) {
                $compiler->compile($this);
            }
        } catch (Throwable $throwable) {
            $this->getLogger()->logException($throwable);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @return SourceCodeManager
     */
    public function getSourceCodeManager(): SourceCodeManager
    {
        if(!$this->sourceCodeManager)
            $this->sourceCodeManager = new SourceCodeManager($this);
        return $this->sourceCodeManager;
    }

    /**
     * @param SourceCodeManager $sourceCodeManager
     */
    public function setSourceCodeManager(SourceCodeManager $sourceCodeManager): void
    {
        $this->sourceCodeManager = $sourceCodeManager;
    }
}