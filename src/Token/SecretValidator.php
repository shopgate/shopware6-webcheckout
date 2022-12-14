<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Token;

use ReallySimpleJWT\Interfaces\Secret as SecretInterface;

class SecretValidator implements SecretInterface
{
    /**
     * Simplified validation to 8+ characters, must have upper or lower case character
     * @inheritDoc
     */
    public function validate(string $secret): bool
    {
        return (bool)preg_match('/^.*(?=.{8,}+)(?=.*[A-Za-z]+).*$/', $secret);
    }
}
