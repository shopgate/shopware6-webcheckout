<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Services;

use ReallySimpleJWT\Secret;
use ReallySimpleJWT\Token;

class TokenManager
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function isValidSecret(): bool
    {
        return (new Secret())->validate($this->secret);
    }

    public function createToken(string $customerId, string $domain): string
    {
        return Token::create($customerId, $this->secret, time() + 60, $domain);
    }
}
