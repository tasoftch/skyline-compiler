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
 * XMLValueNormalizerTest.php
 * skyline-compiler
 *
 * Created on 2019-04-19 17:00 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Compiler\Project\Loader\XML;

class XMLValueNormalizerTest extends TestCase
{
    public function testScalar() {
        $xml = new SimpleXMLElement("<item >Hello World</item>");
        $this->assertSame("Hello World", XML::getXMLElementValue($xml));

        $xml = new SimpleXMLElement("<heheh-lodar >89</heheh-lodar>");
        $this->assertSame("89", XML::getXMLElementValue($xml));

        $xml = new SimpleXMLElement("<heheh-lodar type='int'>89</heheh-lodar>");
        $this->assertSame(89, XML::getXMLElementValue($xml));

        $xml = new SimpleXMLElement("<item type='bool'>Hello World</item>");
        $this->assertSame(true, XML::getXMLElementValue($xml));

        $xml = new SimpleXMLElement("<item type='bool'>0</item>");
        $this->assertSame(false, XML::getXMLElementValue($xml));

        $xml = new SimpleXMLElement("<item type='int'>13.899945</item>");
        $this->assertSame(13, XML::getXMLElementValue($xml));

        $xml = new SimpleXMLElement("<item type='float'>14.665</item>");
        $this->assertSame(14.665, XML::getXMLElementValue($xml));
    }

    public function testArray() {
        $xml = new SimpleXMLElement("<hello type='list'>
    <item>4</item>
    <hello>9</hello>
    <nein>10</nein>
</hello>");
        $this->assertSame(['4', '9', '10'], XML::getXMLElementValue($xml));

        $xml = new SimpleXMLElement("<hello type='list'>
    <item type='int'>4</item>
    <hello type='bool'>3</hello>
    <nein type='float'>10.05</nein>
</hello>");
        $this->assertSame([4, true, 10.05], XML::getXMLElementValue($xml));
    }

    public function testNestedArray() {
        $xml = new SimpleXMLElement("<hello type='list'>
    <item type='int'>4</item>
    <hello type='bool'>3</hello>
    <nein type='float'>10.05</nein>
    <hello type='list'>
        <item type='int'>4</item>
        <hello type='bool'>3</hello>
        <nein type='float'>10.05</nein>
    </hello>
</hello>");
        $this->assertSame([4, true, 10.05, [4, true, 10.05]], XML::getXMLElementValue($xml));
    }

    public function testKeyedList() {
        $xml = new SimpleXMLElement("<hello type='list'>
    <item key='test' type='int'>4</item>
    <hello key='me' type='bool'>3</hello>
    <nein key='he' type='float'>10.05</nein>
</hello>");
        $this->assertSame(['test' => 4, 'me'=>true, 'he'=>10.05], XML::getXMLElementValue($xml));
    }
}
