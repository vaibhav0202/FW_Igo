<?php

class FW_IGo_Export_Datafeed
{
    protected $_allStores;
    protected $_allWebsites;
    protected $_storeIds;
    protected $_mainWebsiteId;
    protected $_mainStoreId;
    protected $_exportFileName;
    protected $_exportFullFileName;
    protected $_exportDirectory;
        
    public function __construct()
    {

        $stores = Mage::getModel('core/store')->getCollection();
        $websites = Mage::getModel('core/website')->getCollection();

        foreach ($stores as $store) 
        {
            $this->_allStores[$store->getId()] = $store->getName();
            if($store->getName() == "Default Store View")
            {
                $this->_mainStoreId  = $store->getId();
            }
        }

        foreach ($websites as $website) 
        {
             $this->_allWebsites[$website->getId()] = $website->getName();
            if($website->getName() == "Main Website")
            {
               $this->_mainWebsiteId = $website->getId();
            }
        }
    }
    
    /**
     * Generates Mecent Product export file - 1 per store
     * 
     */
    public function exportProductFeed()
    {
        Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
        $errorFile = 'iGo_DataFeed_Error.log';
	 
        try 
        {       
            $stores = Mage::getModel('core/store')->getCollection();
			
            foreach ($stores as $store) 
            {
                if($store->getName() != "Default Store View")
                {
                    $this->_exportDirectory = Mage::getBaseDir().'/feed/iGo/';
                    $this->_exportFileName = str_replace(".com","", $store->getName());
                    $this->_exportFileName = str_replace(".","",  $this->_exportFileName);
                    $this->_exportFileName =  $this->_exportFileName.".txt";
                    $this->_exportFullFileName =  $this->_exportDirectory.$this->_exportFileName;
                    $exportFileHandler = fopen($this->_exportFullFileName , 'w');       
                    $fileLine = "sku\tcode\tname\ttype\tprice\tsale_price\tavailability\tlink\timage1\timage2\tcost\trelease_date\tcategory\tauthor_speaker_editor\tbrand\tcustom_design\ttarget_audience\tskill_level\ttechnique\trating\tstock_status\tshort_description\tdescription";
                    fwrite($exportFileHandler,$fileLine); 
                    fclose($exportFileHandler);
                                        
                    //load all products
                    $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(array('sku', 
                        'name',
                        'format',
                        'price',
                        'special_price',
                        'special_from_date',
                        'special_to_date', 
                        'description', 
                        'url_key',
                        'cost', 
                        'publication_date', 
                        'author_speaker_editor', 
                        'brand',
                        'for',
                        'skill_level',
                        'technique',
                        'short_description',
                        'description',
                        'type_id',
                        'category_ids',
                        'visibility',
                        'custom_design',
                        'sold_by_length',
                        'image'
                        ), 'left')
                        ->addAttributeToFilter('status','1')
                        ->addStoreFilter($store->getId())
                        ->joinTable('cataloginventory/stock_item', 'product_id=entity_id', array("stock_status" => "is_in_stock", "manage_stock" => "manage_stock"))
                        ->addAttributeToSelect('stock_status')
                        ->addAttributeToSelect('manage_stock')
                        ->addAttributeToSelect('vote_count')
                        ->addAttributeToSelect('vote_value_sum');

                    Mage::getSingleton('core/resource_iterator')->walk($products->getSelect(),  array(array($this, 'callBackProductFeed')),array('storeName' => $store->getName(), 'storeId' => $store->getId()));

                try
                {
                    $filesToSend[$this->_exportFileName] = $this->_exportFullFileName;
                    unset($products);
                }
                catch(Exception $e)
                { 
                    Mage::log("iGo Export Error: ".$e->getMessage(),null, $errorFile);
                    $this->sendErrorEmail($e->getMessage());
                } 
            }
        }

	//Send the files
	try
	{
		$this->ftpFiles($filesToSend,$errorFile);
		unset($products);
	}
	catch(Exception $e)
	{
		Mage::log("iGo Export Error: ".$e->getMessage(),null, $errorFile);
		$this->sendErrorEmail($e->getMessage());
	}

    } 
    catch (Exception $e) 
    {
        Mage::log("iGo Export Error: ".$e->getMessage(),null, $errorFile);
        $this->sendErrorEmail($e->getMessage());
    }
}

    /**
     * GeneratesProduct export file - 1 per store
     * 
     */
    public function exportZirconProductFeed(){
        Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
        $errorFile = 'iGo_DataFeed_Zircon_Error.log';
	 
        try {
            $stores = Mage::getModel('core/store')->getCollection();
			
            foreach ($stores as $store) {
                if($store->getName() != "Default Store View"){
                    $this->_exportDirectory = Mage::getBaseDir().'/feed/iGo/';
                    $this->_exportFileName = str_replace(".com","", $store->getName());
                    $this->_exportFileName = str_replace(".","",  $this->_exportFileName);
                    $this->_exportFileName =  $this->_exportFileName.".txt";
                    $this->_exportFullFileName =  $this->_exportDirectory.$this->_exportFileName;
                    $exportFileHandler = fopen($this->_exportFullFileName , 'w');       
                    $fileLine = "sku\tcode\tname\ttype\tprice\tsale_price\tavailability\tlink\timage1\timage2\tcost\trelease_date\tcategory\tauthor_speaker_editor\tbrand\tcustom_design\ttarget_audience\tskill_level\ttechnique\trating\tstock_status\tshort_description\tdescription";
                    fwrite($exportFileHandler,$fileLine); 
                    fclose($exportFileHandler);
                                        
                    //load all products
                    $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(array('sku', 
                        'name',
                        'format',
                        'price',
                        'special_price',
                        'description', 
                        'url_key',
                        'warehouse_avail_date', 
                        'technique',
                        'short_description',
                        'description',
                        'type_id',
                        'category_ids',
                        'visibility',
                        'sold_by_length',
                        'image'
                        ), 'left')
                        ->addAttributeToFilter('status','1')
                        ->addStoreFilter($store->getId())
                        ->joinTable('cataloginventory/stock_item', 'product_id=entity_id', array("stock_status" => "is_in_stock", "manage_stock" => "manage_stock"))
                        ->addAttributeToSelect('stock_status')
                        ->addAttributeToSelect('manage_stock')
                        ->addAttributeToSelect('vote_count')
                        ->addAttributeToSelect('vote_value_sum');

                    Mage::getSingleton('core/resource_iterator')->walk($products->getSelect(),  array(array($this, 'callBackZirconProductFeed')),array('storeName' => $store->getName(), 'storeId' => $store->getId()));

                    try{
                        $filesToSend[$this->_exportFileName] = $this->_exportFullFileName;
                        unset($products);
                    }
                    catch(Exception $e){ 
                        Mage::log("iGo Export Error: ".$e->getMessage(),null, $errorFile);
                        $this->sendErrorEmail($e->getMessage());
                    } 
                }
            }

            //Send the files
            try{
                $this->ftpFiles($filesToSend,$errorFile);
                unset($products);
            }
            catch(Exception $e){
                    Mage::log("iGo Export Error: ".$e->getMessage(),null, $errorFile);
                    $this->sendErrorEmail($e->getMessage());
            }

        } 
        catch (Exception $e) {
            Mage::log("iGo Export Error: ".$e->getMessage(),null, $errorFile);
            $this->sendErrorEmail($e->getMessage());
        }
    }
    
   
    /**
    * Callback for each iteration of the product collection, specific to the Product Feed - writes to the output file
     * Each time this call back is invoked for each product in the product collection, the  output file is appended for each product
     * The function assembles a single line that contains a delimited product attribute list for the output file
    */
    public function callBackProductFeed($args)
    {
        $product = Mage::getModel('catalog/product')->setData($args['row']);
        $fileLine = "";
        $exportFileHandler = fopen($this->_exportFullFileName , 'a');
        $productStoreIds = $product->getStoreIds();

        //Only process products that are in stores other than the Main Website and are flagged to be in this feed
        //No products that are no visible individually
        if($product->getSoldByLength() != 1 && ($product->getVisibility() != 1 && ((count($productStoreIds) == 1 && $productStoreIds[0] != $this->_mainStoreId) || count($productStoreIds) > 1)))
        {
            $iGoHelper = Mage::helper('fw_igo');
            $cdnAddress = $iGoHelper->getImageCdnAddress();

            //Sku
            $sku = $product->getSku();
            $sku = str_replace("\t", "&#09;", $sku);
            $sku = str_replace("\"", "&#34;", $sku);
            $sku = str_replace("'", "&#39;", $sku);
            $fileLine = $sku;

            //Sku = 'code'
            $fileLine = $fileLine."\t".$sku;
            
            //Name
            $name = $product->getName();
            $name = str_replace("\t", "&#09;", $name);
            $name = str_replace("\"", "&#34;", $name);
            $name = str_replace("'", "&#39;", $name);
            $fileLine = $fileLine."\t".$name;
            
            //Format == 'type'
            $format = $this->getAttributeLabel('format', $product->getFormat());
            $fileLine = $fileLine."\t".$format;
            
            //Price
            //If the product type is a bundle then have to load the full product to get the correct price
            $price = $product->getPrice();
            if($product->getTypeId() == 'bundle')
            {
                $productTemp = Mage::getModel('catalog/product')->load($product->getId());
                $statusObj = new Mage_CatalogInventory_Model_Stock_Status();
                $bundleStockStatusObj = $statusObj->getProductStatus($product->getId(), Mage::app()->getStore($args['storeId'])->getWebsiteId(), 1);
                $bundleStockStatus = $bundleStockStatusObj[$product->getId()];
                $price = $productTemp->getPrice();
            }
            $fileLine = $fileLine."\t".str_replace(",", "", number_format($price, 2));
            
            //Specical Price
            $specialPriceFromDate = strtotime($product->getSpecialFromDate());
            $specialPriceToDate = strtotime($product->getSpecialToDate());
            $specialPrice = "";

            if($specialPriceFromDate == false)
            {
                if($specialPriceToDate == false)
                {
                   $specialPrice = $product->getSpecialPrice();
                }
                else 
                {
                    if(strtotime("now") <= $specialPriceToDate)
                    {
                        $specialPrice = $product->getSpecialPrice();
                    }
                }
            }
            else if($specialPriceToDate == false)
            {
                if($specialPriceFromDate == false)
                {
                   $specialPrice = $product->getSpecialPrice();
                }
                else 
                {
                    if(strtotime("now") >= $specialPriceFromDate)
                    {
                        $specialPrice = $product->getSpecialPrice();
                    }
                }
            }
            else
            {
                if(strtotime("now") < $specialPriceToDate && strtotime("now") > $specialPriceFromDate)
                {
                    $specialPrice = $product->getSpecialPrice();
                }
            }
            
            if($product->getTypeId() == 'bundle' && $specialPrice != "")
            {
                $specialPrice = number_format(($productTemp->getSpecialPrice() / 100)  * $productTemp->getPrice(), 2); //always a percentage special price adjustment
            }
            
                
            $productRulePrice = Mage::getResourceModel('catalogrule/rule')->getRulePrice(Mage::app()->getLocale()->storeTimeStamp($args['storeId']), Mage::app()->getStore($args['storeId'])->getWebsiteId(), 0,$product->getId());
                              
            if($productRulePrice != FALSE)
            {
                $specialPrice = $productRulePrice;
            }
            
            //if special price is not valid then dont cast to formated number
            if($specialPrice != "")
            {
                $fileLine = $fileLine."\t".str_replace(",", "", number_format($specialPrice, 2));
            }
            else
            {
                $fileLine = $fileLine."\t".str_replace(",", "", $specialPrice);
            }
            
            //Availability
            $avail = "N";

            if($product->getVisibility() == 4 && ($product->getStockStatus() == 1 || $product->getManageStock() == 0))
            {
                if($product->getTypeId() == 'bundle' && $bundleStockStatus == 0){
                    $avail = "N";
                }else{
                    $avail = "Y";
                }
            }

            $fileLine = $fileLine."\t".$avail;
            
            //Url key
            $fileLine = $fileLine."\t"."http://".$args['storeName']."/".$product->getUrlKey();
            
            //Images
            $baseImgeUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage() );
            $baseLocation = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
 
            //Small Image
            $baseImgeUrl = str_replace($baseLocation, "", $baseImgeUrl);
            $imgUrl = $cdnAddress . "/image-server/120x120/".$baseImgeUrl;
            $fileLine = $fileLine."\t".$imgUrl;

            //Larger Image
            $imgUrl = $cdnAddress . "/image-server/200x200/".$baseImgeUrl;
            $fileLine = $fileLine."\t".$imgUrl;
            
            //Cost
            $fileLine = $fileLine."\t".number_format($product->getCost(), 2);
            
            //Publication Date = 'release date'
            $fileLine = $fileLine."\t".$product->getPublicationDate();
            
            //Category Ids
            $this->addAllCategoryIdsToProduct($product, $args['storeId']);
                  
            $catIdStr = "";
  
            foreach ($product->getAllCategories() as $aCat) 
            {
                $catIdStr = $catIdStr.$aCat['category_id']."~";
            }

            if($catIdStr != "")
            {
                $catIdStr = substr($catIdStr, 0, -1);
            }
        
            $fileLine = $fileLine."\t".$catIdStr;
            
            //Author Speaker Editor
            $authorSpeakerEditor = $product->getAuthorSpeakerEditor();
            $authorSpeakerEditor = str_replace("\t", "&#09;", $authorSpeakerEditor);
            $authorSpeakerEditor = str_replace("\"", "&#34;", $authorSpeakerEditor);
            $authorSpeakerEditor = str_replace("'", "&#39;", $authorSpeakerEditor);
            $fileLine = $fileLine."\t".$authorSpeakerEditor; 
            
            //Brand
            $brand = $product->getBrand();
            $brand = str_replace("\t", "&#09;", $brand);
            $brand = str_replace("\"", "&#34;", $brand);
            $brand = str_replace("'", "&#39;", $brand);
            $fileLine = $fileLine."\t".$brand; 
            
            //Custom Design - have to try to load the store view value first
            $custDesignProduct = Mage::getModel('catalog/product')->setStoreId($args['storeId'])->load($product->getId());
            $custDesign = $custDesignProduct->getCustomDesign();
            
            if(!$custDesign){
                $custDesign = $product->getCustomDesign();
            }
            $fileLine = $fileLine."\t".$custDesign;
            
            //For = 'target audience'
            $fileLine = $fileLine."\t".$product->getFor();
            
            //Skill Level
            $fileLine = $fileLine."\t".$product->getSkillLevel(); 
            
            //Technique
            $fileLine = $fileLine."\t".$product->getTechnique(); 
            
            //Rating
            $rating ="";
            $this->addRatingVoteAggregateToProduct($product, $args['storeId']);
            
            if($product->getVotes())
            {
                $votes = $product->getVotes();
                $voteValueSum = $votes['vote_value_sum'];
                $voteCount = $votes['vote_count'];
                $rating = number_format($voteValueSum/$voteCount,1);
            }
            $fileLine = $fileLine."\t".$rating;  
           
            //Stock Status
            $fileLine = $fileLine."\t".$product->getStockStatus();

            //Description
            $shortDescription = $product->getShortDescription();
            $shortDescription = str_replace(array("\r"), '', $shortDescription);
            $shortDescription = str_replace(PHP_EOL, '', $shortDescription);
            $shortDescription = str_replace("\t", "&#09;", $shortDescription);
            $shortDescription = str_replace("\"", "&#34;", $shortDescription);
            $shortDescription = str_replace("'", "&#39;", $shortDescription);
            $shortDescription = trim($shortDescription);
            $fileLine = $fileLine."\t".$shortDescription;
            
            //Description
            $description = $product->getDescription();
            $description = str_replace(array("\r"), '', $description);
            $description = str_replace("\n", '', $description);
            $description = str_replace(PHP_EOL, '', $description);
            $description = str_replace("\t", "&#09;", $description);
            $description = str_replace("\"", "&#34;", $description);
            $description = str_replace("'", "&#39;", $description);
            $description = trim($description);
            $fileLine = "\r".$fileLine."\t".$description;
 
            fwrite($exportFileHandler,$fileLine); 
        }

        fclose($exportFileHandler);
    }
    
        /**
        * Callback for each iteration of the product collection, specific to the Product Feed - writes to the output file
         * Each time this call back is invoked for each product in the product collection, the  output file is appended for each product
         * The function assembles a single line that contains a delimited product attribute list for the output file
        */
    public function callBackZirconProductFeed($args){
            $product = Mage::getModel('catalog/product')->setData($args['row']);
            $fileLine = "";
            $exportFileHandler = fopen($this->_exportFullFileName , 'a');
            $productStoreIds = $product->getStoreIds();

            if($product->getSoldByLength() == 1){
                return;
            }
            //Only process products that are in stores other than the Main Website and are flagged to be in this feed
            //No products that are no visible individually
            if(($product->getVisibility() != 1 && ((count($productStoreIds) == 1 && $productStoreIds[0] != $this->_mainStoreId) || count($productStoreIds) > 1))){

                $iGoHelper = Mage::helper('fw_igo');
                $cdnAddress = $iGoHelper->getImageCdnAddress();

                //Sku
                $sku = $product->getSku();
                $sku = str_replace("\t", "&#09;", $sku);
                $sku = str_replace("\"", "&#34;", $sku);
                $sku = str_replace("'", "&#39;", $sku);
                $fileLine = $sku;

                //Sku = 'code'
                $fileLine = $fileLine."\t".$sku;

                //Name
                $name = $product->getName();
                $name = str_replace("\t", "&#09;", $name);
                $name = str_replace("\"", "&#34;", $name);
                $name = str_replace("'", "&#39;", $name);
                $fileLine = $fileLine."\t".$name;

                //Format == 'type'
                $format = $this->getAttributeLabel('format', $product->getFormat());
                $fileLine = $fileLine."\t".$format;

                //Price
                //If the product type is a bundle then have to load the full product to get the correct price
                $price = $product->getPrice();
                if($product->getTypeId() == 'bundle'){
                   $statusObj = new Mage_CatalogInventory_Model_Stock_Status();
                   $bundleStockStatusObj = $statusObj->getProductStatus($product->getId(), Mage::app()->getStore($args['storeId'])->getWebsiteId(), 1);
                   $bundleStockStatus = $bundleStockStatusObj[$product->getId()];
                   $productTemp = Mage::getModel('catalog/product')->load($product->getId()); 
                   $price = $productTemp->getPrice();
                }

                $fileLine = $fileLine."\t".str_replace(",", "", number_format($price, 2));

                //Specical Price
                $specialPrice = $product->getSpecialPrice();

                if($product->getTypeId() == 'bundle' && $specialPrice != ""){
                    $specialPrice = number_format(($productTemp->getSpecialPrice() / 100)  * $productTemp->getPrice(), 2); //always a percentage special price adjustment
                }

                $productRulePrice = Mage::getResourceModel('catalogrule/rule')->getRulePrice(Mage::app()->getLocale()->storeTimeStamp($args['storeId']), Mage::app()->getStore($args['storeId'])->getWebsiteId(), 0,$product->getId());

                if($productRulePrice != FALSE){
                    $specialPrice = $productRulePrice;
                }

                //if special price is not valid then dont cast to formated number
                if($specialPrice != ""){
                    $fileLine = $fileLine."\t".str_replace(",", "", number_format($specialPrice, 2));
                }
                else
                {
                    $fileLine = $fileLine."\t".str_replace(",", "", $specialPrice);
                }

                //Availability
                $avail = "N";

                if($product->getVisibility() == 4 && ($product->getStockStatus() == 1 || $product->getManageStock() == 0))
                {
                    if($product->getTypeId() == 'bundle' && $bundleStockStatus == 0){
                        $avail = "N";   
                    }else{
                        $avail = "Y";    
                    } 
                }
                $fileLine = $fileLine."\t".$avail;

                //Url key
                $fileLine = $fileLine."\t"."http://".$args['storeName']."/".$product->getUrlKey();

                //Images
                $baseImgeUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage() );
                $baseLocation = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

                //Small Image
                $baseImgeUrl = str_replace($baseLocation, "", $baseImgeUrl);
                $imgUrl = $cdnAddress . "/image-server/120x120/".$baseImgeUrl;
                $fileLine = $fileLine."\t".$imgUrl;

                //Larger Image
                $imgUrl = $cdnAddress . "/image-server/200x200/".$baseImgeUrl;
                $fileLine = $fileLine."\t".$imgUrl;

                //Cost
                $fileLine = $fileLine."\t";

                //WarehouseAvailabilty Date = 'release date'
                $fileLine = $fileLine."\t".$product->getWarehouseAvailabiltyDate();

                //Category Ids
                $this->addAllCategoryIdsToProduct($product, $args['storeId']);

                $catIdStr = "";

                foreach ($product->getAllCategories() as $aCat) 
                {
                    $catIdStr = $catIdStr.$aCat['category_id']."~";
                }

                if($catIdStr != "")
                {
                    $catIdStr = substr($catIdStr, 0, -1);
                }

                $fileLine = $fileLine."\t".$catIdStr;

                //Author Speaker Editor
                $fileLine = $fileLine."\t"; 

                //Brand
                $fileLine = $fileLine."\t"; 

                //Custom Design
                $fileLine = $fileLine."\t";

                //For = 'target audience'
                $fileLine = $fileLine."\t";

                //Skill Level
                $fileLine = $fileLine."\t".$product->getSkillLevel(); 

                //Technique
                $fileLine = $fileLine."\t".$product->getTechnique(); 

                //Rating
                $rating ="";
                $this->addRatingVoteAggregateToProduct($product, $args['storeId']);

                if($product->getVotes())
                {
                    $votes = $product->getVotes();
                    $voteValueSum = $votes['vote_value_sum'];
                    $voteCount = $votes['vote_count'];
                    $rating = number_format($voteValueSum/$voteCount,1);
                }
                $fileLine = $fileLine."\t".$rating;  

                //Stock Status
                $fileLine = $fileLine."\t".$product->getStockStatus();

                //Short Description
                $shortDescription = $product->getShortDescription();
                $shortDescription = str_replace(array("\r"), '', $shortDescription);
                $shortDescription = str_replace(PHP_EOL, '', $shortDescription);
                $shortDescription = str_replace("\t", "&#09;", $shortDescription);
                $shortDescription = str_replace("\"", "&#34;", $shortDescription);
                $shortDescription = str_replace("'", "&#39;", $shortDescription);
                $shortDescription = trim($shortDescription);
                $fileLine = $fileLine."\t".$shortDescription;

                //Description
                $description = $product->getDescription();
                $description = str_replace(array("\r"), '', $description);
                $description = str_replace("\n", '', $description);
                $description = str_replace(PHP_EOL, '', $description);
                $description = str_replace("\t", "&#09;", $description);
                $description = str_replace("\"", "&#34;", $description);
                $description = str_replace("'", "&#39;", $description);
                $description = trim($description);
                $fileLine = "\r".$fileLine."\t".$description;

                fwrite($exportFileHandler,$fileLine); 
            }

            fclose($exportFileHandler);
        }
    
    
    /**
    * Manually add the Rating to a Product
    * 
    * 
    */
    function addRatingVoteAggregateToProduct(Mage_Catalog_Model_Product $product, $storeId) 
    {
        $read = Mage::getSingleton('core/resource')->getConnection('catalog_read');
        $ratingValues = $read->fetchAll('SELECT rova.vote_count, rova.vote_value_sum  FROM `rating_option_vote_aggregated` AS `rova`WHERE rova.entity_pk_value = ' . $product->getId() . ' AND rova.store_id = ' . $storeId);
        
        if($ratingValues)
        {
            $product->setData('votes',$ratingValues[0]);
            $product->setData('vote_value_sum', $ratingValues[1]);
        } 
    }
    
    
    /**
    * Generate the product image
    * 
    * 
    */
    private function getImage($image, $width, $height)
    {
        try
        {
            $img = Mage::getModel('catalog/product_image')
                    ->setWatermark( Mage::getStoreConfig('catalog/watermark/image') )
                    ->setDestinationSubdir('image')
                    ->setKeepFrame(isset($width, $height));
            if ($width) $img->setWidth($width);
            if ($height) $img->setHeight($height);
            $img->setBaseFile($image);
            if (!$img->isCached()) $img->resize()->saveFile();
            return $img;
        }
        catch( Exception $e )
        {
            $this->_forward('noRoute');
        }
    }
     
     /**
	 * Manually add the Category Ids (including anchor categories) to a Product
	 * 
	 * 
	 */
    function addAllCategoryIdsToProduct(Mage_Catalog_Model_Product $product, $storeId) 
    {
        $read = Mage::getSingleton('core/resource')->getConnection('catalog_read');
        $allCatIds = $read->fetchAll('SELECT category_id FROM catalog_category_product_index AS ccpi WHERE ccpi.product_id = ' . $product->getId() . ' AND ccpi.store_id = ' . $storeId);
        
        if($allCatIds)
        {
            $product->setData('all_categories',$allCatIds);
        } 
    }

   
     /**
	 * Get the label for a option value
	 * 
	 * 
	 */
    function getAttributeLabel($arg_attribute, $arg_value)
    {
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);
        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);
        
        foreach($options as $option)
        {	
            if ($option['value'] == $arg_value)
            {
                return $option['label'];
            }
        }

        return false;
    }
    
    /**
     * Send Email signifing error occured
     */
    public function sendErrorEmail($error)
    {
        //EMAIL ERROR NOTICE
        $to = Mage::getStoreConfig('thirdparty/iGo/emailnotice');
        $subject = "Data Feed Error (iGo)";
        $body = $error;
        mail($to, $subject, $body);
    }
    
     /**
     * Send files generated via FTP
	 * @param $filesToSend Array
	 * @param $errorFile string
     */
    private function ftpFiles($filesToSend,$errorFile)
    {
            # ftp-login
            $iGoHelper = Mage::helper('fw_igo');
            $ftp_server = $iGoHelper->getFtpHost();
            $ftp_user = $iGoHelper->getFtpUser();
            $ftp_pw = $iGoHelper->getFtpPassword();
            $ftp_dir = $iGoHelper->getFtpLocation();

            // set up basic connection
            $conn_id = ftp_connect($ftp_server);

            // login with username and password
            if($conn_id == false)
            {
                echo "Connection to ftp server failed\n";
                Mage::log('Connection to ftp server failed',null,$errorFile);
                $this->sendErrorEmail('Connection to ftp server failed');
                return;
            }

            // login with username and password
            $login_result = ftp_login($conn_id, $ftp_user, $ftp_pw);
            
            if($login_result == false) 
            { 
                echo "Login to ftp server failed\n"; 
                Mage::log('Login to ftp server failed\r\n',null,$errorFile); 
                $this->sendErrorEmail('Login to ftp server failed');
                return; 
            }

            // turn passive mode on
            ftp_pasv($conn_id, true);
            if($ftp_dir != null && $ftp_dir != "") {
                ftp_chdir($conn_id, $ftp_dir);
            }

            foreach($filesToSend as $fileName=>$fullFileName)
            {
                $filePut = ftp_put($conn_id, $fileName, $fullFileName, FTP_BINARY, 0) ;
                
                if($filePut == false) 
                {
                    Mage::log('File PUT failed\r\n',null,$errorFile); 
                    $this->sendErrorEmail('File PUT failed');
                }
            }
            
            // close the connection
            ftp_close($conn_id);
    }    
}


