<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Token;

use ReallySimpleJWT\Build;
use ReallySimpleJWT\Encoders\EncodeHS256;
use ReallySimpleJWT\Exception\BuildException;
use ReallySimpleJWT\Helper\Validator;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Tokens;

class TokenBuilder extends Tokens
{
    public function builder(): Build
    {
        return new Build(
            'JWT',
            new Validator(),
            new SecretValidator(),
            new EncodeHS256()
        );
    }

    /**
     * @throws BuildException
     */
    public function createCustomPayload(string $secret, int $expiration, string $issuer, array $payload): Jwt
    {
        $builder = $this->builder()
            ->setSecret($secret)
            ->setExpiration($expiration)
            ->setIssuer($issuer)
            ->setIssuedAt(time());

        foreach ($payload as $key => $value) {
            $builder->setPayloadClaim($key, $value);
        }

        return $builder->build();
    }
}
