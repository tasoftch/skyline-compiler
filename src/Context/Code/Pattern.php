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


class Pattern
{
    const MODE_FILES = 1<<0;
    const MODE_DIRECTORIES = 1<<1;
    const MODE_SYMLINKS = 1<<2;

    /** @var string */
    private $format;
    /** @var int */
    private $mode;

    /** @var bool */
    private $caseSensitive;

    /**
     * Pattern constructor.
     * @param string $format
     * @param int $mode
     * @param bool $caseSensitive
     */
    public function __construct(string $format, int $mode = self::MODE_FILES | self::MODE_DIRECTORIES, bool $caseSensitive = false)
    {
        $this->format = $format;
        $this->mode = $mode;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Returns true, if the filename matches
     *
     * @param string $filename
     * @return bool
     */
    public function match(string $filename): bool {
        if(
            (is_file($filename) && $this->getMode() & self::MODE_FILES)      ||
            (is_link($filename) && $this->getMode() & self::MODE_SYMLINKS)   ||
            (is_dir($filename) && $this->getMode() & self::MODE_DIRECTORIES)
        )
            return fnmatch($this->getFormat(), basename($filename), $this->isCaseSensitive() ? FNM_CASEFOLD : 0);
        return false;
    }

    /**
     * @return bool
     */
    public function isCaseSensitive(): bool
    {
        return $this->caseSensitive;
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }
}