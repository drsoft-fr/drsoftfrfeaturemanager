<?php

declare(strict_types=1);

namespace DrSoftFr\Module\FeatureManager\Adapter\QueryHandler\Product;

use Doctrine\DBAL\Driver\Connection;
use DrSoftFr\Module\FeatureManager\Query\Product\GetProductsByFeatureValueIdQuery;
use PrestaShop\PrestaShop\Core\Domain\Feature\ValueObject\FeatureId;

/**
 * Handles the query GetProductsByFeatureValueIdQuery
 */
final class GetProductsByFeatureValueIdQueryHandler
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $tablePrefix;

    /**
     * @param Connection $connection
     * @param string $tablePrefix
     */
    public function __construct(
        Connection $connection,
        string     $tablePrefix
    )
    {
        $this->connection = $connection;
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * Handle GetProductsByFeatureValueIdQuery
     *
     * @param FeatureId $featureId
     * @param int[] $featureValueIds
     * @param GetProductsByFeatureValueIdQuery $query
     *
     * @return array[]
     */
    public function handle(FeatureId $featureId, array $featureValueIds, GetProductsByFeatureValueIdQuery $query): array
    {
        return $this->getData(
            $featureId->getValue(),
            $featureValueIds,
            $query->getLanguageId()->getValue(),
            $query->getShopId()->getValue()
        );
    }

    /**
     * Retrieves data based on provided parameters
     *
     * @param int $featureId The feature ID to filter by
     * @param int[] $featureValueIds Array of feature value IDs to filter by
     * @param int $languageId The language ID to filter by
     * @param int $shopId The shop ID to filter by
     *
     * @return array Array containing fetched data
     */
    private function getData(
        int   $featureId,
        array $featureValueIds,
        int   $languageId,
        int   $shopId
    ): array
    {
        $join = ' INNER JOIN {table_prefix}product_lang AS pl ON (pl.id_product = p.id_product AND pl.id_lang = :lang_id)';
        $join .= ' INNER JOIN {table_prefix}product_shop AS ps ON (ps.id_product = p.id_product AND ps.id_shop = :shop_id)';
        $join .= ' INNER JOIN {table_prefix}feature_product AS fp ON (fp.id_product = p.id_product)';
        $join .= ' INNER JOIN {table_prefix}feature_lang AS fl ON (fl.id_feature = fp.id_feature AND fl.id_lang = :lang_id)';
        $join .= ' INNER JOIN {table_prefix}feature_value_lang AS fvl ON (fvl.id_feature_value = fp.id_feature_value AND fvl.id_lang = :lang_id)';
        $join .= ' INNER JOIN {table_prefix}category_lang AS cl ON (cl.id_category = ps.id_category_default AND cl.id_lang = :lang_id)';
        $join .= ' LEFT JOIN {table_prefix}supplier AS s ON (s.id_supplier = p.id_supplier)';
        $join .= ' LEFT JOIN {table_prefix}manufacturer AS m ON (m.id_manufacturer = p.id_manufacturer)';

        $where = ' WHERE fp.id_feature = :feature_id AND pl.id_lang = :lang_id AND ps.id_shop = :shop_id';

        if (0 < count($featureValueIds)) {
            $featureValueIds = implode(',', $featureValueIds);
            $where .= ' AND fp.id_feature_value IN(' . pSQL($featureValueIds) . ')';
        }

        $query = str_replace(
            '{table_prefix}',
            $this->tablePrefix,
            'SELECT
            p.id_product, p.id_supplier, s.name AS supplier, p.id_manufacturer, m.name AS manufacturer, p.reference, p.active, pl.id_lang, pl.name, ps.id_shop, ps.id_category_default, cl.name AS category, fp.id_feature, fl.name AS feature, fp.id_feature_value, fvl.value
            FROM `{table_prefix}product` AS p' . $join . $where . ';'
        );

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue('feature_id', $featureId);
        $stmt->bindValue('lang_id', $languageId);
        $stmt->bindValue('shop_id', $shopId);
        $stmt->execute();

        return $stmt->fetchAllAssociative();
    }
}
