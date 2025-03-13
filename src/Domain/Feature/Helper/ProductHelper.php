<?php

declare(strict_types=1);

namespace DrSoftFr\Module\FeatureManager\Domain\Feature\Helper;

use Doctrine\DBAL\Driver\Connection;
use PrestaShop\PrestaShop\Core\Domain\Feature\ValueObject\FeatureId;
use PrestaShop\PrestaShop\Core\Domain\Feature\ValueObject\FeatureValueId;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductConstraintException;

final class ProductHelper
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
     * @param FeatureId $featureId
     * @param FeatureValueId $featureValueId
     * @param int[] $ids
     *
     * @return bool
     *
     * @throws ProductConstraintException
     */
    public function bulkDelete(
        FeatureId      $featureId,
        FeatureValueId $featureValueId,
        array          $ids
    ): bool
    {
        if (empty($ids)) {
            return true;
        }

        foreach ($ids as $id) {
            if ($id <= 0) {
                throw new ProductConstraintException(
                    sprintf('Product id %s is invalid. Product id must be number that is greater than zero.', var_export($id, true)),
                    ProductConstraintException::INVALID_ID
                );
            }
        }

        $productIds = implode(',', $ids);
        $query = str_replace(
            '{table_prefix}',
            $this->tablePrefix,
            'DELETE FROM `{table_prefix}feature_product` WHERE id_feature = :feature_id AND id_feature_value = :feature_value_id AND id_product IN(' . pSQL($productIds) . ');'
        );

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue('feature_id', $featureId->getValue());
        $stmt->bindValue('feature_value_id', $featureValueId->getValue());

        return $stmt->execute();
    }
}