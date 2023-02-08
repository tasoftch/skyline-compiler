<?php

namespace Context\Code;

use Skyline\Compiler\Context\Code\Pattern;
use PHPUnit\Framework\TestCase;

class PatternTest extends TestCase
{

	public function testMatch()
	{
		$pattern = new Pattern("*.tgt", Pattern::MODE_FILES);
		$this->assertFalse($pattern->match(__FILE__));
		$this->assertFalse($pattern->match( __DIR__ . "/MyTestPattern.tgt"));
		$this->assertTrue($pattern->match( __DIR__ . "/MyTestPattern.tgt/MyTestFile.tgt"));

		$pattern = new Pattern("*.tgt", Pattern::MODE_DIRECTORIES);

		$this->assertFalse($pattern->match(__FILE__));
		$this->assertTrue($pattern->match( __DIR__ . "/MyTestPattern.tgt"));
		$this->assertFalse($pattern->match( __DIR__ . "/MyTestPattern.tgt/MyTestFile.tgt"));

		$pattern = new Pattern("*.tgt", Pattern::MODE_DIRECTORIES | Pattern::MODE_FILES);

		$this->assertFalse($pattern->match(__FILE__));
		$this->assertTrue($pattern->match( __DIR__ . "/MyTestPattern.tgt"));
		$this->assertTrue($pattern->match( __DIR__ . "/MyTestPattern.tgt/MyTestFile.tgt"));
	}

	public function testGetMode()
	{
		$pattern = new Pattern("*.txt", 15);
		$this->assertEquals(15, $pattern->getMode());
	}

	public function testIsCaseSensitive()
	{
		$pattern = new Pattern("*.txt", 0, true);
		$this->assertTrue($pattern->isCaseSensitive());

		$pattern = new Pattern("*.txt");
		$this->assertFalse($pattern->isCaseSensitive());
	}

	public function test__construct()
	{
		$pattern = new Pattern("*.txt");
		$this->assertInstanceOf(Pattern::class, $pattern);
	}

	public function testGetFormat()
	{
		$pattern = new Pattern("*.txt");
		$this->assertEquals("*.txt", $pattern->getFormat());
	}
}
