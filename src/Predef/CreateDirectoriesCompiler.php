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


use Skyline\Compiler\AbstractCompiler;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Context\Logger\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Creates defined directories. If a directory exists, it will be removed.
 *
 * @package Skyline\Compiler\Predef
 */
class CreateDirectoriesCompiler extends AbstractCompiler
{
    /** @var string[] */
    private $directoryNames;

    /** @var Filesystem */
    private $fileSystem;

    /**
     * CreateDirectoriesCompiler constructor.
     * @param string[] $directoryNames
     */
    public function __construct(string $compilerID, array $directoryNames)
    {
        parent::__construct($compilerID);
        $this->directoryNames = $directoryNames;
    }

    /**
     * @inheritDoc
     */
    public function compile(CompilerContext $context)
    {
        foreach($this->getDirectoryNames() as $dirName) {
            if(file_exists($dirName) && !$this->removeDir($dirName, $context->getLogger())) {
                continue;
            }

            if(!file_exists($dirName)) {
                $this->makeDir($dirName, $context->getLogger());
            }
        }
    }

    /**
     * Makes the required directory. It does NOT exist!
     *
     * @param $dirName
     * @param LoggerInterface $logger
     */
    protected function makeDir($dirName, LoggerInterface $logger) {
        if(@mkdir($dirName)) {
            $logger->logText("Created directory %s", LoggerInterface::VERBOSITY_NORMAL, NULL, $dirName);
        } else {
            $logger->logWarning("Creating directory $dirName failed");
        }
    }

    /**
     * @param $dir
     * @param LoggerInterface $logger
     * @return bool
     */
    protected function removeDir($dir, LoggerInterface $logger): bool {
        try {
            if(!$this->fileSystem)
                $this->fileSystem = new Filesystem();
            $this->fileSystem->remove($dir);
            $logger->logText("Directory $dir removed", LoggerInterface::VERBOSITY_VERY);
            return true;
        } catch (IOException $exception) {
            $logger->logWarning("Removing directory $dir failed");
            return false;
        }
    }

    /**
     * @return string[]
     */
    public function getDirectoryNames(): array
    {
        return $this->directoryNames;
    }

    /**
     * @param string[] $directoryNames
     */
    public function setDirectoryNames(array $directoryNames): void
    {
        $this->directoryNames = $directoryNames;
    }
}