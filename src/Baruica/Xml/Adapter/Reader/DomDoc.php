<?php

declare(strict_types=1);

namespace Baruica\Xml\Adapter\Reader;

use Baruica\Xml\Reader;

final class DomDoc implements Reader
{
    private $domXpath;

    private function __construct(\DOMDocument $domDocument)
    {
        $this->domXpath = new \DOMXPath($domDocument);
    }

    public static function fromFile(string $filePath): self
    {
        $domDocument = new \DOMDocument();

        try {
            if (false === file_exists($filePath) || false === $domDocument->load($filePath)) {
                throw new \RuntimeException(sprintf('Could not load xml file [%s].', $filePath));
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return new self($domDocument);
    }

    public static function fromString(string $xmlStr): self
    {
        $domDocument = new \DOMDocument();

        try {
            if (false === $domDocument->loadXML($xmlStr)) {
                throw new \RuntimeException(sprintf('Could not load XML from string [%s]', $xmlStr));
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return new self($domDocument);
    }

    public function getList(string $xpath): \Generator
    {
        if (null === $nodeList = $this->getNodeList($xpath)) {
            return [];
        }

        foreach ($nodeList as $node) {
            yield $this->getNodeValue($node);
        }
    }

    public function getNodeList(string $xpath, \DOMNode $contextNode = null): \DOMNodeList
    {
        $nodeList = (null === $contextNode)
            ? $this->domXpath->query($xpath)
            : $this->domXpath->query($xpath, $contextNode);

        if (false === $nodeList) {
            return new \DOMNodeList();
        }

        return $nodeList;
    }

    public function getFirstNode(string $xpath, \DOMNode $contextNode = null): \DOMElement
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if (0 === $nodeList->length) {
            throw new \RuntimeException('');
        }

        return $nodeList->item(0);
    }

    public function getLastNode(string $xpath, \DOMNode $contextNode = null): \DOMElement
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if (0 === $nodeList->length) {
            throw new \RuntimeException('');
        }

        $lastIndex = $nodeList->length - 1;

        return $nodeList->item($lastIndex);
    }

    public function getNodeValue(\DOMElement $node = null): string
    {
        if (null !== $node) {
            return $node->nodeValue;
        }

        throw new \InvalidArgumentException('');
    }

    public function getNodeAttribute(string $att, \DOMElement $node = null): string
    {
        if (null !== $node) {
            return $node->getAttribute($att);
        }

        throw new \InvalidArgumentException('');
    }

    public function getNeighborNodeValue(string $neighborNodeName, \DOMElement $node = null): string
    {
        if (null !== $node) {
            return $this->getNodeValue(
                $node->parentNode->getElementsByTagName($neighborNodeName)->item(0)
            );
        }

        throw new \InvalidArgumentException('');
    }

    public function getValue(string $xpath, \DOMNode $contextNode = null): string
    {
        return $this->getNodeValue($this->getFirstNode($xpath, $contextNode));
    }

    public function getLastValue(string $xpath, \DOMNode $contextNode = null): string
    {
        return $this->getNodeValue($this->getLastNode($xpath, $contextNode));
    }

    public function getValues(\DOMNodeList $contextNodes, string $keyNodeName, array $valNodes = [], \Closure $fn = null, array $fnParams = []): array
    {
        $values = [];

        foreach ($contextNodes as $node) {
            $keyNodeValue = $this->getValue($keyNodeName, $node);

            if (!array_key_exists($keyNodeValue, $values)) {
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
}
