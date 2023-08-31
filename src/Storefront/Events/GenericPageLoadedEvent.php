<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Storefront\Events;

use Shopgate\WebcheckoutSW6\Storefront\Page\GenericPage;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

class GenericPageLoadedEvent extends PageLoadedEvent
{
    public function __construct(
        protected GenericPage $page,
        SalesChannelContext $salesChannelContext,
        Request $request
    ) {
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): GenericPage
    {
        return $this->page;
    }
}
