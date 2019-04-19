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

namespace Skyline\Compiler\Project\Loader;


use Skyline\Compiler\Exception\BadConfigurationException;
use Skyline\Compiler\Project\MutableProjectInterface;
use Skyline\Compiler\Project\ProjectInterface;
use TASoft\Service\ConfigurableServiceInterface;
use TASoft\Service\Container\AbstractContainer;
use TASoft\Service\ServiceManager;

abstract class AbstractLoader extends AbstractContainer implements ConfigurableServiceInterface, LoaderInterface
{
    private $configuration;

    /**
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param mixed $configuration
     */
    public function setConfiguration($configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @return ProjectInterface
     */
    public function getProject(): ProjectInterface
    {
        /** @var ProjectInterface $proj */
        $proj = $this->getInstance();
        return $proj;
    }

    /**
     * @inheritDoc
     */
    protected function loadInstance()
    {
        $this->loadDidBegin();
        $projClass = $this->getProjectInstanceClass();
        if(!$projClass) {
            throw new BadConfigurationException("XML Project <project> must contain a class attribute");
        }

        $projectDirectory = $this->getProjectDirectory();
        if(!is_dir($projectDirectory)) {
            throw new BadConfigurationException("XML Project <project> must contain an element called <directory> specifying a valid project directory. Can be absolute or relative to the config file");
        }

        $arguments = $this->getConstructorArguments();
        array_unshift($arguments, $projectDirectory);

        $project = $this->getServiceManager()->makeServiceInstance($projClass, $arguments);
        if($project instanceof MutableProjectInterface) {

        } else {
            throw new BadConfigurationException("Instantiated project is not mutable");
        }

        $this->loadDidComplete();
        return $project;
    }

    protected function getServiceManager(): ServiceManager {
        return ServiceManager::generalServiceManager([]);
    }

    protected function loadDidBegin() {}
    protected function loadDidComplete() {}

    /**
     * @return string
     */
    abstract protected function getProjectDirectory(): string;

    /**
     * @return string
     */
    abstract protected function getProjectInstanceClass(): string;

    /**
     *
     * @return array|null
     */
    abstract protected function getConstructorArguments(): ?array;
}