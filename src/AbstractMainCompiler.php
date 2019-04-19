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

namespace Skyline\Compiler;

use Skyline\Compiler\Context\CompilerContext;
use Skyline\Compiler\Exception\CompilerException;
use Skyline\Compiler\Project\ProjectInterface;
use TASoft\Config\Config;

abstract class AbstractMainCompiler
{
    /** @var ProjectInterface */
    private $project;
    /** @var Config */
    private $configuration;

    /**
     * @var CompilerContext
     */
    private $context;

    /**
     * @return ProjectInterface
     */
    public function getProject(): ProjectInterface
    {
        return $this->project;
    }

    /**
     * @param ProjectInterface $project
     */
    public function setProject(ProjectInterface $project): void
    {
        $this->project = $project;
    }

    public function getSkylineAppDataDirectory() {
        $config = $this->getConfiguration();

        $skylineTarget = $config["SkylineAppDataDirectory"] ?? 'SkylineAppData';
        $projDir = $this->getProject()->getProjectRootDirectory();
        return "$projDir/$skylineTarget/";
    }

    protected function getConfiguration(): Config {
        if(!$this->configuration) {
            $cfg = new Config( $this->getCoreConfiguration() );
            $main = new Config($this->getMainConfiguration());
            $main->merge($cfg);
            $this->configuration = $main;
        }
        return $this->configuration;
    }

    abstract protected function getCoreConfiguration(): array;

    protected function getMainConfiguration(): array {
        return [

        ];
    }

    /**
     * @return CompilerContext
     */
    public function getContext(): CompilerContext
    {
        if(!$this->context) {
            $this->setContext(new CompilerContext() );
        }
        return $this->context;
    }

    /**
     * @param CompilerContext $context
     */
    public function setContext(CompilerContext $context): void
    {
        $this->context = $context;
        $this->context->setMainCompiler($this);
    }


    public function compile() {
        $config = $this->getConfiguration();
        if(!$this->getProject()) {
            if(isset($config['project']) && ($proj = $config['project']) instanceof ProjectInterface) {
                $this->setProject($proj);
            } else {
                $e = new CompilerException("Can not compile without project");
                throw $e;
            }
        }

        $domains = new Config([]);


    }
}