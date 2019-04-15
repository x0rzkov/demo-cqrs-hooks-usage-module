<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ToggleColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;

//todo: demonstrate how include custom js extensions for existing grids.
/**
 * Class Ps_DemoCQRSHooksUsage demonstrates the usage of CQRS and hooks.
 */
class Ps_DemoCQRSHooksUsage extends Module
{
    public function __construct()
    {
        $this->name = 'ps_democqrshooksusage';
        $this->version = '1.0.0';
        $this->author = 'Tomas Ilginis';

        parent::__construct();

        $this->displayName = 'Demo for CQRS and hooks usage';
        $this->description =
            'Help developers to understand how to create module using new hooks and apply best practices when using CQRS';

        $this->ps_versions_compliancy = [
            'min' => '1.7.6.0',
            'max' => _PS_VERSION_,
        ];
    }

    /**
     * Install module and register hooks to allow grid modification.
     *
     * @return bool
     */
    public function install()
    {
        return parent::install() &&
            // Register hook to allow Logs grid definition modifications.
            // Each grid's definition modification hook has it's own name. Hook name is built using
            // this structure: "action{grid_id}GridDefinitionModifier", in this case "grid_id" is "customer"
            // this means we will be modifying "Sell > Customers" page grid.
            // You can check any definition factory service in PrestaShop\PrestaShop\Core\Grid\Definition\Factory
            // to see available grid ids. Grid id is returned by `getId()` method.
            $this->registerHook('actionCustomerGridDefinitionModifier') &&
            $this->registerHook('actionCustomerGridQueryBuilderModifier') &&
            $this->installTables()
        ;
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallTables();
    }

    /**
     * Hooks allows to modify Customers grid definition.
     * This hook is a right place to add/remove columns or actions (bulk, grid).
     *
     * @param array $params
     */
    public function hookActionCustomerGridDefinitionModifier(array $params)
    {
        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition
            ->getColumns()
            ->addAfter(
                'optin',
                (new ToggleColumn('is_allowed_for_review'))
                    ->setName('Allowed for review')
                    ->setOptions([
                        'field' => 'is_allowed_for_review',
                        'primary_field' => 'id_customer',
                        'route' => 'ps_democqrshooksusage_toggle_is_allowed_for_review',
                        'route_param_name' => 'customerId',
                    ])
            )
        ;
    }

    public function hookActionCustomerGridQueryBuilderModifier(array $params)
    {
        
    }

    /**
     * Installs sample tables required for demonstration.
     *
     * @return bool
     */
    private function installTables()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `' . pSQL(_DB_PREFIX_) . 'democqrshooksusage_reviewer` (
                `id_reviewer` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_customer` INT(10) UNSIGNED NOT NULL,
                `is_allowed_for_review` TINYINT(1) NOT NULL,
                PRIMARY KEY (`id_reviewer`)
            ) ENGINE=' . pSQL(_MYSQL_ENGINE_) . ' COLLATE=utf8_unicode_ci;
        ';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Uninstalls sample tables required for demonstration.
     *
     * @return bool
     */
    private function uninstallTables()
    {
        $sql = 'DROP TABLE IF EXISTS `' . pSQL(_DB_PREFIX_) . 'democqrshooksusage_reviewer`';

        return Db::getInstance()->execute($sql);
    }
}
