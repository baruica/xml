<?php

declare(strict_types=1);

namespace Baruica\Xml\Adapter\Reader;

use Baruica\Xml\Reader;
use PhpSpec\ObjectBehavior;

class DomDocSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromFile', [__DIR__.'/list.xml']);
    }

    public function it_is_not_initializable_through_constructor()
    {
        $this->shouldThrow(\Exception::class)->during('__construct');
    }

    public function it_throws_an_exception_if_the_xml_file_does_not_exist()
    {
        $this->beConstructedThrough('fromFile', [__DIR__.'/toto.xml']);

        $this->shouldThrow(\RuntimeException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_DOMDocument_cannot_load_the_content_of_the_xml_file()
    {
        $this->beConstructedThrough('fromFile', [__DIR__.'/unloadable.xml']);

        $this->shouldThrow(\RuntimeException::class)->duringInstantiation();
    }

    public function it_is_initializable_from_the_path_of_a_xml_file()
    {
        $this->beConstructedThrough('fromFile', [__DIR__.'/static_factory_constructor.xml']);

        $this->shouldImplement(Reader::class);
    }

    public function it_throws_an_exception_if_DOMDocument_cannot_load_the_xml_string()
    {
        $this->beConstructedThrough('fromString', ['unloadable content']);

        $this->shouldThrow(\RuntimeException::class)->duringInstantiation();
    }

    public function it_is_initializable_from_a_string()
    {
        $this->beConstructedThrough('fromString', ['<?xml version="1.0" ?><test_root><test_node_1>node 1</test_node_1></test_root></xml>']);

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
