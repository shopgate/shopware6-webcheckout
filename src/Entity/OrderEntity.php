<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class OrderEntity extends Entity
{
    use EntityIdTrait;

    /**
     * Keep public as assigner does not use methods, e.g. $this->$key = value
     */
    public string $shopwareOrderId;
    public string $userAgent;

    /**
     * @return string
     */
    public function getShopwareOrderId(): string
    {
        return $this->shopwareOrderId;
    }

    /**
     * @param string $shopwareOrderId
     * @return self
     */
    public function setShopwareOrderId(string $shopwareOrderId): self
    {
        $this->shopwareOrderId = $shopwareOrderId;

        return $this;
    }

    /**
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function toArray(): array
    {
        return [
            'shopwareOrderId' => $this->shopwareOrderId,
            'userAgent' => $this->userAgent,
        ];
    }
}
