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

/**
 * DirectoryProtectionTest.php
 * Skyline Compiler
 *
 * Created on 2019-10-31 15:56 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Predef\DirectoryProtectionCompiler;
use Skyline\Compiler\Project\Attribute\Attribute;
use Skyline\Compiler\Project\Project;
use Symfony\Component\Filesystem\Filesystem;

class DirectoryProtectionTest extends TestCase
{
    public function testProtection() {
        $fs = new Filesystem();

        $fs->mkdir([
            "Protect",
            "Protect/Directory",
            "Protect/Test",
            "Protect/Other",
            "Protect/Directory/Test",
            "Protect/Directory/Other",
        ]);

        file_put_contents("Protect/Test/.htaccess", "test");

        $compiler = new DirectoryProtectionCompiler('', ['Protect']);
        $proj = new Project();
        $proj->setAttribute(new Attribute("public", "Public"));

        $ctx = new CompilerContext($proj);

        $compiler->compile($ctx);

        $this->assertFileExists("Protect/.htaccess");
        $this->assertFileExists("Protect/Directory/.htaccess");
        $this->assertFileExists("Protect/Test/.htaccess");
        $this->assertFileExists("Protect/Other/.htaccess");
        $this->assertFileExists("Protect/Directory/Test/.htaccess");
        $this->assertFileExists("Protect/Directory/Other/.htaccess");

        $this->assertEquals('Deny from all', file_get_contents("Protect/.htaccess"));
        $this->assertEquals('Deny from all', file_get_contents("Protect/Directory/.htaccess"));
        $this->assertEquals('test', file_get_contents("Protect/Test/.htaccess"));
        $this->assertEquals('Deny from all', file_get_contents("Protect/Other/.htaccess"));
        $this->assertEquals('Deny from all', file_get_contents("Protect/Directory/Test/.htaccess"));
        $this->assertEquals('Deny from all', file_get_contents("Protect/Directory/Other/.htaccess"));

        $fs->remove("Protect");
    }
}
