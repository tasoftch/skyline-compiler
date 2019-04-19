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
 * XMLProjectTest.php
 * skyline-compiler
 *
 * Created on 2019-04-19 17:27 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Compiler\Project\Loader\XML;
use Skyline\Compiler\Project\Project;

class XMLProjectTest extends TestCase
{
    /**
     * @expectedException Skyline\Compiler\Exception\BadConfigurationException
     */
    public function testProjectWithoutClass() {
        $xml = new XML(__DIR__ . "/Projects/class-less-project.xml");
        $xml->getProject();
    }

    /**
     * @expectedException Skyline\Compiler\Exception\BadConfigurationException
     */
    public function testProjectWithoutRoot() {
        $xml = new XML(__DIR__ . "/Projects/root-less-project.xml");
        $xml->getProject();
    }

    public function testProjectWithArguments() {
        $xml = new XML(__DIR__ . "/Projects/arguments-project.xml");
        /** @var MyProject $proj */
        $proj = $xml->getProject();
        $this->assertInstanceOf(MyProject::class, $proj);

        $this->assertEquals([getcwd(), 'PublicFolder', [45, "Hello World"]], $proj->arguments);
    }


}

class MyProject extends Project {
    public $arguments;

    public function __construct(string $rootDirectory = NULL, string $publicDirectory = NULL, array $attributes = NULL)
    {
        parent::__construct($rootDirectory, $publicDirectory, $attributes);
        $this->arguments = func_get_args();
    }
}
