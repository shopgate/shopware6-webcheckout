<?php

declare(strict_types=1);

namespace Shopgate\ConnectSW6\Subscribers;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\ThemeCompilerConcatenatedStylesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SystemConfigSubscriber implements EventSubscriberInterface
{
    private SystemConfigService $configService;

    public function __construct(SystemConfigService $configService)
    {
        $this->configService = $configService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ThemeCompilerConcatenatedStylesEvent::class => 'addCssToCompiler'
        ];
    }

    public function addCssToCompiler(ThemeCompilerConcatenatedStylesEvent $event): void
    {
        $css = $this->configService->getString('ShopgateConnectSW6.config.css', $event->getSalesChannelId());
        if (empty($css)) {
            $css = $this->configService->getString('ShopgateConnectSW6.config.css');
        }
        $event->setConcatenatedStyles($event->getConcatenatedStyles() . "\n$css\n");
    }
}
