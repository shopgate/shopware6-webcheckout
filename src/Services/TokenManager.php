<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Services;

use ReallySimpleJWT\Exception\BuildException;
use ReallySimpleJWT\Exception\EncodeException;
use ReallySimpleJWT\Exception\JwtException;
use Shopgate\WebcheckoutSW6\Token\TokenBuilder;

class TokenManager
{
    public function __construct(
        private readonly TokenBuilder $tokens,
        private readonly string $secret
    ) {
    }

    public function validateToken(string $token): bool
    {
        try {
            $validation = $this->tokens->validateExpiration($token);
        } catch (JwtException) {
            return false;
        }
        return $validation;
    }

    public function getCustomerId(string $token): ?string
    {
        return $this->tokens->getPayload($token)['user-id'] ?? null;
    }

    public function getContextToken(string $token): ?string
    {
        return $this->tokens->getPayload($token)['sw-context-token'] ?? null;
    }

    /**
     * @throws BuildException|EncodeException
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
