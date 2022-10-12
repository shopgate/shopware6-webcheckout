<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Storefront\Page;

use Shopgate\WebcheckoutSW6\Storefront\Events\GenericPageLoadedEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class GenericPageLoader
{
    private GenericPageLoaderInterface $genericPageLoader;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        GenericPageLoaderInterface $genericPageLoader,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->genericPageLoader = $genericPageLoader;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function load(Request $request, SalesChannelContext $context): GenericPage
    {
        $page = $this->genericPageLoader->load($request, $context);
        /** @var GenericPage $page */
        $page = GenericPage::createFrom($page);

        $this->eventDispatcher->dispatch(new GenericPageLoadedEvent($page, $context, $request));

        return $page;
    }
}
