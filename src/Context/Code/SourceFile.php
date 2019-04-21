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


use Skyline\Compiler\Exception\FileOrDirectoryNotFoundException;
use SplFileInfo;

final class SourceFile
{
    /** @var string */
    private $fileName;
    private $pathName;

    public static $stringifyReal = false;

    /**
     * SourceFile constructor.
     * @param $filename
     */
    public function __construct($filename)
    {
        if($filename instanceof SplFileInfo) {
            $this->fileName = $filename->getFilename();
            $this->pathName = $filename->getPathname();
        } else {
            $this->fileName = basename( $this->pathName = (string) $filename );
        }

        if(!file_exists($this->pathName)) {
            $e = new FileOrDirectoryNotFoundException("File or directory $this->fileName not found");
            $e->setFilename($this->pathName);
            throw $e;
        }
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getPathName(): string
    {
        return $this->pathName;
    }

    public function getRealPath() {
        return realpath($this->pathName);
    }

    public function __toString()
    {
        return self::$stringifyReal ? $this->getRealPath() : $this->getPathName();
    }
}