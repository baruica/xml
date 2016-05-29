<?php

namespace tests\Baruica\Xml\Adapter\Reader;

use PhpSpec\ObjectBehavior;
use Baruica\Xml\Reader;

/**
 * @mixin \Baruica\Xml\Adapter\Reader\DomDoc
 */
class DomDocSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedFromFile(__DIR__.'/../../../res/xml/list.xml');
    }

    public function it_is_not_initializable_through_constructor()
    {
        $this->shouldThrow('\Exception')->during('__construct');
    }

    public function it_throws_an_exception_if_the_xml_file_does_not_exist()
    {
        $this->beConstructedFromFile('toto.xml');

        $this->shouldThrow('\RuntimeException')->duringInstantiation();
    }

    public function it_throws_an_exception_if_DOMDocument_cannot_load_the_content_of_the_xml_file()
    {
        $this->beConstructedFromFile(__DIR__.'/../../../res/xml/unloadable.xml');

        $this->shouldThrow('\RuntimeException')->duringInstantiation();
    }

    public function it_is_initializable_from_the_path_of_a_xml_file()
    {
        $this->beConstructedFromFile(__DIR__.'/../../../res/xml/static_factory_constructor.xml');

        $this->shouldImplement(Reader::class);
    }

    public function it_throws_an_exception_if_DOMDocument_cannot_load_the_xml_string()
    {
        $this->beConstructedFromString('unloadable content');

        $this->shouldThrow('\RuntimeException')->duringInstantiation();
    }

    public function it_is_initializable_from_a_string()
    {
        $this->beConstructedFromString('<?xml version="1.0" ?><test_root><test_node_1>node 1</test_node_1></test_root></xml>');

        $this->shouldImplement(Reader::class);
    }

    public function it_returns_a_list_of_node_values()
    {
        $this->getList('/test_root/test_nodes/*')->shouldReturn([
            'node 1',
            'node 2',
            'node 3',
            'node 4',
        ]);
    }
}
