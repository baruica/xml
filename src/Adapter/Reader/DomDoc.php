<?php

namespace Baruica\Xml\Adapter\Reader;

use Baruica\Xml\Reader;

class DomDoc implements Reader
{
    /** @var \DOMXPath */
    private $domXpath;

    private function __construct(\DOMDocument $doc)
    {
        $this->domXpath = new \DOMXPath($doc);
    }

    public static function fromFile(string $filePath) : Reader
    {
        $doc = new \DOMDocument();

        try {
            if (false === file_exists($filePath) || false === $doc->load($filePath)) {
                throw new \RuntimeException(sprintf('Could not load xml file [%s].', $filePath));
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return new DomDoc($doc);
    }

    public static function fromString(string $xmlStr) : Reader
    {
        $doc = new \DOMDocument();

        try {
            if (false === $doc->loadXML($xmlStr)) {
                throw new \RuntimeException(sprintf('Could not load XML from string [%s]', $xmlStr));
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return new DomDoc($doc);
    }

    public function getList(string $xpath) : array
    {
        $list = [];

        if (null === $nodeList = $this->getNodeList($xpath)) {
            return $list;
        }

        foreach ($nodeList as $node) {
            $list[] = $this->getNodeValue($node);
        }

        return $list;
    }

    public function getNodeList(string $xpath, \DOMNode $contextNode = null) : \DOMNodeList
    {
        $valNL = (null === $contextNode)
            ? $this->domXpath->query($xpath)
            : $this->domXpath->query($xpath, $contextNode);

        if (false === $valNL) {
            return new \DOMNodeList();
        }

        return $valNL;
    }

    public function getFirstNode(string $xpath, \DOMNode $contextNode = null) : \DOMElement
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if (0 === $nodeList->length) {
            return null;
        }

        return $nodeList->item(0);
    }

    public function getLastNode(string $xpath, \DOMNode $contextNode = null) : \DOMElement
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if (0 === $nodeList->length) {
            return null;
        }

        $lastIndex = $nodeList->length - 1;

        return $nodeList->item($lastIndex);
    }

    public function getNodeValue(\DOMElement $node = null) : string
    {
        if (null !== $node) {
            return $node->nodeValue;
        }

        return null;
    }

    public function getNodeAttribute(string $att, \DOMElement $node = null) : string
    {
        if (null !== $node) {
            return $node->getAttribute($att);
        }

        return null;
    }

    public function getNeighborNodeValue(string $neighborNodeName, \DOMElement $node = null) : string
    {
        if (null !== $node) {
            return $this->getNodeValue(
                $node->parentNode->getElementsByTagName($neighborNodeName)->item(0)
            );
        }

        return null;
    }

    public function getValue(string $xpath, \DOMNode $contextNode = null) : string
    {
        return $this->getNodeValue($this->getFirstNode($xpath, $contextNode));
    }

    public function getValues(\DOMNodeList $contextNodes, string $keyNodeName, array $valNodes = [], \Closure $fn = null, array $fnParams = []) : array
    {
        $values = [];

        foreach ($contextNodes as $node) {
            $keyNodeValue = $this->getValue($keyNodeName, $node);
            if (!isset($values[$keyNodeValue])) {
                $values[$keyNodeValue] = [];
            }
            foreach ($valNodes as $valNodeName) {
                if (null !== $fn) {
                    $params = $fnParams;
                    array_unshift($params, $this->getValue($valNodeName, $node));
                    $values[$keyNodeValue] = $fn($params);
                } else {
                    $values[$keyNodeValue] = $this->getValue($valNodeName, $node);
                }
            }
        }

        ksort($values);

        return $values;
    }

    public function getLastValue(string $xpath, \DOMNode $contextNode = null) : string
    {
        return $this->getNodeValue($this->getLastNode($xpath, $contextNode));
    }
}
