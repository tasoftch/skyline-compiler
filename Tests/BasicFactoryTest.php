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
 * BasicFactoryTest.php
 * skyline-compiler
 *
 * Created on 2019-04-21 23:45 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Compiler\Context\Logger\SilentLogger;
use Skyline\Compiler\Factory\ConfigMainCompilerFactory;
use Skyline\Compiler\Factory\CreateHTAccessCompilerFactory;
use Skyline\Compiler\Factory\SkylineEntryPointCompilerFactory;
use Skyline\Compiler\Project\ProjectInterface;
use Symfony\Component\Filesystem\Filesystem;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Factory\BasicCompilersFactory;
use Skyline\Compiler\Project\Loader\XML;

class BasicFactoryTest extends TestCase
{
    /** @var ProjectInterface */
    public static $project;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $xml = new XML(__DIR__ . "/Projects/project.xml");
        /** @var MyProject $proj */
        self::$project = $xml->getProject();

        $fs = new Filesystem();
        $fs->remove(self::$project->getProjectRootDirectory() . "/SkylineAppData");
    }

    public function testFactory() {
        $ctx = new CompilerContext(self::$project);
        $ctx->setLogger($logger = new SilentLogger());

        $ctx->addCompiler(new SkylineEntryPointCompilerFactory());
        $ctx->addCompiler(new BasicCompilersFactory());
        $ctx->addCompiler(new ConfigMainCompilerFactory());
        $ctx->addCompiler(new CreateHTAccessCompilerFactory());

        $ctx->compile();

        $dappData = self::$project->getProjectRootDirectory() . "/SkylineAppData/";

        $this->assertDirectoryExists($dappData);
        $this->assertDirectoryExists("$dappData/Classes");
        $this->assertDirectoryExists("$dappData/Compiled");
        $this->assertDirectoryExists("$dappData/Config");

        $this->assertFileExists("$dappData/Compiled/composer-packages.php");
    }
}
