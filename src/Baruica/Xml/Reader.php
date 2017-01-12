<?php
declare(strict_types=1);

namespace Baruica\Xml;

interface Reader
{
    public function getList(string $xpath) : array;
}
