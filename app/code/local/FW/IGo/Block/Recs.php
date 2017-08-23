<?php
/**
 * @category    FW
 * @package     FW_IGo
 * @copyright   Copyright (c) 2015 F+W (http://www.fwcommunity.com)
 * @author		J.P. Daniel <jp.daniel@fwcommunity.com>
 */
class FW_IGo_Block_Recs extends Mage_Core_Block_Abstract
{    
    /** 
     * Array to store params for the FW_IGo_Block_Content Block
     * @var array $_params
     */
    private $_params = array();

	/**
	 * Render the iGo Recommendation Content Block
	 * @return string
	 */
	protected function _toHtml() 
	{
        /** @var FW_IGo_Helper_Data $helper */
        $helper = Mage::helper('fw_igo');     // Load the iGo helper
	    
        if (!$helper->isRecsAvailable()) return;   // iGo is disabled or missing required conf data

        return <<<HTML
<script>
(function(rid){
    Event.observe(window, 'load', function() {
        var src='//'+rid+'.recs.igodigital.com/a/v2/'+rid+'/{$this->getRecType()}/recommend.json?callback=render{$this->getNameInLayout()}{$this->getParams()}';
        var s=new Element('script',{'src':src,'async':'true'});
        $(document.body).insert({bottom:s});
    });
})('{$helper->getRetailerID()}');
function render{$this->getNameInLayout()}(recs) {
    recs.each(function(rec) {
        var elem = $(rec.name); 
        if (elem) {
            var tempRec = new Template(elem.down('.igoRecTemplate').innerHTML);
            var tempItem = new Template(elem.down('.igoItemTemplate').innerHTML);
            var tempRegPrice = new Template(elem.down('.igoRegPriceTemplate').innerHTML);
            var tempSalePrice = new Template(elem.down('.igoSalePriceTemplate').innerHTML);
            var items = '';
            rec.items.each(function(item, i) {
                if (item.regular_price) item.regular_price = item.regular_price.toFixed(2);
                if (item.regular_price > 0 && item.sale_price > 0) {
                    item.sale_price = item.sale_price.toFixed(2);
                    var save = item.regular_price - item.sale_price;
                    save = save.toFixed(2);
                    item.save = save + ' (' + Math.floor(save / item.regular_price * 100) + '%)';
                    item.price = tempSalePrice.evaluate(item);
                } else if (item.regular_price > 0) {
                    item.price = tempRegPrice.evaluate(item);                   
                }
                item.i = i + 1;
                if (!item.image_link) item.image_link = item.image2;
                items += tempItem.evaluate(item);
            });
            rec.column_count = rec.items.length;
            rec.items = items;
            elem.update(tempRec.evaluate(rec));
            elem.select('ol li, ul li').each(function(elem){
                elem.removeClassName('first');
                elem.removeClassName('last');
            });
            elem.select('ou, ul').each(function(elem){
                elem.select('li').first().addClassName('first');
                elem.select('li').last().addClassName('last'); 
            });
        }
    });
}
</script>
HTML;
	}

    /**
     * Return the params in querytring format
     * @return string
     */
    private function getParams() {
        return $this->_params ? '&' . http_build_query($this->_params) : '';
    }

    /**
     * Merge the iGo block's s array with data
     * @param array
     */
    public function setParams($data) {
        $this->_params = array_merge($this->_params, (array)$data);
    }
}