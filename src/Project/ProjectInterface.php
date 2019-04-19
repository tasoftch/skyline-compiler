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

namespace Skyline\Compiler\Project;


interface ProjectInterface
{
    const SEARCH_PATH_VENDOR = 'vendor';
    const SEARCH_PATH_USER_CONFIG = 'config';
    const SEARCH_PATH_USER_MODULES = 'modules';
    const SEARCH_PATH_CLASSES = 'classes';

    /**
     * Returns the root directory of the project
     * @return string
     */
    public function getProjectRootDirectory(): string;

    /**
     * Returns the public directory of the project.
     * This is where the public files will be distributed.
     *
     * @return string
     */
    public function getProjectPublicDirectory(): string;

    /**
     * Gets the attribute of the project
     *
     * @param string $attributeName
     * @return mixed
     */
    public function getAttribute(string $attributeName);

    /**
     * Gets the search paths of the project
     *
     * @param string $searchPathName
     * @return array|null
     * @see ProjectInterface::SEARCH_PATH_* constants
     */
    public function getSearchPaths(string $searchPathName): ?array;
}