<?php

/**
 * Copyright (c) 2012 Alchemy-fr
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace DataURI\Tests;

use DataURI\Data;
use DataURI\Exception\FileNotFoundException;
use DataURI\Exception\TooLongDataException;
use PHPUnit\Framework\TestCase;

/**
 *
 * @author      Nicolas Le Goff
 * @author      Phraseanet team
 * @license     http://opensource.org/licenses/MIT MIT
 */
class DataTest extends TestCase
{
    public function testTooLongException(): void
    {
        $i = 0;
        $string = '';
        while ($i < Data::ATTS_TAG_LIMIT + 1) {
            $string .= 'x';
            $i++;
        }

        try {
            $dataURI = new Data($string, null, array(), true);
            $this->fail('An exception should have beeen raised');
        } catch (TooLongDataException $e) {
            $this->assertEquals(Data::ATTS_TAG_LIMIT + 1, $e->getLength());
        }

        $dataURI = new Data($string);

        try {
            $dataURI = new Data($string, null, array(), true, Data::LITLEN);
            $this->fail('An exception should have beeen raised');
        } catch (TooLongDataException $e) {

        }
    }

    public function testGetData(): void
    {
        $dataString = 'Lorem ipsum dolor sit amet';
        $dataURI = new Data($dataString);
        $this->assertEquals($dataString, $dataURI->getData());
    }

    public function testGetMimeType(): void
    {
        $dataString = 'Lorem ipsum dolor sit amet';
        $mimeType = 'text/plain';
        $dataURI = new Data($dataString, $mimeType);
        $this->assertEquals($mimeType, $dataURI->getMimeType());
    }

    public function testGetParameters(): void
    {
        $dataString = 'Lorem ipsum dolor sit amet';
        $mimeType = 'text/plain';
        $parameters = array('charset', 'utf-8');
        $dataURI = new Data($dataString, $mimeType, $parameters);
        $this->assertEquals($parameters, $dataURI->getParameters());
        $this->assertIsArray($dataURI->getParameters());
    }

    public function testIsBinaryData(): void
    {
        $dataString = 'Lorem ipsum dolor sit amet';
        $dataURI = new Data($dataString);
        $dataURI->setBinaryData(true);
        $this->assertTrue($dataURI->isBinaryData());
    }

    public function testInit(): void
    {
        $dataString = 'Lorem ipsum dolor sit amet';
        $dataURI = new Data($dataString);
        $parameters = $dataURI->getParameters();
        $this->assertArrayHasKey('charset', $parameters);
        $this->assertEquals('US-ASCII', $parameters['charset']);
        $this->assertEquals('text/plain', $dataURI->getMimeType());
    }

    public function testAddParameters(): void
    {
        $dataString = 'Lorem ipsum dolor sit amet';
        $dataURI = new Data($dataString);
        $current = count($dataURI->getParameters());
        $dataURI->addParameters('charset', 'iso-8859-7');
        $this->assertCount($current, $dataURI->getParameters());
        $dataURI->addParameters('another-charset', 'iso-8859-7');
        $this->assertGreaterThan($current, count($dataURI->getParameters()));
        $this->assertArrayHasKey('another-charset', $dataURI->getParameters());
    }

    public function testBuildFromFile(): void
    {
        $file = __DIR__ . '/smile.png';
        $dataURI = Data::buildFromFile($file);
        $this->assertInstanceOf('DataURI\Data', $dataURI);
        $this->assertEquals('image/png', $dataURI->getMimeType());
        $this->assertEquals(file_get_contents($file), $dataURI->getData());
    }

    public function testBuildFromUrlShouldThrowFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $url = 'https://placehold.co/x150.png';
        Data::buildFromUrl($url);
    }

    public function testBuildFromUrl(): void
    {
        $url = 'https://placehold.co/350x150.png';
        $dataURI = Data::buildFromUrl($url);
        $this->assertInstanceOf('DataURI\Data', $dataURI);
        $this->assertEquals('image/png', $dataURI->getMimeType());
        $this->assertEquals(file_get_contents($url), $dataURI->getData());
    }

    public function testFileNotFound(): void
    {
        $this->expectException(FileNotFoundException::class);
        $filename = __DIR__ . '/unknown-file';

        $dataString = 'Lorem ipsum dolor sit amet';
        $dataURI = new Data($dataString);
        $dataURI->write($filename);
    }

    public function testFileNotFoundFromFile(): void
    {
        $this->expectException(FileNotFoundException::class);

        $filename = __DIR__ . '/unknown-file';

        Data::buildFromFile($filename);
    }

    public function testWrite(): void
    {
        $filename = __DIR__ . '/test';
        $this->createEmptyFile($filename);
        $dataString = 'hello world';
        $dataURI = new Data($dataString);
        $dataURI = Data::buildFromFile($dataURI->write($filename));
        $this->assertEquals($dataString, $dataURI->getData());
        unlink($filename);
    }

    private function createEmptyFile(string $filename): void
    {
        if (file_exists($filename)) {
            unlink($filename);
        }

        $handle = fopen($filename, 'x+');
        fwrite($handle, '');
        fclose($handle);
    }
}
