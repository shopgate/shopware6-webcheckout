<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Services;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;

class SeoUrlEntityResolver
{
    private const PRODUCT_ROUTE_NAME = 'frontend.detail.page';
    private const CATEGORY_ROUTE_NAME = 'frontend.navigation.page';

    public function __construct(
        private readonly EntityRepository $seoUrlRepository,
        private readonly SalesChannelRepository $salesChannelProductRepository,
        private readonly SalesChannelRepository $salesChannelCategoryRepository
    ) {
    }

    public function resolveProductBySeoPath(string $seoPath, SalesChannelContext $salesChannelContext): ?ProductEntity
    {
        $productId = $this->resolveForeignKey($seoPath, self::PRODUCT_ROUTE_NAME, $salesChannelContext);
        if ($productId === null) {
            return null;
        }

        $criteria = new Criteria([$productId]);
        $result = $this->salesChannelProductRepository->search($criteria, $salesChannelContext);
        $product = $result->first();

        return $product instanceof ProductEntity ? $product : null;
    }

    public function resolveCategoryBySeoPath(string $seoPath, SalesChannelContext $salesChannelContext): ?CategoryEntity
    {
        $categoryId = $this->resolveForeignKey($seoPath, self::CATEGORY_ROUTE_NAME, $salesChannelContext);
        if ($categoryId === null) {
            return null;
        }

        $criteria = new Criteria([$categoryId]);
        $result = $this->salesChannelCategoryRepository->search($criteria, $salesChannelContext);
        $category = $result->first();

        return $category instanceof CategoryEntity ? $category : null;
    }

    private function resolveForeignKey(string $seoPath, string $routeName, SalesChannelContext $salesChannelContext): ?string
    {
        $candidates = $this->buildSeoPathCandidates($seoPath);
        if ($candidates === []) {
            return null;
        }

        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsAnyFilter('seoPathInfo', $candidates));
        $criteria->addFilter(new EqualsFilter('routeName', $routeName));
        $criteria->addFilter(new EqualsFilter('isCanonical', true));
        $criteria->addFilter(new EqualsFilter('isDeleted', false));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannelId()));
        $criteria->addFilter(new EqualsFilter('languageId', $salesChannelContext->getContext()->getLanguageId()));

        $seoUrl = $this->seoUrlRepository->search($criteria, $salesChannelContext->getContext())->first();
        if ($seoUrl !== null && method_exists($seoUrl, 'getForeignKey')) {
            return $seoUrl->getForeignKey();
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function buildSeoPathCandidates(string $seoPath): array
    {
        $normalized = trim($seoPath, '/');
        if ($normalized === '') {
            return [];
        }

        return array_values(array_unique([
            $normalized,
            $normalized . '/'
        ]));
    }
}
