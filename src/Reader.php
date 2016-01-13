<?php

namespace Baruica\Xml;

interface Reader
{
    /**
     * @param  string $xpath
     *
     * @return array
     */
    public function getList($xpath);
}
