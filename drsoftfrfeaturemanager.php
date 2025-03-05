<?php

declare(strict_types=1);

use DrSoftFr\Module\FeatureManager\Controller\Admin\HomeController;
use PrestaShop\PrestaShop\Core\Cache\Clearer\CacheClearerChain;

if (!defined('_PS_VERSION_') || !defined('_CAN_LOAD_FILES_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Class drsoftfrfeaturemanager
 */
class DrsoftFrFeatureManager extends Module
{
    /**
     * @var string $authorEmail Author email
     */
    public $authorEmail;

    /**
     * @var string $moduleGithubRepositoryUrl Module GitHub repository URL
     */
    public $moduleGithubRepositoryUrl;

    /**
     * @var string $moduleGithubIssuesUrl Module GitHub issues URL
     */
    public $moduleGithubIssuesUrl;

    public function __construct()
    {
        $this->author = 'drSoft.fr';
        $this->bootstrap = true;
        $this->dependencies = [];
        $this->name = 'drsoftfrfeaturemanager';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_
        ];
        $this->tab = 'content_management';
        $this->tabs = [
            [
                'class_name' => HomeController::TAB_CLASS_NAME,
                'name' => 'Feature manager',
                'parent_class_name' => 'AdminCatalog',
                'route_name' => 'admin_drsoft_fr_feature_manager_home_index',
                'visible' => true,
            ],
        ];
        $this->version = '0.0.1';
        $this->authorEmail = 'contact@drsoft.fr';
        $this->moduleGithubRepositoryUrl = 'https://github.com/drsoft-fr/prestashop-module-drsoftfrfeaturemanager';
        $this->moduleGithubIssuesUrl = 'https://github.com/drsoft-fr/prestashop-module-drsoftfrfeaturemanager/issues';

        parent::__construct();

        $this->displayName = $this->trans('drSoft.fr Feature manager', [], 'Modules.Drsoftfrfeaturemanager.Admin');
        $this->description = $this->trans('PrestaShop module for managing product characteristics.', [], 'Modules.Drsoftfrfeaturemanager.Admin');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.Drsoftfrfeaturemanager.Admin');
    }

    /**
     * Disables the module.
     *
     * @param bool $force_all Whether to disable all instances of the module, even if they are currently enabled.
     *
     * @return bool Whether the module was disabled successfully.
     */
    public function disable($force_all = false)
    {
        $this->_clearCache('*');

        if (!parent::disable($force_all)) {
            return false;
        }

        return true;
    }

    /**
     * Enables the module by clearing the cache and calling the parent's enable method.
     *
     * @param bool $force_all Whether to force the enabling of all modules.
     *
     * @return bool True on successful enable, false otherwise.
     */
    public function enable($force_all = false)
    {
        $this->_clearCache('*');

        if (!parent::enable($force_all)) {
            return false;
        }

        return true;
    }

    /**
     * Redirects the user to the admin panel.
     *
     * @return void
     */
    public function getContent(): void
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink(HomeController::TAB_CLASS_NAME)
        );
    }

    /**
     * Installs the module
     *
     * @return bool Returns true if the installation is successful, false otherwise.
     */
    public function install(): bool
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install()) {
            $this->_errors[] = $this->trans(
                'There was an error during the installation.',
                [],
                'Modules.Drsoftfrfeaturemanager.Error'
            );

            return false;
        }

        try {
            $this->getCacheClearerChain()->clear();
        } catch (Throwable $t) {
            $this->_errors[] = $t->getMessage();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * Uninstalls the module
     *
     * @return bool Returns true if uninstallation was successful, false otherwise
     */
    public function uninstall(): bool
    {
        if (!parent::uninstall()) {
            $this->_errors[] = $this->trans(
                'There was an error during the uninstallation.',
                [],
                'Modules.Drsoftfrfeaturemanager.Error'
            );

            return false;
        }

        try {
            $this->getCacheClearerChain()->clear();
        } catch (Throwable $t) {
            $this->_errors[] = $t->getMessage();
        }

        return parent::uninstall();
    }

    /**
     * Get the CacheClearerChain.
     *
     * @return CacheClearerChain
     *
     * @throws Exception
     */
    private function getCacheClearerChain(): CacheClearerChain
    {
        $cacheClearerChain = $this->get('prestashop.core.cache.clearer.cache_clearer_chain');

        if (!($cacheClearerChain instanceof CacheClearerChain)) {
            throw new Exception('The cacheClearerChain object must implement CacheClearerChain.');
        }

        return $cacheClearerChain;
    }
}
