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
                'getFeatures' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_get_features'),
                'featureValueDelete' => $this->generateUrl('admin_drsoft_fr_feature_manager_home_ajax_feature_value_delete'),
            ]
        ]);

        return $this->render(self::TEMPLATE_FOLDER . 'index.html.twig', [
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'manifest' => $this->manifest,
        ]);
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
    public function ajaxGetFeaturesAction(Request $request): JsonResponse
    {
        $datas = [];
        $featureRepository = $this->getFeatureRepository();
        /** @var array<int, array<string, mixed>> $features */
        $features = $featureRepository->getFeaturesByLang(1);

        foreach ($features as $feature) {
            $featureValueRepository = $this->getFeatureValueRepository();
            $featureValues = $featureValueRepository->getFeatureValuesByLang(1, ['id_feature' => $feature['id_feature']]);
            $formattedFeatureValues = [];

            foreach ($featureValues as $featureValue) {
                $formattedFeatureValues[] = [
                    'id' => $featureValue['id_feature_value'],
                    'name' => $featureValue['value'],
                    'custom' => (bool)$featureValue['custom']
                ];
            }

            $datas[] = [
                'id' => $feature['id_feature'],
                'name' => $feature['localized_names'][1],
                'values' => $formattedFeatureValues
            ];
        }

        return $this->json($datas);
    }

    public function ajaxFeatureValueDeleteAction(Request $request): JsonResponse
    {
        $id = $request->request->getInt('id');

        // TODO faire la suppression de la featureValue

        return $this->json([
            'success' => true,
            'id_feature_value' => $id,
        ]);
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
