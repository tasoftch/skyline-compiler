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
 * MemoryCacheTest.php
 * skyline-compiler
 *
 * Created on 2019-04-21 19:27 by thomas
 */

use Skyline\Compiler\Context\FileCache\MemoryCache;
use PHPUnit\Framework\TestCase;

class MemoryCacheTest extends TestCase
{
    public function testLocalMemoryCache() {
        $cache = new MemoryCache();
        $this->assertFalse($cache->has(__FILE__));

        $cache->set(__FILE__, 12);
        $this->assertEquals(12, $cache->get(getcwd() . "/Tests/Cache/MemoryCacheTest.php"));
        $this->assertTrue($cache->has(__FILE__));

        $this->assertFalse($cache->changed(__FILE__));
    }

    /**
     * @expectedException Skyline\Compiler\Exception\FileOrDirectoryNotFoundException
     */
    public function testNonexistingFileChanged() {
        $cache = new MemoryCache();
		$this->expectException(\Skyline\Compiler\Exception\FileOrDirectoryNotFoundException::class);
        $cache->set("Tests/Cache/MemoryCacheTestingFileThatDoesNotExist.php", 12);
    }

    public function testLocalVarCache() {
        $cache = new MemoryCache($local);
        $this->assertSame([], $local);

        $cache->set(__FILE__, "Thomas");
        $this->assertSame([
            "meta" => [
                __FILE__ => filemtime(__FILE__)
            ],
            "data" => [
                __FILE__ => 'Thomas'
            ]
        ], $local);
    }

    public function testHasChanged() {
        $local = [
            "meta" => [
                __FILE__ => 13
            ]
        ];
        $cache = new MemoryCache($local);
        $this->assertTrue($cache->changed(__FILE__));
    }

    public function testHasCached() {
        $local = [
            'meta' => [
                __FILE__ => filemtime(__FILE__)
            ],
            'data' => [
                __FILE__ => 'TASoft'
            ]
        ];
        $cache = new MemoryCache($local);
        $this->assertFalse($cache->changed(__FILE__));

        $this->assertSame("TASoft", $cache->get(__FILE__));

        $cache->set(__FILE__, "Applications");
        $this->assertSame("Applications", $cache->get(__FILE__));
    }
}
