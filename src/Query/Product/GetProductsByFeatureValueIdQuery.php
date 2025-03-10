<?php

declare(strict_types=1);

namespace DrSoftFr\Module\FeatureManager\Query\Product;

use PrestaShop\PrestaShop\Core\Domain\Language\ValueObject\LanguageId;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopId;

/**
 * Get all products
 */
final class GetProductsByFeatureValueIdQuery
{
    /**
     * @var LanguageId
     */
    private $languageId;

    /**
     * @var ShopId
     */
    private $shopId;

    /**
     * @param int $languageId
     * @param int $shopId
     *
     * @throws \PrestaShop\PrestaShop\Core\Domain\Shop\Exception\ShopException
     */
    public function __construct(
        int $languageId,
        int $shopId
    )
    {
        $this->languageId = new LanguageId($languageId);
        $this->shopId = new ShopId($shopId);
    }

    /**
     * @return LanguageId
     */
    public function getLanguageId(): LanguageId
    {
        return $this->languageId;
    }

    /**
     * @return ShopId
     */
    public function getShopId(): ShopId
    {
        return $this->shopId;
    }
}
