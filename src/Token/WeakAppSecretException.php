<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Token;

use Shopware\Core\Framework\ShopwareHttpException;

class WeakAppSecretException extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('App secret is too weak');
    }

    public function getErrorCode(): string
    {
        return 'SGCONNECT__WEAK_APP_SECRET';
    }
}
