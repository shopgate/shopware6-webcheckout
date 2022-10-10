<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Services;

use DateTimeImmutable;
use Exception;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLogoutRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Shopware\Core\Framework\Routing\SalesChannelRequestContextResolver;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CustomerManager
{
    private SalesChannelContextRestorer $contextRestorer;
    private EventDispatcherInterface $dispatcher;
    private EntityRepositoryInterface $customerRepository;
    private SalesChannelRequestContextResolver $contextResolver;
    private AbstractLogoutRoute $logoutRoute;
    private SalesChannelContextPersister $contextPersist;

    public function __construct(
        SalesChannelContextRestorer $contextRestorer,
        EventDispatcherInterface $dispatcher,
        EntityRepositoryInterface $customerRepository,
        SalesChannelContextPersister $contextPersist,
        SalesChannelRequestContextResolver $contextResolver,
        AbstractLogoutRoute $logoutRoute
    ) {
        $this->contextRestorer = $contextRestorer;
        $this->dispatcher = $dispatcher;
        $this->customerRepository = $customerRepository;
        $this->contextPersist = $contextPersist;
        $this->contextResolver = $contextResolver;
        $this->logoutRoute = $logoutRoute;
    }

    public function loginByContextToken(
        string $contextToken,
        Request $request,
        SalesChannelContext $context
    ): SalesChannelContext {
        // sometimes the guest already has a frontend session started
        if ($context->getToken() === $contextToken) {
            return $context;
        }

        $this->contextResolver->handleSalesChannelContext($request, $context->getSalesChannelId(), $contextToken);
        $newContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);

        $this->dispatcher->dispatch(new SalesChannelContextResolvedEvent($newContext, $context->getToken()));

        return $newContext;
    }

    public function loginCustomerById(string $customerId, SalesChannelContext $context): SalesChannelContext
    {
        $this->extendCustomerTokenLife($context->getToken(), $context->getSalesChannelId(), $customerId);
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

    /**
     * @return Exception[]|ConstraintViolationException[]
     */
    public function logoutCustomer(SalesChannelContext $context, RequestDataBag $dataBag): array
    {
        try {
            $this->logoutRoute->logout($context, $dataBag);
        } catch (ConstraintViolationException $formViolations) {
            return ['formViolations' => $formViolations];
        }

        return [];
    }

    /**
     * Since it will attempt to create a new token & syncing it with the App requires
     * the user to log back in, we try to extend the token life if it expired.
     */
    public function extendCustomerTokenLife(string $token, string $channelId, ?string $customerId = null): void
    {
        $customerPayload = $this->contextPersist->load($token, $channelId, $customerId);

        if ($customerPayload['expired'] ?? null) {
            $newToken = $customerPayload['token'] ?? $token;
            $newCustomerId = $customerId ?: $customerPayload['customerId'] ?? null;
            $this->contextPersist->save($newToken, ['expired' => false], $channelId, $newCustomerId);
        }
    }
}
