<?php

namespace Shopgate\ConnectSW6\Token;

use ReallySimpleJWT\Build;
use ReallySimpleJWT\Encoders\EncodeHS256;
use ReallySimpleJWT\Helper\Validator;
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
}
