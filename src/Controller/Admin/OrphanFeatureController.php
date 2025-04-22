<?php

declare(strict_types=1);

namespace DrSoftFr\Module\FeatureManager\Controller\Admin;

use Db;
use Feature;
use FeatureValue;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Validate;

/**
 * Class OrphanFeatureController.
 *
 * @ModuleActivated(moduleName="drsoftfrfeaturemanager", redirectRoute="admin_module_manage")
 */
final class OrphanFeatureController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'AdminDrSoftFrFeatureManagerOrphanFeature';

    /**
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_module_manage",
     *     message="Access denied."
     * )
     *
     * @return JsonResponse
     */
    public function ajaxOrphanFeatureGetAllAction(): JsonResponse
    {
        try {
            $sql = '
            SELECT f.*, fl.name
            FROM ' . _DB_PREFIX_ . 'feature f
            LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON (f.id_feature = fl.id_feature AND fl.id_lang = ' . (int)$this->getContext()->language->id . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'feature_product fp ON f.id_feature = fp.id_feature
            WHERE fp.id_feature IS NULL
            GROUP BY f.id_feature
            ';
            $orphanFeatures = Db::getInstance()->executeS($sql);

            return $this->json([
                'success' => true,
                'message' => 'Orphan Features retrieved',
                'orphan_features' => $orphanFeatures
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to get all Orphan Features [%s]',
                        $t->getMessage()
                    ),
                'orphan_features' => [],
            ]);
        }
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_module_manage",
     *     message="Access denied."
     * )
     *
     * @return JsonResponse
     */
    public function ajaxOrphanFeatureValueGetAllAction(): JsonResponse
    {
        try {
            $sql = '
            SELECT fv.*, fvl.value
            FROM ' . _DB_PREFIX_ . 'feature_value fv
            LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON (fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . (int)$this->getContext()->language->id . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'feature_product fp ON fv.id_feature_value = fp.id_feature_value
            WHERE fp.id_feature_value IS NULL
            GROUP BY fv.id_feature_value
        ';
            $orphanFeatureValues = Db::getInstance()->executeS($sql);

            return $this->json([
                'success' => true,
                'message' => 'Orphan Feature Values retrieved',
                'orphan_feature_values' => $orphanFeatureValues
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to get all Orphan Feature Values [%s]',
                        $t->getMessage()
                    ),
                'orphan_feature_values' => [],
            ]);
        }
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_module_manage",
     *     message="Access denied."
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxOrphanFeatureBulkDeleteAction(Request $request): JsonResponse
    {
        try {
            $featureIds = $this->extractIds($request->request->get('feature_ids'));

            if (empty($featureIds)) {
                return $this->json([
                    'success' => true,
                    'message' => 'No features to delete',
                ]);
            }

            $result = $this->deleteEntities($featureIds, Feature::class);

            return $this->json([
                'success' => $result,
                'message' => $result ? 'Orphan Features deleted' : 'No Orphan Features to delete',
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to delete Orphan Features [%s]',
                        $t->getMessage()
                    ),
            ]);
        }
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_module_manage",
     *     message="Access denied."
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxOrphanFeatureValueBulkDeleteAction(Request $request): JsonResponse
    {
        try {
            $featureValueIds = $this->extractIds($request->request->get('feature_value_ids'));

            if (empty($featureValueIds)) {
                return $this->json([
                    'success' => true,
                    'message' => 'No feature values to delete',
                ]);
            }

            $result = $this->deleteEntities($featureValueIds, FeatureValue::class);

            return $this->json([
                'success' => $result,
                'message' => $result ? 'Orphan Feature Values deleted' : 'No Orphan Feature Values to delete',
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to delete Orphan Feature Values [%s]',
                        $t->getMessage()
                    ),
            ]);
        }
    }

    /**
     * Extracts and filters a list of positive integer IDs from a comma-separated string.
     *
     * @param string|null $idsString The input string containing comma-separated IDs. Null or empty string will return an empty array.
     *
     * @return array An array of positive integers extracted and filtered from the input string.
     */
    private function extractIds(?string $idsString): array
    {
        if (empty($idsString)) {
            return [];
        }

        return array_filter(
            array_map('intval', explode(',', $idsString)),
            function ($id) {
                return $id > 0;
            }
        );
    }

    /**
     * Deletes entities of the specified class by their IDs.
     *
     * @param array $ids An array of entity IDs to delete.
     * @param string $className The name of the class representing the entities.
     *
     * @return bool True if all entities were successfully deleted, false otherwise.
     */
    private function deleteEntities(array $ids, string $className): bool
    {
        $result = true;

        foreach ($ids as $id) {
            $obj = new $className((int)$id);

            if (!Validate::isLoadedObject($obj)) {
                continue;
            }

            $result &= $obj->delete();
        }

        return (bool)$result;
    }


}