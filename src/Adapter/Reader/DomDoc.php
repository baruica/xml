<?php

namespace Baruica\Xml\Adapter\Reader;

use Baruica\Xml\Reader;

class DomDoc implements Reader
{
    /** @var \DOMXPath */
    private $domXpath;

    private function __construct()
    {
    }

    /**
     * @param string $filePath
     *
     * @return \Baruica\Xml\Reader
     * @throws \RuntimeException If xml from $filePath could not be loaded
     */
    public static function fromFile(string $filePath) : Reader
    {
        $xml = new DomDoc();

        try {
            $doc = new \DOMDocument();

            if (false === file_exists($filePath) || false === $doc->load($filePath)) {
                throw new \RuntimeException(sprintf('Could not load xml file [%s].', $filePath));
            }

            $xml->domXpath = new \DOMXPath($doc);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Could not load xml file [%s].', $filePath));
        }

        return $xml;
    }

    /**
     * @param string $xmlStr
     *
     * @return \Baruica\Xml\Reader
     * @throws \RuntimeException If xml from $xmlStr could not be loaded
     */
    public static function fromString(string $xmlStr) : Reader
    {
        $xml = new DomDoc();

        try {
            $doc = new \DOMDocument();

            if (false === $doc->loadXML($xmlStr)) {
                throw new \RuntimeException(sprintf('Could not load XML from string [%s]', $xmlStr));
            }

            $xml->domXpath = new \DOMXPath($doc);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Could not load XML from string [%s]', $xmlStr));
        }

        return $xml;
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

    /**
     * @param  string   $xpath
     * @param  \DOMNode $contextNode
     *
     * @return \DOMNode|null
     */
    public function getFirstNode(string $xpath, \DOMNode $contextNode = null) : \DOMNode
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if ($nodeList->length == 0) {
            return null;
        }

        return $nodeList->item(0);
    }

    /**
     * @param  string   $xpath
     * @param  \DOMNode $contextNode
     *
     * @return \DOMNode|null
     */
    public function getLastNode(string $xpath, \DOMNode $contextNode = null) : \DOMNode
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if ($nodeList->length == 0) {
            return null;
        }

        $lastIndex = $nodeList->length - 1;

        return $nodeList->item($lastIndex);
    }

    /**
     * @param  \DOMNode $node
     *
     * @return string|null
     */
    public function getNodeValue(\DOMNode $node = null) : string
    {
        if (null !== $node) {
            return $node->nodeValue;
        }

        return null;
    }

    /**
     * @param  string      $att
     * @param  \DOMElement $node
     *
     * @return string|null
     */
    public function getNodeAttribute(string $att, \DOMElement $node = null) : string
    {
        if (null !== $node) {
            return $node->getAttribute($att);
        }

        return null;
    }

    /**
     * @param  string   $neighborNodeName
     * @param  \DOMNode $node
     *
     * @return string|null
     */
    public function getNeighborNodeValue(string $neighborNodeName, \DOMNode $node = null) : string
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

    /**
     * @param  \DOMNodeList $contextNodes
     * @param  string       $keyNodeName
     * @param  array        $valNodes
     * @param  \Closure     $fn
     * @param  array        $fnParams
     *
     * @return array
     */
    public function getValues(\DOMNodeList $contextNodes, string $keyNodeName, array $valNodes = array(), \Closure $fn = null, array $fnParams = array()) : array
    {
        $values = array();

        foreach ($contextNodes as $node) {
            $keyNodeValue = $this->getValue($keyNodeName, $node);
            if (!isset($values[$keyNodeValue])) {
                $values[$keyNodeValue] = array();
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

    public function getList(string $xpath) : array
    {
        $list = array();

        if (null === $nodeList = $this->getNodeList($xpath)) {
            return $list;
        }

        foreach ($nodeList as $node) {
            $list[] = $this->getNodeValue($node);
        }

        return $list;
    }

    public function getListWithName(string $xpath) : array
    {
        $list = array();

        if (null === $nodeList = $this->getNodeList($xpath)) {
            return $list;
        }

        foreach ($nodeList as $key => $node) {
            foreach ($node->childNodes as $subNode) {
                $list[$key][$subNode->nodeName] = $this->getNodeValue($subNode);
            }
        }

        return $list;
    }
}
