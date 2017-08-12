<?php

declare(strict_types=1);

namespace Baruica\Xml\XmlReader;

interface XmlReader
{
    public function getList(string $xpath): \Generator;
}
