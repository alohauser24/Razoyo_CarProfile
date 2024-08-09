<?php

namespace Razoyo\CarProfile\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // Add the car_profile attribute to the customer entity
        $customerSetup->addAttribute(Customer::ENTITY, 'car_profile', [
            'type' => 'text',
            'label' => 'My Car Profile',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'position' => 1000,
            'system' => 0,
            'visible_on_front' => true,
            'is_wysiwyg_enabled' => false,
            'is_html_allowed_on_front' => false,
            'is_searchable' => true,
            'is_visible_in_advanced_search' => true,
            'is_filterable' => true,
            'is_filterable_in_search' => true,
            'is_comparable' => false,
            'is_used_for_promo_rules' => false,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'used_in_forms' => [
                    'adminhtml_customer',
                    'checkout_account',
                    'customer_account_create',
                    'customer_account_edit'
                ],
        ]);

        // Add the attribute to the default attribute set and group
        $attributeSetId = $customerSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $customerSetup->getDefaultAttributeGroupId($attributeSetId);

        $eavSetup->addAttributeToSet(
            Customer::ENTITY,
            $attributeSetId,
            $attributeGroupId,
            'car_profile'
        );

        $setup->endSetup();
    }
}
