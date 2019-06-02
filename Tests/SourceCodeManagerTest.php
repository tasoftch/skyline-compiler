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
 * SourceCodeManagerTest.php
 * skyline-compiler
 *
 * Created on 2019-04-21 21:22 by thomas
 */

use Skyline\Compiler\CompilerConfiguration;
use Skyline\Compiler\CompilerContext;
use PHPUnit\Framework\TestCase;
use Skyline\Compiler\Context\Code\SourceCodeManager;
use Skyline\Compiler\Context\Code\TestsExcludingSourceCodeManager;
use Skyline\Compiler\Predef\ComposerPackagesOrderCompiler;
use Skyline\Compiler\Project\Loader\XML;
use TASoft\Config\Config;

class SourceCodeManagerTest extends TestCase
{
    public function testSourceCodeManager() {
        $xml = new XML(__DIR__ . "/Projects/project.xml");
        /** @var MyProject $proj */
        $proj = $xml->getProject();
        $ctx = new CompilerContext($proj);

        $ctx->setSourceCodeManager(new TestsExcludingSourceCodeManager($ctx));

        $gen = $ctx->getSourceCodeManager()->yieldSourceFiles('/^AbstractContaineredCollection\.php$/i');
        foreach ($gen as $name => $file) {
           $this->assertEquals("vendor/tasoft/collection/src/AbstractContaineredCollection.php", $name);
        }
    }

    public function testCustomFilePatjs() {
        $xml = new XML(__DIR__ . "/Projects/project.xml");
        /** @var MyProject $proj */
        $proj = $xml->getProject();
        $ctx = new CompilerContext($proj);

        $ctx->setSourceCodeManager(new SourceCodeManager($ctx));

        foreach($ctx->getSourceCodeManager()->yieldSourceFiles("/^\..*?$/i", ['Tests/Projects']) as $fn => $file) {
            $this->assertEquals("Tests/Projects/.DS_Store", $fn);
            break;
        }
    }

    public function testZeroSourceFiles() {
        $xml = new XML(__DIR__ . "/Projects/project.xml");
        /** @var MyProject $proj */
        $proj = $xml->getProject();
        $ctx = new CompilerContext($proj);
        $ctx->setConfiguration(new Config([
            CompilerConfiguration::COMPILER_ZERO_LINKS => true
        ]));

        $ctx->setSourceCodeManager(new SourceCodeManager($ctx));

        foreach($ctx->getSourceCodeManager()->yieldSourceFiles('/^AbstractContaineredCollection\.php$/i') as $fn => $file) {
            $this->assertEquals(getcwd()."/vendor/tasoft/collection/src/AbstractContaineredCollection.php", $fn);
            break;
        }
    }

    public function testOrderedSourceFiles() {
        $xml = new XML(__DIR__ . "/Projects/project.xml");
        /** @var MyProject $proj */
        $proj = $xml->getProject();
        $ctx = new CompilerContext($proj);

        $ctx->setConfiguration(new Config([
            CompilerConfiguration::COMPILER_ZERO_LINKS => false
        ]));

        $compiler = new ComposerPackagesOrderCompiler('id', "./", false);
        $compiler->compile($ctx);

        $scm = new SourceCodeManager($ctx);
        $ctx->setSourceCodeManager($scm);

        $scm->setRespectPackageOrder(true);

        $gen = $ctx->getSourceCodeManager()->yieldSourceFiles('/^composer\.json$/i');
        print_r(array_keys(iterator_to_array($gen)));
    }
}
