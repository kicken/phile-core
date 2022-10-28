<?php

namespace Phile\Plugin\ParserMeta;

use PHPUnit\Framework\TestCase;

class MetaParserPluginTest extends TestCase {
    protected $metaTestData = "
Title: Welcome
Spaced Key: Should become underscored
Nested:
    nested a: 1
    nested B: 2
Description: This description will go in the meta description tag
Date: 2014/08/01
";
    private $parser;

    protected function setUp() : void{
        $this->parser = new MetaParserPlugin([], []);
    }

    public function testCanParseCStyleFence(){
        $meta = $this->parser->parse("/*\n{$this->metaTestData}\n*/");
        $this->processAssertions($meta);
    }

    public function testCanParseHtmlFence(){
        $meta = $this->parser->parse("<!--\n{$this->metaTestData}\n-->");
        $this->processAssertions($meta);
    }

    public function testCanParseYamlFence(){
        $meta = $this->parser->parse("---\n{$this->metaTestData}\n---");
        $this->processAssertions($meta);
    }

    private function processAssertions(array $meta){
        $this->assertCount(5, $meta);
        $this->assertArrayHasKey('title', $meta);
        $this->assertArrayHasKey('spaced_key', $meta);
        $this->assertArrayHasKey('nested', $meta);
        $this->assertArrayHasKey('description', $meta);
        $this->assertArrayHasKey('date', $meta);

        $this->assertEquals('Welcome', $meta['title']);
        $this->assertEquals('Should become underscored', $meta['spaced_key']);
        $this->assertEquals('This description will go in the meta description tag', $meta['description']);
        $this->assertEquals('2014/08/01', $meta['date']);

        $this->assertIsArray($meta['nested']);
        $this->assertEquals('1', $meta['nested']['nested_a']);
        $this->assertEquals('2', $meta['nested']['nested_b']);
    }
}
