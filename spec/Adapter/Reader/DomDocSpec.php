<?php

namespace spec\Baruica\Xml\Adapter\Reader;

use PhpSpec\ObjectBehavior;

use Baruica\Xml\Reader;
use Puli\Repository\Api\ResourceRepository;

class DomDocSpec extends ObjectBehavior
{
    public function it_is_initializable_from_a_file_path()
    {
        $factoryClass = PULI_FACTORY_CLASS;
        $factory = new $factoryClass();

        /** @var ResourceRepository $repo */
        $repo = $factory->createRepository();

        $this->beConstructedThrough('fromFile', array(
            $repo->get('/baruica/xml/xml/static_factory_constructor.xml')->getFilesystemPath()
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
