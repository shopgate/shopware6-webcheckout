<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Subscribers;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\ThemeCompilerConcatenatedStylesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SystemConfigSubscriber implements EventSubscriberInterface
{
    private SystemConfigService $configService;
    private string $configKey;

    public function __construct(SystemConfigService $configService, string $cssConfigKey)
    {
        $this->configService = $configService;
        $this->configKey = $cssConfigKey;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ThemeCompilerConcatenatedStylesEvent::class => 'addCssToCompiler'
        ];
    }

    public function addCssToCompiler(ThemeCompilerConcatenatedStylesEvent $event): void
    {
        $css = $this->configService->getString($this->configKey, $event->getSalesChannelId());
        if (!empty($css)) {
            $event->setConcatenatedStyles($event->getConcatenatedStyles() . "\n$css\n");
        }
    }
}
