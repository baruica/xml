<?php

declare(strict_types=1);

namespace Baruica\Xml\XmlReader;

final class DomDocXmlReader implements XmlReader
{
    private $domXpath;

    private function __construct(\DOMDocument $domDocument, array $namespaces = [])
    {
        $this->domXpath = new \DOMXPath($domDocument);

        foreach ($namespaces as $prefix => $namespace) {
            if (false === $this->domXpath->registerNamespace($prefix, $namespace)) {
                throw new \RuntimeException("Error while trying to register namespace [$namespace] with prefix [$prefix]");
            }
        }
    }

    public static function fromFile(string $filePath, array $namespaces = []): self
    {
        $domDocument = new \DOMDocument();

        try {
            if (false === \file_exists($filePath) || false === $domDocument->load($filePath)) {
                throw new \RuntimeException("Could not load xml file [$filePath].");
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return new self($domDocument, $namespaces);
    }

    public static function fromString(string $xmlStr, array $namespaces = []): self
    {
        $domDocument = new \DOMDocument();

        try {
            if (false === $domDocument->loadXML($xmlStr)) {
                throw new \RuntimeException("Could not load XML from string [$xmlStr]");
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return new self($domDocument, $namespaces);
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

    /**
     * @return \DOMElement | null
     */
    public function getFirstNode(string $xpath, \DOMNode $contextNode = null)
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if (0 !== $nodeList->length) {
            return $nodeList->item(0);
        }

        return null;
    }

    /**
     * @return \DOMElement | null
     */
    public function getLastNode(string $xpath, \DOMNode $contextNode = null)
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if (0 !== $nodeList->length) {
            $lastIndex = $nodeList->length - 1;

            return $nodeList->item($lastIndex);
        }

        return null;
    }

    /**
     * @return string | null
     */
    public function getNodeAttribute(string $att, \DOMElement $node = null)
    {
        if (null !== $node) {
            return $node->getAttribute($att);
        }

        return null;
    }

    /**
     * @return string | null
     */
    public function getNodeValue(\DOMElement $node = null)
    {
        if (null !== $node) {
            return $node->nodeValue;
        }

        return null;
    }

    /**
     * @return string | null
     */
    public function getValue(string $xpath, \DOMNode $contextNode = null)
    {
        return $this->getNodeValue($this->getFirstNode($xpath, $contextNode));
    }

    /**
     * @return string | null
     */
    public function getLastValue(string $xpath, \DOMNode $contextNode = null)
    {
        return $this->getNodeValue($this->getLastNode($xpath, $contextNode));
    }

    /**
     * @return string | null
     */
    public function getNeighborNodeValue(string $neighborNodeName, \DOMElement $node = null)
    {
        if (null !== $node) {
            return $this->getNodeValue(
                $node->parentNode->getElementsByTagName($neighborNodeName)->item(0)
            );
        }

        return null;
    }

    public function getValues(\DOMNodeList $contextNodes, string $keyNodeName, array $valNodes = [], \Closure $fn = null, array $fnParams = []): array
    {
        $values = [];

        foreach ($contextNodes as $node) {
            $keyNodeValue = $this->getValue($keyNodeName, $node);

            if (!\array_key_exists($keyNodeValue, $values)) {
                $values[$keyNodeValue] = [];
            }

            foreach ($valNodes as $valNodeName) {
                if (null !== $fn) {
                    $params = $fnParams;
                    \array_unshift($params, $this->getValue($valNodeName, $node));
                    $values[$keyNodeValue] = $fn($params);
                } else {
                    $values[$keyNodeValue] = $this->getValue($valNodeName, $node);
                }
            }
        }

        \ksort($values);

        return $values;
    }
}
