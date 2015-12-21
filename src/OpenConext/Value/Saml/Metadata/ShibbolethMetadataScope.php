<?php

namespace OpenConext\Value\Saml\Metadata;

use OpenConext\Value\Exception\InvalidArgumentException;
use OpenConext\Value\RegularExpression;

final class ShibbolethMetadataScope
{
    /**
     * @var string|null
     */
    private $literal;

    /**
     * @var RegularExpression|null
     */
    private $regexp;

    /**
     * @param string $literal
     * @return ShibbolethMetadataScope
     */
    public static function literal($literal)
    {
        if (!is_string($literal) || trim($literal) === '') {
            throw InvalidArgumentException::invalidType('not-blank string', 'literal', $literal);
        }

        $scope          = new ShibbolethMetadataScope();
        $scope->literal = $literal;

        return $scope;
    }

    /**
     * @param string $regexp
     * @return ShibbolethMetadataScope
     */
    public static function regexp($regexp)
    {
        if (!is_string($regexp) || trim($regexp) === '') {
            throw InvalidArgumentException::invalidType('non-blank string', 'regexp', $regexp);
        }

        $scope         = new ShibbolethMetadataScope();
        $scope->regexp = new RegularExpression($regexp);

        return $scope;
    }

    private function __construct()
    {
    }

    /**
     * @param string $string
     * @return bool
     */
    public function allows($string)
    {
        if (!is_string($string)) {
            throw InvalidArgumentException::invalidType('string', 'string', $string);
        }

        if ($this->literal !== null) {
            return $this->literal === $string;
        }

        return $this->regexp->matches($string);
    }

    /**
     * @param ShibbolethMetadataScope $other
     * @return bool
     */
    public function equals(ShibbolethMetadataScope $other)
    {
        return ($this->literal !== null && $this->literal === $other->literal)
                || ($this->regexp && $other->regexp && $this->regexp->equals($other->regexp));
    }

    public function __toString()
    {
        if ($this->literal !== null) {
            return sprintf('ShibbolethMetadataScope(literal=%s)', $this->literal);
        } else {
            return sprintf('ShibbolethMetadataScope(regexp=%s)', $this->regexp);
        }
    }
}
