<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Services;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Exception;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLogoutRoute;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Shopware\Core\Framework\Routing\SalesChannelRequestContextResolver;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Context\CartRestorer;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CustomerManager
{
    public function __construct(
        private readonly CartRestorer $cartRestorer,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityRepository $customerRepository,
        private readonly SalesChannelContextPersister $contextPersist,
        private readonly SalesChannelRequestContextResolver $contextResolver,
        private readonly AbstractLogoutRoute $logoutRoute,
        private readonly Connection $connection
    ) {
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
        $newContext = $this->cartRestorer->restore($customerId, $context);
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
     * @return string[]|ConstraintViolationException[]
     */
    public function logoutCustomer(SalesChannelContext $context, RequestDataBag $dataBag): array
    {
        try {
            return ['token' => $this->logoutRoute->logout($context, $dataBag)->getToken()];
        } catch (ConstraintViolationException $formViolations) {
            return ['formViolations' => $formViolations];
        }
    }

    /**
     * Since it will attempt to create a new token & syncing it with the App requires
     * the user to log back in, we try to extend the token life if it expired.
     */
    public function extendCustomerTokenLife(string $token, string $channelId, ?string $customerId = null): void
    {
        $customerPayload = $this->contextPersist->load($token, $channelId, $customerId);
        if ($customerPayload['expired'] ?? false) {
            try {
                $this->connection->executeStatement(
                    'UPDATE `sales_channel_api_context`
                       SET `updated_at` = :updatedAt
                       WHERE `token` = :token',
                    [
                        'token' => $customerPayload['token'] ?? $token,
                        'updatedAt' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );
            } catch (\Doctrine\DBAL\Exception $e) {
            }
        }
    }
}
