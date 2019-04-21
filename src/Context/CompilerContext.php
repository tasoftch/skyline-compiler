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

namespace Skyline\Compiler\Context;


use Skyline\Compiler\AbstractMainCompiler;
use Skyline\Compiler\Context\FileCache\FileCacheInterface;
use Skyline\Compiler\Context\FileCache\LocalFileCache;
use Skyline\Compiler\Context\Logger\LoggerInterface;
use Skyline\Compiler\Context\Logger\OutputLogger;
use Skyline\Compiler\Context\ValueCache\ValueCache;
use Skyline\Compiler\Context\ValueCache\ValueCacheInterface;

class CompilerContext
{
    /** @var AbstractMainCompiler */
    private $mainCompiler;

    /** @var LoggerInterface */
    private $logger;

    /** @var ValueCacheInterface */
    private $valueCache;

    /** @var FileCacheInterface */
    private $fileCache;

    /**
     * @return AbstractMainCompiler
     */
    public function getMainCompiler(): AbstractMainCompiler
    {
        return $this->mainCompiler;
    }

    /**
     * @param AbstractMainCompiler $mainCompiler
     */
    public function setMainCompiler(AbstractMainCompiler $mainCompiler): void
    {
        $this->mainCompiler = $mainCompiler;
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
        if(!$this->fileCache)
            $this->fileCache = new LocalFileCache("./compiler-cache.php");
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


}