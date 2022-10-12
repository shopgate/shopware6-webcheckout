<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Storefront\Events;

use Shopgate\WebcheckoutSW6\Storefront\Page\GenericPage;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

class GenericPageLoadedEvent extends PageLoadedEvent
{
    protected GenericPage $page;

    public function __construct(GenericPage $page, SalesChannelContext $salesChannelContext, Request $request)
    {
        $this->page = $page;
        parent::__construct($salesChannelContext, $request);
    }

    /**
     * @inheritDoc
     */
    public function getPage(): GenericPage
    {
        return $this->page;
    }
}
