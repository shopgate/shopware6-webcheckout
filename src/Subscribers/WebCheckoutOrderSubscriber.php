<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Subscribers;

use Shopgate\WebcheckoutSW6\Entity\OrderEntity;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class WebCheckoutOrderSubscriber implements EventSubscriberInterface
{
    use ShopgateDetectTrait;

    private EntityRepositoryInterface $entityRepository;
    private RequestStack $stack;

    public function __construct(RequestStack $stack, EntityRepositoryInterface $entityRepository)
    {
        $this->entityRepository = $entityRepository;
        $this->stack = $stack;
    }

    public static function getSubscribedEvents(): array
    {
        return [CheckoutOrderPlacedEvent::class => ['handleOrder', 40]];
    }

    public function handleOrder(CheckoutOrderPlacedEvent $event): void
    {
        if (!$this->isShopgate($this->stack->getCurrentRequest())) {
            return;
        }
        $this->entityRepository->create([
            (new OrderEntity())
                ->setShopwareOrderId($event->getOrderId())
                ->setUserAgent((string)$this->stack->getCurrentRequest()->headers->get('User-Agent', ''))
                ->toArray()
        ], $event->getContext());
    }
}
