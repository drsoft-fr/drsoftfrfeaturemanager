<?php

declare(strict_types=1);

namespace DrSoftFr\Module\FeatureManager\Controller\Admin;

use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\Package;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\VersionStrategy\JsonManifestVersionStrategy;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
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
        return $this->render(self::TEMPLATE_FOLDER . 'index.html.twig', [
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'manifest' => $this->manifest,
        ]);
    }
}
