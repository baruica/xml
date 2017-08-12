<?php

declare(strict_types=1);

namespace Baruica\Xml\XmlReader;

interface XmlReader
{
    public function getList(string $xpath): \Generator;

    /**
     * @return string | null
     */
    public function getValue(string $xpath, \DOMNode $contextNode = null);
}
