<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Services;

use ReallySimpleJWT\Exception\BuildException;
use Shopgate\WebcheckoutSW6\Token\TokenBuilder;

class TokenManager
{
    private string $secret;
    private TokenBuilder $tokens;

    public function __construct(TokenBuilder $tokens, string $secret)
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

        return $payload['user-id'] ?? null;
    }

    public function getContextToken(string $token): ?string
    {
        $payload = $this->tokens->getPayload($token, $this->secret);

        return $payload['sw-context-token'] ?? null;
    }

    /**
     * @throws BuildException
     */
    public function createToken(string $swContextToken, string $domain, ?string $customerId): array
    {
        $expiration = time() + 60;
        return [
            'token' => $this->tokens->createCustomPayload($this->secret, $expiration, $domain, [
                'user-id' => $customerId,
                'sw-context-token' => $swContextToken
            ])->getToken(),
            'expiration' => $expiration
        ];
    }
}
