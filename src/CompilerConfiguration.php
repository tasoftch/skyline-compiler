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


final class CompilerConfiguration
{
    const COMPILER_CACHE_FILENAME = 'compiler-cache';
    const COMPILER_PROJECT = 'project';

    const COMPILER_SOURCE_DIRECTORIES = 'source-dirs';
    const COMPILER_DEBUG = 'debug';
    const COMPILER_TEST = 'test';

    const COMPILER_ZERO_LINKS = 'zero';
    const COMPILER_WITH_PDO = 'with-pdo';

    const SKYLINE_APP_DATA_DIR = 'skyline-add-data';
    const SKYLINE_PUBLIC_DATA_DIR = 'skyline-public-data';

    const SKYLINE_DIR_COMPILED = 'dir-compiled';
    const SKYLINE_DIR_CLASSES = 'dir-classes';
    const SKYLINE_DIR_CONFIG = 'dir-config';
    const SKYLINE_DIR_LOGS = 'dir-logs';
    const SKYLINE_DIR_USER_INTERFACE = 'dir-ui';
    const SKYLINE_DIR_TEMPLATES = 'dir-templates';
    const SKYLINE_DIR_ACTION_CONTROLLERS = 'dir-controllers';
    const SKYLINE_DIR_MODULES = 'dir-modules';
    const SKYLINE_DIR_CACHE = 'dir-cache';



    /**
     * Defaults
     * @var array
     */
    private static $defaults = [
        self::COMPILER_CACHE_FILENAME => "./compiler-cache.php",
        self::SKYLINE_APP_DATA_DIR => 'SkylineAppData',
        self::SKYLINE_PUBLIC_DATA_DIR => 'public_html',

        self::SKYLINE_DIR_COMPILED => 'Compiled',
        self::SKYLINE_DIR_MODULES => 'Modules',
        self::SKYLINE_DIR_CACHE => 'Cache',
        self::SKYLINE_DIR_CLASSES => 'Classes',
        self::SKYLINE_DIR_CONFIG => 'Config',
        self::SKYLINE_DIR_TEMPLATES => 'Templates',
        self::SKYLINE_DIR_ACTION_CONTROLLERS => 'Classes/Controller',
        self::SKYLINE_DIR_USER_INTERFACE => 'UI',
        self::SKYLINE_DIR_LOGS => 'Logs',


        self::COMPILER_DEBUG => false,
        self::COMPILER_TEST => false,
        self::COMPILER_ZERO_LINKS => false
    ];

    /**
     * Fetches a configuration
     *
     * @param $array
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public static function get($array, $name, $default = NULL) {
        return $array[$name] ?? self::$defaults[$name] ?? $default;
    }
}