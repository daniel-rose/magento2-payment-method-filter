<?php

namespace DR\PaymentMethodFilter\Setup;

use DR\PaymentMethodFilter\Model\Entity\Attribute\Source\PaymentMethods;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * Eav setup factory
     *
     * @var EavSetup
     */
    protected $eavSetupFactory;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $setup->startSetup();

        $customerSetup->addAttribute(Customer::ENTITY, 'disallowed_payment_methods', [
            'type' => 'text',
            'label' => 'Disallowed Payment Methods',
            'input' => 'multiselect',
            'source' => PaymentMethods::class,
            'backend' => ArrayBackend::class,
            'system' => false,
            'required' => false,
            'position' => 100
        ]);

        $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'disallowed_payment_methods')
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $eavSetup->addAttribute(Product::ENTITY, 'disallowed_payment_methods', [
            'type' => 'text',
            'label' => 'Disallowed Payment Methods',
            'input' => 'multiselect',
            'source' => PaymentMethods::class,
            'backend' => ArrayBackend::class,
            'required' => false,
            'global' => Attribute::SCOPE_STORE,
            'user_defined' => false,
            'apply_to' => ''
        ]);

        $setup->endSetup();
    }
}