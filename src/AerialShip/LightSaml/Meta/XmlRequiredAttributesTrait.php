<?php

namespace AerialShip\LightSaml\Meta;

use AerialShip\LightSaml\Error\InvalidXmlException;


abstract class XmlRequiredAttributesTrait
{
    public static function checkRequiredAttributes(\DOMElement $element, array $attributes) {
        foreach ($attributes as $name) {
            if (!$element->hasAttribute($name)) {
                throw new InvalidXmlException('XML Element '.$element->localName.' missing required attribute '.$name);
            }
        }
    }
}