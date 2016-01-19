<?php

namespace Baruica\Xml;

interface Reader
{
    public function getList(string $xpath) : array;
}
