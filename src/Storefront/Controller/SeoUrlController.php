<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Storefront\Controller;

use Shopgate\WebcheckoutSW6\Services\SeoUrlEntityResolver;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class SeoUrlController extends StorefrontController
{
    public function __construct(
        private readonly SeoUrlEntityResolver $seoUrlEntityResolver
    ) {
    }

    /**
     * This endpoint allows resolving a product based on a given SEO URL path.
     * The endpoints expects a query parameter named 'url' containing the SEO URL path to be resolved.
     * If a matching product is found for the provided SEO URL path, it returns the product data as a JSON response; otherwise, it throws a NotFoundHttpException.
     */
    #[Route(path: '/store-api/sgwebcheckout/product/by-seo-url', name: 'store-api.sgwebcheckout.product.by-seo-url', defaults: [
        '_routeScope' => ['store-api'],
        '_contextTokenRequired' => false
    ], methods: [
        'GET',
        'POST'
    ])]
    public function productBySeoUrl(Request $request, SalesChannelContext $context): JsonResponse
    {
        $seoPath = $this->extractSeoPath($request);
        $product = $this->seoUrlEntityResolver->resolveProductBySeoPath($seoPath, $context);
        if ($product === null) {
            throw new NotFoundHttpException('No matching product was found.');
        }

        return new JsonResponse($product);
    }

    /**
     * This endpoint allows resolving a category based on a given SEO URL path.
     * The endpoints expects a query parameter named 'url' containing the SEO URL path to be resolved.
     * If a matching category is found for the provided SEO URL path, it returns the category data as a JSON response; otherwise, it throws a NotFoundHttpException.
     */
    #[Route(path: '/store-api/sgwebcheckout/category/by-seo-url', name: 'store-api.sgwebcheckout.category.by-seo-url', defaults: [
        '_routeScope' => ['store-api'],
        '_contextTokenRequired' => false
    ], methods: [
        'GET',
        'POST'
    ])]
    public function categoryBySeoUrl(Request $request, SalesChannelContext $context): JsonResponse
    {
        $seoPath = $this->extractSeoPath($request);
        $category = $this->seoUrlEntityResolver->resolveCategoryBySeoPath($seoPath, $context);
        if ($category === null) {
            throw new NotFoundHttpException('No matching category was found.');
        }

        return new JsonResponse($category);
    }

    private function extractSeoPath(Request $request): string
    {
        $raw = trim((string) $request->get('url', ''));
        $path = (string) parse_url($raw, \PHP_URL_PATH);
        if ($path === '') {
            $path = $raw;
        }

        $normalized = trim(rawurldecode($path), '/');
        if ($normalized === '') {
            throw new NotFoundHttpException('No matching entity was found.');
        }

        return $normalized;
    }
}
