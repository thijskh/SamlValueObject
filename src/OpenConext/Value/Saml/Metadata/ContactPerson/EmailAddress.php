<?php

namespace OpenConext\Value\Saml\Metadata\ContactPerson;

use OpenConext\Value\Exception\InvalidArgumentException;

final class EmailAddress
{
    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @param string $emailAddress RFC 822 compliant email address
     */
    public function __construct($emailAddress)
    {
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw InvalidArgumentException::invalidType(
                'RFC 822 compliant email address',
                'emailAddress',
                $emailAddress
            );
        }

        $this->emailAddress = $emailAddress;
    }

    /**
     * @param EmailAddress $other
     * @return bool
     */
    public function equals(EmailAddress $other)
    {
        return $this->emailAddress === $other->emailAddress;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function __toString()
    {
        return $this->emailAddress;
    }
}
