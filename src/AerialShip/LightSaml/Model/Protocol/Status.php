<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Protocol;


class Status implements GetXmlInterface, LoadFromXmlInterface
{

    /** @var  StatusCode */
    protected $statusCode;

    /** @var string */
    protected $message;


    /**
     * @param StatusCode|null $statusCode
     * @param string $message
     */
    public function __construct(StatusCode $statusCode = null, $message = null)
    {
        $this->statusCode = $statusCode;
        $this->message = $message;
    }

    /**
     * @param \AerialShip\LightSaml\Model\Protocol\StatusCode $statusCode
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Protocol\StatusCode
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * @param string $message
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }



    public function isSuccess() {
        $result = $this->getStatusCode() && $this->getStatusCode()->getValue() == Protocol::STATUS_SUCCESS;
        return $result;
    }


    public function setSuccess() {
        $this->setStatusCode(new StatusCode());
        $this->getStatusCode()->setValue(Protocol::STATUS_SUCCESS);
    }



    protected function prepareForXml() {
        if (!$this->getStatusCode()) {
            throw new InvalidXmlException('StatusCode not set');
        }
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $this->prepareForXml();

        $result = $context->getDocument()->createElementNS(Protocol::SAML2, 'samlp:Status');
        $parent->appendChild($result);

        $result->appendChild($this->getStatusCode()->getXml($result, $context));

        if ($this->getMessage()) {
            $statusMessageNode = $context->getDocument()->createElementNS(Protocol::SAML2, 'samlp:StatusMessage', $this->getMessage());
            $result->appendChild($statusMessageNode);
        }

        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'Status' || $xml->namespaceURI != Protocol::SAML2) {
            throw new InvalidXmlException('Expected Status element but got '.$xml->localName);
        }
        $current = $this;
        $this->iterateChildrenElements($xml, function(\DOMElement $node) use ($current) {
            if ($node->localName == 'StatusCode' && $node->namespaceURI == Protocol::SAML2) {
                $statusCode = new StatusCode();
                $statusCode->loadFromXml($node);
                $current->setStatusCode($statusCode);
            } else if ($node->localName == 'StatusMessage' && $node->namespaceURI == Protocol::SAML2) {
                $current->setMessage($node->textContent);
            }
        });

        if (!$this->getStatusCode()) {
            throw new InvalidXmlException('Missing StatusCode node');
        }
    }

    public function iterateChildrenElements(\DOMElement $xml, \Closure $elementCallback) {
      return XmlChildrenLoaderTrait::iterateChildrenElements($xml, $elementCallback);
    }
    
    public function loadXmlChildren(\DOMElement $xml, array $node2ClassMap, \Closure $itemCallback) {
      return XmlChildrenLoaderTrait::loadXmlChildren($xml, $node2ClassMap, $itemCallback, $this);
    }
    
    public function doMapping(\DOMElement $node, array $node2ClassMap, \Closure $itemCallback) {
      return XmlChildrenLoaderTrait::doMapping($node, $node2ClassMap, $itemCallback, $this);
    }
    
    public function getNodeNameAndNamespaceFromMeta($meta, &$nodeName, &$nodeNS) {
      return XmlChildrenLoaderTrait::getNodeNameAndNamespaceFromMeta($meta, $nodeName, $nodeNS);
    }
    
    public function getObjectFromMetaClass($meta, \DOMElement $node) {
      return XmlChildrenLoaderTrait::getObjectFromMetaClass($meta, $node);
    }
}
