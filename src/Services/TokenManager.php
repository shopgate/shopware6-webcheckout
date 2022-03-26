<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Services;

use ReallySimpleJWT\Tokens;

class TokenManager
{
    private string $secret;
    private Tokens $tokens;

    public function __construct(Tokens $tokens, string $secret)
    {
        $this->secret = $secret;
        $this->tokens = $tokens;
    }

    public function validateToken(string $token): bool
    {
        return $this->tokens->validateExpiration($token, $this->secret);
    }

    public function getCustomerId(string $token): ?string
    {
        $payload = $this->tokens->getPayload($token, $this->secret);

        return $payload['user_id'] ?? null;
    }

    public function createToken(string $customerId, string $domain): array
    {
        $expiration = time() + 60;
        return [
            'token' => $this->tokens->create('user_id', $customerId, $this->secret, $expiration, $domain)->getToken(),
            'expiration' => $expiration
        ];
    }
}
