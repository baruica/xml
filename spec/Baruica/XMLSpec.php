<?php

namespace spec\Baruica;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class XMLSpec extends ObjectBehavior
{
    const XML_FILE = '/../Resources/static_factory_constructor.xml';
    const XML_STR  = '<?xml version="1.0" ?><test_root><test_node_1>node 1</test_node_1></test_root></xml>';

    function it_is_initializable_from_a_file()
    {
        $this->beConstructedThrough('fromFile', array(__DIR__.self::XML_FILE));

        $this->shouldHaveType('Baruica\XML');
    }

    function it_is_initializable_from_a_string()
    {
        $this->beConstructedThrough('fromString', array(self::XML_STR));

        $this->shouldHaveType('Baruica\XML');
    }

    function it_is_not_initializable_through_constructor()
    {
        $this->shouldThrow('\Exception')->during('__construct');
    }
}
