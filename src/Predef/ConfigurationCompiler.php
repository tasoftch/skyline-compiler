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
use Skyline\Compiler\AbstractCompiler;
use Skyline\Compiler\CompilerConfiguration as CC;
use Skyline\Compiler\CompilerConfiguration;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Context\Logger\LoggerInterface;
use Skyline\Compiler\Project\Attribute\SearchPathAttribute;
use TASoft\Config\Compiler\BadSourceException;
use TASoft\Config\Compiler\Source\FileSource;
use TASoft\Config\Compiler\Source\SourceContainer;
use TASoft\Config\Compiler\StandardFactoryCompiler;
use Traversable;

class ConfigurationCompiler extends AbstractCompiler
{
    private $info;

    public $ignoreModuleConfiguration = true;

    const INFO_TARGET_FILENAME_KEY = 'target';
    const INFO_PATTERN_KEY = 'pattern';
    const INFO_CUSTOM_FILENAME_KEY = 'default';
    const INFO_DEV_FILENAME_KEY = 'dev';
    const INFO_TEST_FILENAME_KEY = 'test';
    const INFO_EXCLUDE_PATTERN = 'exclude';


    public function compile(CompilerContext $context)
    {
        $sourceContainer = new SourceContainer();

        $addFile = function($file, $skipCheck = false) use ($sourceContainer, $context) {
            if(is_file($file)) {
                if($this->ignoreModuleConfiguration && $context->getSourceCodeManager()->isFilePartOfModule($file)) {
                    $context->getLogger()->logText("Source %s ignored: %s", LoggerInterface::VERBOSITY_VERY_VERBOSE, NULL, $this->getCompilerID(), $file);
                    return;
                }

                if(!$skipCheck && in_array(basename($file), [
                        $this->info[ static::INFO_CUSTOM_FILENAME_KEY ] ?? NULL,
                        $this->info[ static::INFO_DEV_FILENAME_KEY ] ?? NULL,
                        $this->info[ static::INFO_TEST_FILENAME_KEY ] ?? NULL
                    ])) {
                    $context->getLogger()->logWarning("Source %s conflicts with default", NULL, $file);
                    return;
                }
                $context->getLogger()->logText("Source for %s found: %s", LoggerInterface::VERBOSITY_VERY_VERBOSE, NULL, $this->getCompilerID(), $file);

                try {
                    $sourceContainer->addSource(new FileSource($file));
                } catch (BadSourceException $e) {
                    $context->getLogger()->logWarning("Source Error: %s", NULL, $e->getMessage());
                }
            } else {
                $context->getLogger()->logWarning("Source not found: %s", NULL, $file);
            }
        };

        foreach($this->yieldConfigurationFiles($context) as $file) {
            $file = (string) $file;
            if(($ptrn = $this->info[ static::INFO_EXCLUDE_PATTERN ] ?? "") && preg_match($ptrn, $file))
                continue;
            $addFile($file);
        }

        if($defaultFile = $this->info[ static::INFO_CUSTOM_FILENAME_KEY ] ?? NULL) {
            foreach($context->getProjectSearchPaths(SearchPathAttribute::SEARCH_PATH_USER_CONFIG) as $configPath) {
                if(is_file($f = "$configPath/$defaultFile")) {
                    $context->getLogger()->logText("DEFAULT for %s: %s", LoggerInterface::VERBOSITY_VERY_VERBOSE, NULL, $this->getCompilerID(), $f);
                    $addFile($f, true);
                    break;
                }
            }
        }

        if(CC::get($context->getConfiguration(), CC::COMPILER_DEBUG) && ($defaultFile = $this->info[ static::INFO_DEV_FILENAME_KEY ] ?? NULL)) {
            foreach($context->getProjectSearchPaths(SearchPathAttribute::SEARCH_PATH_USER_CONFIG) as $configPath) {
                if(is_file($f = "$configPath/$defaultFile")) {
                    $context->getLogger()->logText("DEV for %s: %s", LoggerInterface::VERBOSITY_VERY_VERBOSE, NULL, $this->getCompilerID(), $f);
                    $addFile($f, true);
                    break;
                }
            }
        }

        if(CC::get($context->getConfiguration(), CC::COMPILER_TEST) && ($defaultFile = $this->info[ static::INFO_TEST_FILENAME_KEY ] ?? NULL)) {
            foreach($context->getProjectSearchPaths(SearchPathAttribute::SEARCH_PATH_USER_CONFIG) as $configPath) {
                if(is_file($f = "$configPath/$defaultFile")) {
                    $context->getLogger()->logText("TEST for %s: %s", LoggerInterface::VERBOSITY_VERY_VERBOSE, NULL, $this->getCompilerID(), $f);
                    $addFile($f, true);
                    break;
                }
            }
        }

        $target = $this->info[ static::INFO_TARGET_FILENAME_KEY ];
        $cdir = $context->getSkylineAppDirectory(CompilerConfiguration::SKYLINE_DIR_COMPILED);

        $this->compileConfiguration($sourceContainer, "$cdir/$target", $context);
        $context->getValueCache()->postValue($this->info[ static::INFO_TARGET_FILENAME_KEY ], $this->getCompilerID());
    }

    /**
     * Compile the configuration now
     *
     * @param Traversable $source
     * @param string $target
     * @param CompilerContext $context
     */
    protected function compileConfiguration(Traversable $source, string $target, CompilerContext $context) {
        $compiler = new StandardFactoryCompiler($target);
        $compiler->setSource($source);
        $compiler->compile();
    }

    /**
     * Override to adjust configuration files and order
     *
     * @param CompilerContext $context
     * @return Generator
     */
    protected function yieldConfigurationFiles(CompilerContext $context): Generator{
        $configDirs = [];
        foreach($context->getProjectSearchPaths(SearchPathAttribute::SEARCH_PATH_CONFIG) as $configDir) {
            $configDirs[] = (string)$configDir;
        }

        $pattern = $this->info[ static::INFO_PATTERN_KEY ];
        $defaultFile = $this->info[ static::INFO_CUSTOM_FILENAME_KEY ] ?? NULL;

        $rp = $context->getSourceCodeManager()->restrictSourcesToProject();
        $context->getSourceCodeManager()->setRestrictSourcesToProject(true);

        foreach($context->getSourceCodeManager()->yieldSourceFiles($pattern, $configDirs) as $fileName => $file) {
            if(basename($fileName) == $defaultFile)
                continue;

            yield $fileName => $file;
        }
        $context->getSourceCodeManager()->setRestrictSourcesToProject($rp);
    }

    /**
     * ConfigurationCompiler constructor.
     * @param string $compilerID
     * @param $info
     */
    public function __construct(string $compilerID, $info)
    {
        parent::__construct($compilerID);
        $this->info = $info;
    }
}