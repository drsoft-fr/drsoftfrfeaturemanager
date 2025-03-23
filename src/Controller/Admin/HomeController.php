<?php

declare(strict_types=1);

namespace DrSoftFr\Module\FeatureManager\Controller\Admin;

use DrSoftFr\Module\FeatureManager\Adapter\QueryHandler\Product\GetProductsByFeatureValueIdQueryHandler;
use DrSoftFr\Module\FeatureManager\Domain\Feature\Helper\ProductHelper;
use DrSoftFr\Module\FeatureManager\Query\Product\GetProductsByFeatureValueIdQuery;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\Package;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\VersionStrategy\JsonManifestVersionStrategy;
use PrestaShop\PrestaShop\Adapter\Feature\Repository\FeatureRepository;
use PrestaShop\PrestaShop\Adapter\Feature\Repository\FeatureValueRepository;
use PrestaShop\PrestaShop\Core\Domain\Feature\Exception\InvalidFeatureValueIdException;
use PrestaShop\PrestaShop\Core\Domain\Feature\ValueObject\FeatureId;
use PrestaShop\PrestaShop\Core\Domain\Feature\ValueObject\FeatureValueId;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomeController.
 *
 * @ModuleActivated(moduleName="drsoftfrfeaturemanager", redirectRoute="admin_module_manage")
 */
final class HomeController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'AdminDrSoftFrFeatureManagerHome';
    const TEMPLATE_FOLDER = '@Modules/drsoftfrfeaturemanager/views/templates/admin/home/';

    /**
     * @var Package
     */
    private $manifest;

    public function __construct()
    {
        parent::__construct();

        $this->manifest = new Package(
            new JsonManifestVersionStrategy(
                _PS_MODULE_DIR_ . '/drsoftfrfeaturemanager/views/.vite/manifest.json'
            )
        );
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
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        \Media::addJsDef([
            'drsoftfrfeaturemanager' => [
                'routes' => [
                    'featureCreate' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_create'),
                    'featureDelete' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_delete'),
                    'featureGetAll' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_get_all'),
                    'featureValueCreate' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_value_create'),
                    'featureValueDelete' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_value_delete'),
                    'featureValueDuplicate' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_value_duplicate'),
                    'featureValueGet' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_value_get'),
                    'featureValueRelocate' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_value_relocate'),
                    'productAddToRightColumn' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_product_add_to_right_column'),
                    'productDelete' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_product_delete'),
                    'productGet' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_product_get'),
                    'productRelocate' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_product_relocate'),
                ]
            ]
        ]);

        return $this->render(self::TEMPLATE_FOLDER . 'index.html.twig', [
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'manifest' => $this->manifest,
        ]);
    }

    public function ajaxFeatureCreateAction(Request $request): JsonResponse
    {
        try {
            $name = $request->request->get('name', '');
            $obj = new \Feature();
            $obj->name = [1 => $name];

            if (!$obj->save()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to create Feature',
                    'name' => $name ?? '',
                ]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Feature created',
                'name' => $name ?? '',
                'id_feature' => $obj->id ?? 0,
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to create Feature [%s]',
                        $t->getMessage()
                    ),
                'name' => $name ?? '',
            ]);
        }
    }

    public function ajaxFeatureDeleteAction(Request $request): JsonResponse
    {
        try {
            $featureId = $request->request->getInt('id_feature', 0);
            $obj = new \Feature($featureId);

            if (!\Validate::isLoadedObject($obj)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Feature not found',
                    'id_feature' => $featureId ?? 0,
                ]);
            }

            if (!$obj->delete()) {
                return $this->json([
                    'success' => false,
                    'message' =>
                        sprintf(
                            'Failed to delete %s #%d',
                            get_class($obj),
                            $obj->id
                        ),
                    'id_feature' => $featureId ?? 0,
                ]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Feature deleted',
                'id_feature' => $featureId,
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to delete Feature #%d [%s]',
                        $featureId ?? 0,
                        $t->getMessage()
                    ),
                'id_feature' => $featureId ?? 0,
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
    public function ajaxFeatureGetAllAction(): JsonResponse
    {
        $datas = [];
        $featureRepository = $this->getFeatureRepository();
        /** @var array<int, array<string, mixed>> $features */
        $features = $featureRepository->getFeaturesByLang(1);

        foreach ($features as $feature) {
            $datas[] = [
                'id_feature' => $feature['id_feature'],
                'name' => $feature['localized_names'][1],
            ];
        }

        return $this->json($datas);
    }

    public function ajaxFeatureValueCreateAction(Request $request): JsonResponse
    {
        try {
            $featureId = $request->request->getInt('id_feature');
            $value = $request->request->get('value');
            $obj = new \FeatureValue();
            $obj->id_feature = $featureId;
            $obj->value = [
                1 => $value
            ];

            if (!$obj->save()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to create FeatureValue',
                    'id_feature' => $featureId ?? 0,
                    'value' => $value ?? '',
                ]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Feature value created',
                'id_feature' => $featureId ?? 0,
                'value' => $value ?? '',
                'id_feature_value' => $obj->id ?? 0,
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to create FeatureValue [%s]',
                        $t->getMessage()
                    ),
                'id_feature' => $featureId ?? 0,
                'value' => $value ?? '',
            ]);
        }
    }

    /**
     * Handle the AJAX request to delete a feature value.
     *
     * @param Request $request The incoming request object
     *
     * @return JsonResponse JSON response indicating the success or failure of the deletion
     */
    public function ajaxFeatureValueDeleteAction(Request $request): JsonResponse
    {
        try {
            $featureValueId = $request->request->getInt('id_feature_value');
            $obj = new \FeatureValue($featureValueId);

            if (!\Validate::isLoadedObject($obj)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Feature value not found',
                    'id_feature_value' => $featureValueId ?? 0,
                ]);
            }

            if (!$obj->delete()) {
                return $this->json([
                    'success' => false,
                    'message' =>
                        sprintf(
                            'Failed to delete %s #%d',
                            get_class($obj),
                            $obj->id
                        ),
                    'id_feature_value' => $featureValueId ?? 0,
                ]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Feature value deleted',
                'id_feature_value' => $featureValueId,
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to delete FeatureValue #%d [%s]',
                        $featureValueId ?? 0,
                        $t->getMessage()
                    ),
                'id_feature_value' => $featureValueId ?? 0,
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
     *
     * @throws InvalidFeatureValueIdException
     */
    public function ajaxFeatureValueDuplicateAction(Request $request): JsonResponse
    {
        try {
            $featureId = $request->request->getInt('id_feature', 0);
            $featureValueId = $request->request->getInt('id_feature_value', 0);

            if (0 >= $featureId || 0 >= $featureValueId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid FeatureId or id_feature_value',
                    'id_feature' => $featureId ?? 0,
                    'id_feature_value' => $featureValueId ?? 0,
                ]);
            }

            $obj = new \FeatureValue($featureValueId);

            if (!\Validate::isLoadedObject($obj)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Feature value not found',
                    'id_feature_value' => $featureValueId ?? 0,
                ]);
            }

            $obj->id = null;
            $obj->id_feature_value = null;
            $obj->id_feature = $featureId;
            $obj->force_id = false;

            if (!$obj->add()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to duplicate FeatureValue',
                    'old_id_feature_value' => $featureValueId ?? 0,
                    'id_feature' => $obj->feature_id ?? 0,
                ]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Feature value duplicated',
                'id_feature' => $obj->feature_id ?? 0,
                'value' => $obj->value ?? '',
                'id_feature_value' => $obj->id ?? 0,
                'old_id_feature_value' => $featureValueId ?? 0,
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to duplicate FeatureValue [%s]',
                        $t->getMessage()
                    ),
                'id_feature' => $featureId ?? 0,
                'old_id_feature' => $featureValueId ?? 0,
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
    public function ajaxFeatureValueGetAction(Request $request): JsonResponse
    {
        $featureId = $request->request->getInt('id_feature', 0);
        $datas = [];
        $featureValueRepository = $this->getFeatureValueRepository();
        $featureValues = $featureValueRepository->getFeatureValuesByLang(1, ['id_feature' => $featureId]);

        foreach ($featureValues as $featureValue) {
            $datas[] = [
                'id_feature_value' => $featureValue['id_feature_value'],
                'value' => $featureValue['value'],
                'custom' => (bool)$featureValue['custom']
            ];
        }

        return $this->json($datas);
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_module_manage",
     *     message="Access denied."
     * )
     *
     * Handles AJAX request to relocate a feature value.
     *
     * @param Request $request The HTTP request object
     *
     * @return JsonResponse JSON response indicating success or failure of the relocation process
     */
    public function ajaxFeatureValueRelocateAction(Request $request): JsonResponse
    {
        try {
            $featureId = $request->request->getInt('id_feature', 0);
            $newFeatureId = $request->request->getInt('new_id_feature', 0);
            $featureValueId = $request->request->getInt('id_feature_value', 0);

            if (0 >= $featureId || 0 >= $newFeatureId || 0 >= $featureValueId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid feature_id, new_feature_id or id_feature_value',
                    'id_feature' => $featureId ?? 0,
                    'new_id_feature' => $newFeatureId ?? 0,
                    'id_feature_value' => $featureValueId ?? 0,
                ]);
            }

            $obj = new \FeatureValue($featureValueId);

            if (!\Validate::isLoadedObject($obj)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Feature value not found',
                    'id_feature_value' => $featureValueId ?? 0,
                ]);
            }

            if ($obj->id_feature !== $featureId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Feature ID incorrect',
                    'id_feature_value' => $featureValueId ?? 0,
                    'new_id_feature' => $newFeatureId ?? 0,
                    'id_feature' => $featureId ?? 0,
                ]);
            }

            $obj->id_feature = $newFeatureId;

            if (!$obj->save()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to relocate FeatureValue',
                    'id_feature_value' => $featureValueId ?? 0,
                    'new_id_feature' => $newFeatureId ?? 0,
                    'id_feature' => $featureId ?? 0,
                ]);
            }

            $query = $this->getGetProductsByFeatureValueIdQuery();
            $products = $this
                ->getGetProductsByFeatureValueIdQueryHandler()
                ->handle(
                    new FeatureId($featureId),
                    new FeatureValueId($featureValueId),
                    $query
                );
            $formattedProductIds = [];

            foreach ($products as $product) {
                $product = (int)$product['id_product'];

                if (0 >= $product) {
                    continue;
                }

                $formattedProductIds[] = $product;
            }

            $helper = $this->getProductHelper();

            if (!$helper->bulkInsert(
                new FeatureId($newFeatureId),
                new FeatureValueId($featureValueId),
                $formattedProductIds
            )) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to insert new products',
                    'id_feature' => $featureId ?? 0,
                    'id_feature_value' => $featureValueId ?? 0,
                    'new_id_feature' => $newFeatureId ?? 0,
                    'id_products' => $formattedProductIds ?? [],
                ]);
            }

            if (!$helper->bulkDelete(
                new FeatureId($featureId),
                new FeatureValueId($featureValueId),
                $formattedProductIds
            )) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to delete old products',
                    'id_feature' => $featureId ?? 0,
                    'id_feature_value' => $featureValueId ?? 0,
                    'new_id_feature' => $newFeatureId ?? 0,
                    'id_products' => $formattedProductIds ?? [],
                ]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Feature value relocated',
                'id_feature' => $obj->feature_id ?? 0,
                'value' => $obj->value ?? '',
                'id_feature_value' => $obj->id ?? 0,
                'old_id_feature' => $featureId ?? 0,
                'id_products' => $formattedProductIds ?? [],
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to relocate FeatureValue [%s]',
                        $t->getMessage()
                    ),
                'id_feature' => $featureId ?? 0,
                'new_id_feature' => $newFeatureId ?? 0,
                'id_feature_value' => $featureValueId ?? 0,
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxProductDeleteAction(Request $request): JsonResponse
    {
        try {
            $formattedProductIds = [];
            $productIds = explode(',', $request->request->get('id_products', ''));
            $featureValueId = $request->request->getInt('id_feature_value', 0);
            $featureId = $request->request->getInt('id_feature', 0);

            foreach ($productIds as $productId) {
                $productId = (int)$productId;

                if (0 >= $productId) {
                    continue;
                }

                $formattedProductIds[] = $productId;
            }

            if (0 >= $featureId || 0 >= $featureValueId || empty($formattedProductIds)) {
                return $this->json([]);
            }

            $service = $this->getProductHelper();

            if (!$service->bulkDelete(
                new FeatureId($featureId),
                new FeatureValueId($featureValueId),
                $formattedProductIds
            )) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to delete products',
                    'id_feature' => $featureId ?? 0,
                    'id_feature_value' => $featureValueId ?? 0,
                    'id_products' => $productIds ?? [],
                ]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Products deleted',
                'id_feature' => $featureId,
                'id_feature_value' => $featureValueId,
                'id_products' => $productIds,
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to delete Products [%s]',
                        $t->getMessage()
                    ),
                'id_feature' => $featureId ?? 0,
                'id_feature_value' => $featureValueId ?? 0,
                'id_products' => $productIds ?? [],
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
     *
     * @throws InvalidFeatureValueIdException
     */
    public function ajaxProductGetAction(Request $request): JsonResponse
    {
        $featureId = $request->request->getInt('id_feature', 0);
        $featureValueId = $request->request->getInt('id_feature_value', 0);

        if (0 >= $featureId || 0 >= $featureValueId) {
            return $this->json([]);
        }

        $query = $this->getGetProductsByFeatureValueIdQuery();
        $products = $this
            ->getGetProductsByFeatureValueIdQueryHandler()
            ->handle(
                new FeatureId($featureId),
                new FeatureValueId($featureValueId),
                $query
            );

        return $this->json($products);
    }

    /**
     * Handles AJAX request to relocate products to new feature and feature value.
     *
     * @param Request $request The request object
     *
     * @return JsonResponse JSON response indicating the success or failure of the operation
     */
    public function ajaxProductRelocateAction(Request $request): JsonResponse
    {
        try {
            $formattedProductIds = [];
            $productIds = explode(',', $request->request->get('id_products', ''));
            $newFeatureValueId = $request->request->getInt('new_id_feature_value', 0);
            $newFeatureId = $request->request->getInt('new_id_feature', 0);
            $featureValueId = $request->request->getInt('id_feature_value', 0);
            $featureId = $request->request->getInt('id_feature', 0);

            foreach ($productIds as $productId) {
                $productId = (int)$productId;

                if (0 >= $productId) {
                    continue;
                }

                $formattedProductIds[] = $productId;
            }

            if (0 >= $featureId || 0 >= $featureValueId || 0 >= $newFeatureId || 0 >= $newFeatureValueId || empty($formattedProductIds)) {
                return $this->json([]);
            }

            $helper = $this->getProductHelper();

            if (!$helper->bulkInsert(
                new FeatureId($newFeatureId),
                new FeatureValueId($newFeatureValueId),
                $formattedProductIds
            )) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to insert new products',
                    'id_feature' => $featureId ?? 0,
                    'id_feature_value' => $featureValueId ?? 0,
                    'new_id_feature' => $newFeatureId ?? 0,
                    'new_id_feature_value' => $newFeatureValueId ?? 0,
                    'id_products' => $productIds ?? [],
                ]);
            }

            if (!$helper->bulkDelete(
                new FeatureId($featureId),
                new FeatureValueId($featureValueId),
                $formattedProductIds
            )) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to delete old products',
                    'id_feature' => $featureId ?? 0,
                    'id_feature_value' => $featureValueId ?? 0,
                    'new_id_feature' => $newFeatureId ?? 0,
                    'new_id_feature_value' => $newFeatureValueId ?? 0,
                    'id_products' => $productIds ?? [],
                ]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Products relocated',
                'id_feature' => $featureId,
                'id_feature_value' => $featureValueId,
                'new_id_feature' => $newFeatureId,
                'new_id_feature_value' => $newFeatureValueId,
                'id_products' => $productIds,
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error occurred when trying to delete Products [%s]',
                        $t->getMessage()
                    ),
                'id_feature' => $featureId ?? 0,
                'id_feature_value' => $featureValueId ?? 0,
                'new_id_feature' => $newFeatureId ?? 0,
                'new_id_feature_value' => $newFeatureValueId ?? 0,
                'id_products' => $productIds ?? [],
            ]);
        }
    }

    /**
     * Controller action to add products to the right column via AJAX request.
     *
     * @param Request $request The request object containing product data
     *
     * @return JsonResponse JSON response indicating success or failure of the operation
     */
    public function ajaxProductAddToRightColumnAction(Request $request): JsonResponse
    {
        try {
            $formattedProductIds = [];
            $productIds = explode(',', $request->request->get('id_products', ''));
            $newFeatureValueId = $request->request->getInt('new_id_feature_value', 0);
            $newFeatureId = $request->request->getInt('new_id_feature', 0);

            foreach ($productIds as $productId) {
                $productId = (int)$productId;

                if (0 >= $productId) {
                    continue;
                }

                $formattedProductIds[] = $productId;
            }

            if (0 >= $newFeatureId || 0 >= $newFeatureValueId || empty($formattedProductIds)) {
                return $this->json([]);
            }

            $helper = $this->getProductHelper();

            if (!$helper->bulkInsert(
                new FeatureId($newFeatureId),
                new FeatureValueId($newFeatureValueId),
                $formattedProductIds
            )) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to insert new products',
                    'id_feature' => $featureId ?? 0,
                    'id_feature_value' => $featureValueId ?? 0,
                    'new_id_feature' => $newFeatureId ?? 0,
                    'new_id_feature_value' => $newFeatureValueId ?? 0,
                    'id_products' => $productIds ?? [],
                ]);
            }

            return $this->json([
                'success' => true,
                'message' => 'Products added to right-hand column',
                'new_id_feature' => $newFeatureId,
                'new_id_feature_value' => $newFeatureValueId,
                'id_products' => $productIds,
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' =>
                    sprintf(
                        'Error when trying to add products [%s]',
                        $t->getMessage()
                    ),
                'new_id_feature' => $newFeatureId ?? 0,
                'new_id_feature_value' => $newFeatureValueId ?? 0,
                'id_products' => $productIds ?? [],
            ]);
        }
    }

    /**
     * @return FeatureRepository
     */
    private function getFeatureRepository(): FeatureRepository
    {
        /* @type FeatureRepository */
        return $this->get('PrestaShop\PrestaShop\Adapter\Feature\Repository\FeatureRepository');
    }

    /**
     * @return FeatureValueRepository
     */
    private function getFeatureValueRepository(): FeatureValueRepository
    {
        /* @type FeatureValueRepository */
        return $this->get('PrestaShop\PrestaShop\Adapter\Feature\Repository\FeatureValueRepository');
    }

    /**
     * @return GetProductsByFeatureValueIdQuery
     */
    private function getGetProductsByFeatureValueIdQuery(): GetProductsByFeatureValueIdQuery
    {
        /* @type GetProductsByFeatureValueIdQuery */
        return $this->get('drsoft_fr.module.feature_manager.query.product.get_products_by_feature_value_id_query');
    }

    /**
     * @return GetProductsByFeatureValueIdQueryHandler
     */
    private function getGetProductsByFeatureValueIdQueryHandler(): GetProductsByFeatureValueIdQueryHandler
    {
        /* @type GetProductsByFeatureValueIdQueryHandler */
        return $this->get('drsoft_fr.module.feature_manager.adapter.query_handler.product.get_products_by_feature_value_id_query_handler');
    }

    /**
     * @return ProductHelper
     */
    private function getProductHelper(): ProductHelper
    {
        /* @type ProductHelper */
        return $this->get('drsoft_fr.module.feature_manager.domain.feature.helper.product_helper');
    }
}
