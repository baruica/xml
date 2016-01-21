<?php

namespace spec\Baruica\Xml\Adapter\Reader;

use PhpSpec\ObjectBehavior;

use Baruica\Xml\Reader;

class DomDocSpec extends ObjectBehavior
{
    public function it_is_initializable_from_a_file_path()
    {
        $this->beConstructedThrough('fromFile', array(
            __DIR__.'/../../../res/xml/static_factory_constructor.xml'
        ));

        $this->shouldHaveType(Reader::class);
    }

    public function it_is_initializable_from_a_string()
    {
        $this->beConstructedThrough('fromString', array(
            '<?xml version="1.0" ?><test_root><test_node_1>node 1</test_node_1></test_root></xml>'
        ));

        $this->shouldHaveType(Reader::class);
    }

    public function it_is_not_initializable_through_constructor()
    {
        $this->shouldThrow('\Exception')->during('__construct');
    }
}
