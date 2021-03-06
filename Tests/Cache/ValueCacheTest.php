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
 * ValueCacheTest.php
 * skyline-compiler
 *
 * Created on 2019-04-21 18:45 by thomas
 */

use Skyline\Compiler\Context\ValueCache\ValueCache;
use PHPUnit\Framework\TestCase;

class ValueCacheTest extends TestCase
{

    public function testPostValue()
    {
        $cache = new ValueCache();
        $cache->postValue(1, "test");
        $cache->postValue(2, "test2");
        $cache->postValue(3, "test", "domain");

        $this->assertCount(3, $cache);

        $cache->postValue(5, "test");
        $this->assertCount(3, $cache);

        $this->assertEquals([
            ".test" => 5,
            ".test2" => 2,
            "domain.test" => 3
        ], $cache->fetchAll());
    }

    public function testFetchValue()
    {
        $cache = new ValueCache();
        $cache->postValue(1, "test");
        $cache->postValue(2, "test2");
        $cache->postValue(3, "test", "domain");

        $this->assertEquals(1, $cache->fetchValue("test"));
        $this->assertNull($cache->fetchValue("nonexisting"));

        $this->assertEquals(3, $cache->fetchValue("test", "domain"));
    }

    public function testFetchValues()
    {
        $cache = new ValueCache();
        $cache->postValue(1, "test");
        $cache->postValue(2, "test2");
        $cache->postValue(3, "test", "domain");
        $cache->postValue(4, "test3", "domain");
        $cache->postValue(5, "test2", "domain");
        $cache->postValue(6, "test", "domain");

        $this->assertCount(5, $cache);

        $this->assertEquals([
            'test' => 6,
            'test3' => 4,
            'test2' => 5
        ], $cache->fetchValues("domain"));

        $this->assertEquals([
            'test' => 1,
            'test2' => 2
        ], $cache->fetchValues());
    }
}
