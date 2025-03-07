<?php

declare(strict_types=1);

namespace DrSoftFr\Module\FeatureManager\Controller\Admin;

use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\Package;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\VersionStrategy\JsonManifestVersionStrategy;
use PrestaShop\PrestaShop\Adapter\Feature\Repository\FeatureRepository;
use PrestaShop\PrestaShop\Adapter\Feature\Repository\FeatureValueRepository;
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
                    'featureValueGet' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_value_get'),
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
}
