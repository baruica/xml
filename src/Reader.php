<?php

namespace Baruica\Xml;

interface Reader
{
    const CLASSNAME = __CLASS__;    // workaround for PHP 5.5 class constant (MyClass::class)

    /**
     * @param  string $xpath
     *
     * @return array
     */
    function getList($xpath);
}
