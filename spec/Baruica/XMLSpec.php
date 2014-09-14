<?php

namespace spec\Baruica;

use PhpSpec\ObjectBehavior;

class XMLSpec extends ObjectBehavior
{
    function it_is_initializable_from_a_file_path()
    {
        $this->beConstructedThrough('fromFile', array(
            __DIR__.'/../Resources/static_factory_constructor.xml'
        ));

        $this->shouldHaveType('Baruica\XML');
    }

    function it_is_initializable_from_a_string()
    {
        $this->beConstructedThrough('fromString', array(
            '<?xml version="1.0" ?><test_root><test_node_1>node 1</test_node_1></test_root></xml>'
        ));

        $this->shouldHaveType('Baruica\XML');
    }

    function it_is_not_initializable_through_constructor()
    {
        $this->shouldThrow('\Exception')->during('__construct');
    }
}
