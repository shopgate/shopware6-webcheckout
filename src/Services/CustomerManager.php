<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Services;

use DateTimeImmutable;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CustomerManager
{
    private SalesChannelContextRestorer $contextRestorer;
    private EventDispatcherInterface $dispatcher;
    private EntityRepositoryInterface $customerRepository;

    public function __construct(
        SalesChannelContextRestorer $contextRestorer,
        EventDispatcherInterface $dispatcher,
        EntityRepositoryInterface $customerRepository
    ) {
        $this->contextRestorer = $contextRestorer;
        $this->dispatcher = $dispatcher;
        $this->customerRepository = $customerRepository;
    }

    public function loginCustomerById(string $customerId, SalesChannelContext $context): SalesChannelContext
    {
        $newContext = $this->contextRestorer->restore($customerId, $context);
        $this->customerRepository->update([
            [
                'id' => $customerId,
                'lastLogin' => new DateTimeImmutable(),
            ],
        ], $context->getContext());

        $event = new CustomerLoginEvent($context, $newContext->getCustomer(), $newContext->getToken());
        $this->dispatcher->dispatch($event);

        return $newContext;
    }
}
