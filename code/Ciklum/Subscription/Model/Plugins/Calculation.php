<?php


namespace Ciklum\Subscription\Model\Plugins;

use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session;


class Calculation

{

    /**
     * @param \Magento\Tax\Model\Calculation $subject
     * @param $result
     * @return mixed
     */

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Session $checkoutSession
     */
    protected $session;

    public function __construct(

        ProductRepository $productRepository,
        Session $session
    )
    {
        $this->productRepository = $productRepository;
        $this->session = $session;
    }

    /**
     * Check if one of the products has attribut subscription_type with value TYPE3 and remove tax
     * @param \Magento\Tax\Model\Calculation $subject
     * @param $result
     * @return int
     */
    public function afterCalcTaxAmount(\Magento\Tax\Model\Calculation $subject, $result)
    {
        $items = $this->session->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            $productId = $item->getData('product_id');
            $currentProduct = $this->productRepository->getById($productId);

            $unTaxAttrOptionId = $currentProduct->getData('subscription_type');
            if ($unTaxAttrOptionId != null) {
                $attr = $currentProduct->getResource()->getAttribute('subscription_type');
                $unTaxAttr = $attr->getSource()->getOptionText($unTaxAttrOptionId);

                if ($unTaxAttr === "TYPE3") {
                    $result = 0;
                }
            }

        }
        return $result;
    }

}