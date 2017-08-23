<?php
/**
 * @category    FW
 * @package     FW_IGo
 * @copyright   Copyright (c) 2013 F+W Media, Inc. (http://www.fwmedia.com)
 * @author      J.P. Daniel <jp.daniel@fwmedia.com>
 */
class FW_IGo_Model_Observer
{
    /** 
     * Array to store changes to be merged into the iGoDigital block
     * @var array $_rta
     */
    private $_rta = array();
    
    /** 
     * Array to store params for the FW_IGo_Block_Content Block
     * @var array $_params
     */
    private $_params = array();
    
    /**
     * Add Category information to data array
     * @param Varien_Event_Observer $observer
     */    
    public function onCategoryView(Varien_Event_Observer $observer) 
    {        
        $category = $observer->getEvent()->getCategory(); // Get the category the user is viewing from the observer
        $this->_rta['rtaCategory'] = $category->getId();  // Set the category id to the rta array    
        $this->_params['category'] = $category->getId();  // Set the category id to the params array      
    }
    
    /**
     * Add Product information to data array
     * @param Varien_Event_Observer $observer
     */    
    public function onProductView(Varien_Event_Observer $observer) 
    {
        $product = $observer->getProduct();                 // Get current product from observer
        $this->_rta['rtaProductSKU'] = $product->getSku();  // Set the sku to the rta array
        $this->_params['item'] = $product->getSku();         // Set the sku to the params array
    }
    
    /**
     * Add Review information to session to be grabbed out on next request
     * @param Varien_Event_Observer $observer
     */ 
    public function onReviewSave(Varien_Event_Observer $observer) 
    {
        $rating = $observer->getObject()->getRatings();                     // Get array of ratings
        $rating = array_sum($rating) / count($rating);                      // Get the rating average
        Mage::getSingleton('review/session')->setData('rating', $rating);   // Set rating to session
    }
    
    /**
     * Add ratings data to array if review was just added
     * @param Varien_Event_Observer $observer
     */ 
    public function onReviewList(Varien_Event_Observer $observer) 
    {
        if ($rating = Mage::getSingleton('review/session')->getData('rating', true)) {  // Get rating from session
            if ($sku = Mage::registry('product')->getSku()) {       // Get product from registry
                $this->_rta['rtaProductSKU'] = $sku;        // Set the sku to the rta array
                $this->_rta['rtaRating'] = $rating;         // Set the rating to the rta array
            }
        }
    }
    
    /**
     * Add Cart information to data array
     * @param Varien_Event_Observer $observer
     */ 
    public function onCartView(Varien_Event_Observer $observer) 
    {
        $cartHelper = Mage::helper('checkout/cart');        // Get the cart helper
        if (!$cartHelper->getItemsCount()) {
                $this->_rta['rtaClearCart'] = "1";
                return;          // Return if cart is empty
        }
        $this->_rta['rtaCart'] = array();                   // Init the sku array
        $this->_rta['rtaCartAmounts'] = array();            // Init the amount array
        $this->_rta['rtaCartQuantities'] = array();         // Init the quantity array
        foreach ($cartHelper->getQuote()->getAllItems() as $item) {     // Loop through all cart items
            if ($item->getParentItem() !== NULL && $item->getParentItem()->getProductType() == "configurable")
            	continue;
            $this->_rta['rtaCart'][] = $item->getSku();                             // Add item sku to array
            $this->_rta['rtaCartAmounts'][] = number_format($item->getPrice(), 2);  // Add item price to array
            $this->_rta['rtaCartQuantities'][] = $item->getQty();                   // Add item qty to array
        }
        $this->_rta['rtaCart'] = implode('|', $this->_rta['rtaCart']);                      // format sku data and set to rta array
        $this->_rta['rtaCartSku'] = $this->_rta['rtaCart'];
        $this->_rta['rtaCartAmounts'] = implode('|', $this->_rta['rtaCartAmounts']);        // format price data and set to rta array
        $this->_rta['rtaCartQuantities'] = implode('|', $this->_rta['rtaCartQuantities']);  // format quantity data and set to rta array
        $this->_params['cart'] = $this->_rta['rtaCart'];                                    // Set the sku data to the params array
    }
    
    /**
     * Add Product information to data array
     * @param Varien_Event_Observer $observer
     */ 
    public function onSearchView(Varien_Event_Observer $observer) 
    {
        $query = Mage::helper('catalogsearch/data')->getQueryText();   // Get the query from search helper
        $query = urlencode($query);
        $this->_rta['rtaSearch'] = $query;                             // Set the query to the rta array
        $this->_params['search'] = $query;                             // Set the query to the params array
    }
    
    /**
     * Add Order information to data array
     * @param Varien_Event_Observer $observer
     */
    public function onOrderSuccessPageView(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();   // Get the orderIds from the observer
        if (!empty($orderIds) && is_array($orderIds)) {     // Make sure there are order ids
            $this->_rta['rtaConvertCart'] = "1";            // Set conversion to rta array
            $this->_rta['rtaCart'] = array();               // Init the sku array
            $this->_rta['rtaCartAmounts'] = array();        // Init the amount array
            $this->_rta['rtaCartQuantities'] = array();     // Init the quantity array
            foreach ($orderIds as $oid) {                   // Loop through all order ids
                $order = Mage::getSingleton('sales/order');                 // Load order singleton
                if ($order->getId() != $oid) $order->reset()->load($oid);   // Make sure order matches order id
                $this->_rta['rtaOrderNum'] = $order->getRealOrderId();      // set order id to rta array
                foreach ($order->getAllItems() as $key => $item) {          // Loop through all items in order
                    if ($item->getParentItem() !== NULL && $item->getParentItem()->getProductType() == "configurable")
                    	continue;
                    $this->_rta['rtaCart'][] = $item->getSku();                             // Add item sku to array
                    $this->_rta['rtaCartAmounts'][] = number_format($item->getPrice(), 2);  // Add item price to array
                    $this->_rta['rtaCartQuantities'][] = $item->getQtyOrdered();            // Add item qty to array
                }
            }
            $this->_rta['rtaCart'] = implode('|', $this->_rta['rtaCart']);                      // format sku data and set to rta array
            $this->_rta['rtaCartSku'] = $this->_rta['rtaCart'];
            $this->_rta['rtaCartAmounts'] = implode('|', $this->_rta['rtaCartAmounts']);        // format price data and set to rta array
            $this->_rta['rtaCartQuantities'] = implode('|', $this->_rta['rtaCartQuantities']);  // format quantity data and set to rta array
            $this->_rta['rtaClearCart'] = "1";
            $this->_params['cart'] = $this->_rta['rtaCart'];                                    // Set the sku data to the params array
        }
    }
    
    /**
     * Observe the controller action after blocks are generated
     * @param Varien_Event_Observer $observer
     */ 
    public function onControllerActionBlocksAfter(Varien_Event_Observer $observer) 
    {
        $layout = $observer->getLayout();                       // Get the Layout
        $beforeBodyEnd = $layout->getBlock('before_body_end');  // Get before_body_end
        if (empty($beforeBodyEnd)) return;                      // before_body_end doesn't exist

        $block = $layout->createBlock('fw_igo/observationEmail','fw_igo_email');    // Create the iGoDigital Block in the layout
        if ($block) $beforeBodyEnd->append($block);     // Add iGoDigital block to before_body_end

        $block = $layout->createBlock('fw_igo/observation','fw_igo');    // Create the iGoDigital Block in the layout
        if ($block) $beforeBodyEnd->append($block);     // Add iGoDigital block to before_body_end

        $block = $layout->getBlock('igdrec');             // Get the FW_IGo_Block_Content Block
        if ($block) $block->setParams($this->_params);    // Set the params array to the Block
    }
    
    /**
     * Add information into SiteCatalyst block to render on all pages
     * @param Varien_Event_Observer $observer
     */
    public function oniGoToHtml(Varien_Event_Observer $observer)
    {
        $observer->getBlock()->setRta($this->_rta);     // Add data from observer
    }
    
}
