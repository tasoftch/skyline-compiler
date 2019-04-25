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


use Generator;
use Skyline\Compiler\Exception\BadConfigurationException;
use Skyline\Compiler\Project\Attribute\Attribute;
use Skyline\Compiler\Project\Attribute\AttributeCollection;
use Skyline\Compiler\Project\Attribute\AttributeInterface;
use Skyline\Compiler\Project\Attribute\CompilerContextParameterCollection;
use Skyline\Compiler\Project\Attribute\FilterAttribute;
use Skyline\Compiler\Project\Attribute\SearchPathAttribute;
use Skyline\Compiler\Project\Attribute\SearchPathCollection;
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

            foreach($this->yieldAttributes() as $name => $attr) {
                if($attr instanceof AttributeInterface) {
                    $project->setAttribute($attr);
                }
                elseif(is_array($attr)) {
                    $col = new AttributeCollection($name);
                    $col->setAttributes($attr);
                    $project->setAttribute($col);
                } else {
                    $project->setAttribute(new Attribute($name, $attr));
                }
            }

            $searchPathCollection = NULL;
            foreach($this->yieldSearchPaths() as $type => $searchPath) {

                if(!($searchPath instanceof AttributeInterface)) {
                    $searchPath = new SearchPathAttribute($type, $searchPath);
                }

                if(!$searchPathCollection)
                    $searchPathCollection = new SearchPathCollection(AttributeInterface::SEARCH_PATHS_ATTR_NAME);
                $searchPathCollection->addSearchPath($searchPath);
            }

            if($searchPathCollection)
                $project->setAttribute($searchPathCollection);

            $cors = NULL;
            foreach($this->yieldCrossOriginResourceSharingHosts() as $name => $host) {
                if(!($host instanceof AttributeInterface))
                    $host = new Attribute($name, $host);

                if(!$cors)
                    $cors = new AttributeCollection(AttributeInterface::HOSTS_ATTR_NAME);

                $cors->addAttribute($host);
            }

            if($cors)
                $project->setAttribute($cors);

            $wl = new AttributeCollection(AttributeInterface::WHITELIST_ATTR_NAME);

            foreach($this->yieldWhitelistAccess() as $idx => $whitelist) {
                $wl->addAttribute(new Attribute($idx, $whitelist));
            }

            $project->setAttribute($wl);

            $fl = new AttributeCollection(AttributeInterface::FILTER_ATTR_NAME);
            foreach ($this->yieldFilters() as $idx => $filter) {
                if($filter instanceof FilterAttribute) {
                    $fl->addAttribute($filter);
                }
            }
            $project->setAttribute($fl);

            $ctxParams = new CompilerContextParameterCollection('context');
            $this->loadCompilerContextParameters($ctxParams);
            $project->setAttribute($ctxParams);

            $this->completeProject($project);
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

    /**
     * Yields all attributes
     * @return Generator
     */
    abstract protected function yieldAttributes(): Generator;

    /**
     * Yields all search paths
     * @return Generator
     */
    abstract protected function yieldSearchPaths(): Generator;

    /**
     * Yields all cross origin hosts and hotlink protected hosts as well.
     * @return Generator
     */
    abstract protected function yieldCrossOriginResourceSharingHosts(): Generator;

    /**
     * Yields all whitelist accesses
     * @return Generator
     */
    abstract protected function yieldWhitelistAccess(): Generator;

    /**
     * Yields all filters
     * @return Generator
     */
    abstract protected function yieldFilters(): Generator;

    /**
     * Called to load parameters for context collection
     * @param CompilerContextParameterCollection $parameterCollection
     */
    protected function loadCompilerContextParameters(CompilerContextParameterCollection $parameterCollection) {
    }

    /**
     * Finally passes the project to this method to adjust final settings
     *
     * @param MutableProjectInterface $mutableProject
     */
    protected function completeProject(MutableProjectInterface $mutableProject) {
    }
}