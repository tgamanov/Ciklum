<?php

namespace Ciklum\Subscription\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table as TableSourceModel;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Type;

class InstallData implements InstallDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    private $set;
    private $type;
    private $product;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory, Set $set, Type $type, Product $product)
    {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->set = $set;
        $this->type = $type;
        $this->product = $product;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $this->set;
        $entityType = $this->type->loadByCode('catalog_product');;
        $defaultSetId = $this->product->getDefaultAttributeSetid();

        $data = [
            'attribute_set_name' => 'subscription',
            'entity_type_id' => $entityType->getId(),
            'sort_order' => 200,
        ];

        $attributeSet->setData($data);
        $attributeSet->validate();
        $attributeSet->save();
        $attributeSet->initFromSkeleton($defaultSetId);
        $attributeSet->save();

        /** @var CategorySetup $catalogSetup */
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $setup->startSetup();

        $catalogSetup->addAttribute(Product::ENTITY, 'subscription_type', [
            'type' => 'varchar',
            'label' => 'Type of subscription',
            'input' => 'select',
            'source' => TableSourceModel::class,
            'sort_order' => 1000,
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'used_in_product_listing' => true,
            'user_defined' => true,
            'required' => false,
            'group' => 'Product Details',
            'option' => [
                'values' => [
                    100 => 'TYPE1',
                    200 => 'TYPE2',
                    300 => 'TYPE3',
                ]
            ],
            'attribute_set' => 'subscription',
        ]);

        $setup->endSetup();
    }
}