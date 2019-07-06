<?php

namespace Freyo\WeChatMiniProgram\Utils;

/**
 * Class RichTextParser
 * @package Freyo\WeChatMiniProgram\Utils
 */
class RichTextParser
{
    /**
     * @var \DOMDocument
     */
    protected $DOMDocument;

    /**
     * RichTextParser constructor.
     *
     * @param $HTML
     */
    public function __construct($HTML)
    {
        $this->DOMDocument = new \DOMDocument();
        $this->DOMDocument->preserveWhiteSpace = false;
        $this->DOMDocument->strictErrorChecking = false;
        $this->DOMDocument->loadHTML($this->getXMLEncoding() . $HTML);
    }

    /**
     * @param $HTML
     *
     * @return RichTextParser
     */
    public static function loadHTML($HTML)
    {
        return new self($HTML);
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $body = $this->DOMDocument->getElementsByTagName('body')[0];

        return $body ? $this->getNodes($body->childNodes) : [];
    }

    /**
     * @return string
     */
    protected function getXMLEncoding()
    {
        return '<?xml encoding="utf-8" ?>';
    }

    /**
     * @param $childNodes
     *
     * @return array
     */
    protected function getNodes($childNodes)
    {
        $nodes = [];
        foreach ($childNodes as $childNode) {
            $nodes[] = $this->isTextNode($childNode)
                ? $this->textNode($childNode)
                : $this->elementNode($childNode);
        }
        return $nodes;
    }

    /**
     * @param $attributes
     *
     * @return array
     */
    protected function getAttrs($attributes)
    {
        $attrs = [];
        foreach ($attributes as $attribute) {
            $attrs[$attribute->name] = $attribute->value;
        }
        return $attrs;
    }

    /**
     * @param $childNode
     *
     * @return bool
     */
    protected function isTextNode($childNode)
    {
        return $childNode->nodeName === '#text';
    }

    /**
     * @param $childNode
     *
     * @return array
     */
    protected function textNode($childNode)
    {
        return [
            'text' => $childNode->textContent,
            'type' => 'text',
        ];
    }

    /**
     * @param $childNode
     *
     * @return array
     */
    protected function elementNode($childNode)
    {
        return [
            'name' => $childNode->nodeName,
            'attrs' => $childNode->attributes
                ? $this->getAttrs($childNode->attributes)
                : [],
            'children' => $childNode->childNodes
                ? $this->getNodes($childNode->childNodes)
                : [],
        ];
    }
}
