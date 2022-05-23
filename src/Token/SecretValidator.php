<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Token;

use ReallySimpleJWT\Interfaces\Secret as SecretInterface;

class SecretValidator implements SecretInterface
{
    /**
     * Simplified validation to 8+ characters, must have a digit and upper or lower case character
     * @inheritDoc
     */
    public function validate(string $secret): bool
    {
        return (bool)preg_match('/^.*(?=.{8,}+)(?=.*\d+)(?=.*[A-Za-z]+).*$/', $secret);
    }
}
