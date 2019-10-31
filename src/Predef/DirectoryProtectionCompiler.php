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

class DirectoryProtectionCompiler extends AbstractCompiler
{
    /** @var string[] */
    private $directoryNames;


    /**
     * CreateDirectoriesCompiler constructor.
     * @param string[] $directoryNames
     */
    public function __construct(string $compilerID, array $directoryNames)
    {
        parent::__construct($compilerID);
        $this->directoryNames = $directoryNames;
    }



    public function compile(CompilerContext $context)
    {
        foreach($this->getDirectoryNames() as $dirName) {
            if(is_dir($dirName)) {
                $this->recursiveProtectDirectory(realpath($dirName));
            }
        }
    }

    private function recursiveProtectDirectory($directory) {
        if($items = scandir($directory)) {
            foreach($items as $item) {
                if($item[0] == '.')
                    continue;

                if(is_dir($directory . DIRECTORY_SEPARATOR . $item))
                    $this->recursiveProtectDirectory($directory . DIRECTORY_SEPARATOR . $item);
            }

            if(!file_exists("$directory" . DIRECTORY_SEPARATOR . ".htaccess")) {
                file_put_contents("$directory" . DIRECTORY_SEPARATOR . ".htaccess", 'Deny from all');
            }
        }
    }

    /**
     * @return string[]
     */
    public function getDirectoryNames(): array
    {
        return $this->directoryNames;
    }
}