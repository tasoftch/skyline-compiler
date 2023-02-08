<?php

namespace Context\ValueCache;

use Skyline\Compiler\Context\ValueCache\ValueCache;
use PHPUnit\Framework\TestCase;

class ValueCacheTest extends TestCase
{

	public function testFetchValues()
	{

	}

	public function testFetchAll()
	{

	}

	public function testCount()
	{
		$cache = new ValueCache();
		$this->assertCount(0, $cache);

		$cache->postValue(56, "Thomas");
		$cache->postValue("18", "Katrin", 'test');

		$this->assertCount(2, $cache);
		$cache->postValue(13, "Katrin", 'test');

		$this->assertCount(2, $cache);
	}

	public function testFetchValue()
	{

	}

	public function testPostValue()
	{

	}
}
