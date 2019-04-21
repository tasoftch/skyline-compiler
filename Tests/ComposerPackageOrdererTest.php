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
 * ComposerPackageOrdererTest.php
 * skyline-compiler
 *
 * Created on 2019-04-21 23:05 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Predef\ComposerPackagesOrderCompiler;
use Skyline\Compiler\Project\Loader\XML;

class ComposerPackageOrdererTest extends TestCase
{
    public function testPackageOrder() {
        $compiler = new ComposerPackagesOrderCompiler('id', "./");

        $xml = new XML(__DIR__ . "/Projects/project.xml");
        /** @var MyProject $proj */
        $proj = $xml->getProject();
        $ctx = new CompilerContext($proj);

        $compiler->compile($ctx);

        $packages = $ctx->getValueCache()->fetchValue(ComposerPackagesOrderCompiler::CACHE_PACKAGES_NAME);
        $names = array_keys($packages);

        $idx1 = array_search("tasoft/collection", $names);
        $idx2 = array_search("tasoft/service-manager", $names);
        $idx3 = array_search("tasoft/config", $names);
        $idx4 = array_search("tasoft/dependency-injection", $names);
        $idx5 = array_search("skyline/kernel", $names);
        $idx6 = array_search("symfony/filesystem", $names);
        $idx7 = array_search("skyline/compiler", $names);

        $this->assertLessThan($idx2, $idx1);
        $this->assertLessThan($idx3, $idx2);
        $this->assertLessThan($idx4, $idx3);
        $this->assertLessThan($idx5, $idx4);
        $this->assertLessThan($idx6, $idx5);
        $this->assertLessThan($idx7, $idx6);
    }
}
