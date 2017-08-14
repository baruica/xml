<?php

declare(strict_types=1);

namespace spec\Baruica\Xml\XmlReader;

use Baruica\Xml\XmlReader\XmlReader;
use PhpSpec\ObjectBehavior;

class DomDocXmlReaderSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromFile', [$this->getPathToXmlFile('list.xml')]);
    }

    public function it_throws_an_exception_if_the_xml_file_does_not_exist()
    {
        $this->beConstructedThrough('fromFile', [$this->getPathToXmlFile('toto.xml')]);

        $this->shouldThrow(\RuntimeException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_DOMDocument_cannot_load_the_content_of_the_xml_file()
    {
        $this->beConstructedThrough('fromFile', [$this->getPathToXmlFile('unloadable.xml')]);

        $this->shouldThrow(\RuntimeException::class)->duringInstantiation();
    }

    public function it_is_initializable_from_the_path_of_a_xml_file()
    {
        $this->beConstructedThrough('fromFile', [$this->getPathToXmlFile('static_factory_constructor.xml')]);

        $this->shouldImplement(XmlReader::class);
    }

    public function it_throws_an_exception_if_DOMDocument_cannot_load_the_xml_string()
    {
        $this->beConstructedThrough('fromString', ['unloadable content']);

        $this->shouldThrow(\RuntimeException::class)->duringInstantiation();
    }

    public function it_is_initializable_from_a_string()
    {
        $this->beConstructedThrough('fromString', ['<?xml version="1.0" ?><test_root><test_node_1>node 1</test_node_1></test_root></xml>']);

        $this->shouldImplement(XmlReader::class);
    }

    public function it_returns_a_list_of_node_values()
    {
        $this->getList('/test_root/test_nodes/*')->shouldReturn(yield 'node 1');
        $this->getList('/test_root/test_nodes/*')->shouldReturn(yield 'node 2');
        $this->getList('/test_root/test_nodes/*')->shouldReturn(yield 'node 3');
        $this->getList('/test_root/test_nodes/*')->shouldReturn(yield 'node 4');
    }

    public function it_returns_a_value()
    {
        $this->beConstructedThrough(
            'fromFile',
            [
                $this->getPathToXmlFile('with_namespaces.xml'),
                [
                    'a' => 'http://www.w3.org/2005/Atom',
                    'x' => 'http://www.w3.org/1999/xhtml',
                ]
            ]
        );

        $this->getValue('/a:entry/a:content/x:div/x:div/x:ul/x:li[@id="id_02"]')->shouldReturn('X02');
    }

    public function it_returns_an_empty_node_list_even_if_given_a_malformed_xpath_expression()
    {
        $this->getNodeList('invalide xpath')->shouldBeAnInstanceOf(\DOMNodeList::class);
    }

    private function getPathToXmlFile(string $filename): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.$filename;
    }
}
