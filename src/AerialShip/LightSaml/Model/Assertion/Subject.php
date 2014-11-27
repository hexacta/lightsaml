<?php

namespace AerialShip\LightSaml\Model\Assertion;

use AerialShip\LightSaml\Error\InvalidSubjectException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Protocol;


class Subject implements GetXmlInterface, LoadFromXmlInterface
{

    /** @var NameID */
    protected $nameID;

    /** @var SubjectConfirmation[] */
    protected $subjectConfirmations = array();


    

    /**
     * @param NameID $nameID
     */
    public function setNameID(NameID $nameID)
    {
        $this->nameID = $nameID;
    }

    /**
     * @return NameID
     */
    public function getNameID()
    {
        return $this->nameID;
    }

    /**
     * @param SubjectConfirmation $subjectConfirmation
     */
    public function addSubjectConfirmation($subjectConfirmation)
    {
        $this->subjectConfirmations[] = $subjectConfirmation;
    }

    /**
     * @return SubjectConfirmation[]
     */
    public function getSubjectConfirmations()
    {
        return $this->subjectConfirmations;
    }





    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    public function getXml(\DOMNode $parent, SerializationContext $context)
    {
        $result = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:Subject');
        $parent->appendChild($result);

        if ($this->getNameID()) {
            $this->getNameID()->getXml($result, $context);
        }

        foreach ($this->getSubjectConfirmations() as $sc) {
            $sc->getXml($result, $context);
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \LogicException
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    public function loadFromXml(\DOMElement $xml)
    {
        if ($xml->localName != 'Subject' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected Subject element but got '.$xml->localName);
        }

        $this->nameID = null;
        $this->subjectConfirmations = array();
        $current = $this;
        $this->loadXmlChildren(
            $xml,
            array(
                array(
                    'node' => array('name'=>'NameID', 'ns'=>Protocol::NS_ASSERTION),
                    'class' => '\AerialShip\LightSaml\Model\Assertion\NameID'
                ),
                array(
                    'node' => array('name'=>'SubjectConfirmation', 'ns'=>Protocol::NS_ASSERTION),
                    'class' => '\AerialShip\LightSaml\Model\Assertion\SubjectConfirmation'
                )
            ),
            function ($object) use ($current){
                $current->loadXmlCallback($object);
            }
        );
        if (!$this->getSubjectConfirmations()) {
            throw new InvalidXmlException('Missing SubjectConfirmation element in Subject');
        }
    }


    public function loadXmlCallback($object)
    {
        if ($object instanceof NameID) {
            if ($this->getNameID()) {
                throw new InvalidXmlException('More than one NameID in Subject');
            }
            $this->setNameID($object);
        } else if ($object instanceof SubjectConfirmation) {
            $this->addSubjectConfirmation($object);
        } else {
            throw new \LogicException('Unexpected type '.get_class($object));
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
