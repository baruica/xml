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
     * @throws \Exception If xml from $filePath could not be loaded
     */
    public static function fromFile($filePath)
    {
        $xml = new DomDoc();

        try {
            $doc = new \DOMDocument();

            if (false === file_exists($filePath) || false === $doc->load($filePath)) {
                throw new \Exception(sprintf('Could not load xml file [%s].', $filePath));
            }

            $xml->domXpath = new \DOMXPath($doc);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Could not load xml file [%s].', $filePath));
        }

        return $xml;
    }

    /**
     * @param string $xmlStr
     *
     * @return \Baruica\Xml\Reader
     * @throws \Exception If xml from $xmlStr could not be loaded
     */
    public static function fromString($xmlStr)
    {
        $xml = new DomDoc();

        try {
            $doc = new \DOMDocument();

            if (false === $doc->loadXML($xmlStr)) {
                throw new \Exception(sprintf('Could not load XML from string [%s]', $xmlStr));
            }

            $xml->domXpath = new \DOMXPath($doc);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Could not load XML from string [%s]', $xmlStr));
        }

        return $xml;
    }

    /**
     * @param  string   $xpath
     * @param  \DOMNode $contextNode
     *
     * @return \DOMNodeList
     */
    public function getNodeList($xpath, \DOMNode $contextNode = null)
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
    public function getFirstNode($xpath, \DOMNode $contextNode = null)
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
    public function getLastNode($xpath, \DOMNode $contextNode = null)
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
    public function getNodeValue(\DOMNode $node = null)
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
     * @return string
     */
    public function getNodeAttribute($att, \DOMElement $node = null)
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
    public function getNeighborNodeValue($neighborNodeName, \DOMNode $node = null)
    {
        if (null !== $node) {
            return $this->getNodeValue(
                $node->parentNode->getElementsByTagName($neighborNodeName)->item(0)
            );
        }

        return null;
    }

    /**
     * @param  string   $xpath
     * @param  \DOMNode $contextNode
     *
     * @return string
     */
    public function getValue($xpath, \DOMNode $contextNode = null)
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
    public function getValues(\DOMNodeList $contextNodes, $keyNodeName, array $valNodes = array(), \Closure $fn = null, array $fnParams = array())
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

    /**
     * @param  string   $xpath
     * @param  \DOMNode $contextNode
     *
     * @return string
     */
    public function getLastValue($xpath, \DOMNode $contextNode = null)
    {
        return $this->getNodeValue($this->getLastNode($xpath, $contextNode));
    }

    /**
     * @param  string $xpath
     *
     * @return array
     */
    public function getList($xpath)
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

    /**
     * @param  string $xpath
     *
     * @return array
     */
    public function getListWithName($xpath)
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
