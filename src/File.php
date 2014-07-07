<?php

namespace Baruica\XML;

class File
{
    protected $domXpath;

    /**
     * @param string $xmlFilePath
     *
     * @throws \Exception If xml file could not be loaded
     */
    public function __construct($xmlFilePath)
    {
        try {
            $doc = new \DOMDocument();

            if (true === file_exists($xmlFilePath)) {
                $doc->load($xmlFilePath);
            } else {
                $doc->loadXML($xmlFilePath);
            }

            $this->domXpath = new \DOMXPath($doc);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Could not load xml file [%s].', $xmlFilePath));
        }
    }


    public function getNodeList($xpath, \DOMNode $contextNode = null)
    {
        $valNL = (null === $contextNode)
               ? $this->domXpath->query($xpath)
               : $this->domXpath->query($xpath, $contextNode);

        if (false === $valNL) {
            return null;
        }

        return $valNL;
    }

    public function getFirstNode($xpath, \DOMNode $contextNode = null)
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if ($nodeList->length == 0) {
            return null;
        }

        return $nodeList->item(0);
    }

    public function getLastNode($xpath, \DOMNode $contextNode = null)
    {
        $nodeList = $this->getNodeList($xpath, $contextNode);

        if ($nodeList->length == 0) {
            return null;
        }

        $lastIndex = $nodeList->length - 1;

        return $nodeList->item($lastIndex);
    }

    public function getNodeValue(\DOMNode $node = null)
    {
        if (null !== $node) {
            return $node->nodeValue;
        }

        return null;
    }

    public function getNodeAttribute($att, \DOMNode $node = null)
    {
        if (null !== $node) {
            return $node->getAttribute($att);
        }

        return null;
    }

    public function getNeighborNodeValue($neighborNodeName, \DOMNode $node = null)
    {
        if (null !== $node) {
            return $this->getNodeValue(
                $node->parentNode->getElementsByTagName($neighborNodeName)->item(0)
            );
        }

        return null;
    }

    public function getValue($xpath, \DOMNode $contextNode = null)
    {
        return $this->getNodeValue($this->getFirstNode($xpath, $contextNode));
    }

    public function getValues(\DOMNodeList $contextNodes, $keyNodeName, array $valNodes = null, \Closure $fn = null, array $fnParams = null)
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

    public function getLastValue($xpath, \DOMNode $contextNode = null)
    {
        return $this->getNodeValue($this->getLastNode($xpath, $contextNode));
    }

    public function getList($xpath)
    {
        $list = array();

        foreach ($this->getNodeList($xpath) as $node) {
            $list[] = $this->getNodeValue($node);
        }

        return $list;
    }

    public function getListWithName($xpath)
    {
        $list = array();

        foreach ($this->getNodeList($xpath) as $key => $node) {
            foreach ($node->childNodes as $subNode) {
                $list[$key][$subNode->nodeName] = $this->getNodeValue($subNode);
            }
        }

        return $list;
    }
}
