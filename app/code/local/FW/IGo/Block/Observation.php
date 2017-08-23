<?php
/**
 * @category    FW
 * @package     FW_IGo
 * @copyright   Copyright (c) 2015 F+W (http://www.fwcommunity.com)
 * @author		J.P. Daniel <jp.daniel@fwcommunity.com>
 */
class FW_IGo_Block_Observation extends Mage_Core_Block_Text
{		
    /** 
     * Array to store changes to be merged into the iGo block 
     * @var array $_s
     */
    private $_rta = array();

	/**
	 * Render the iGo Observation JS tracking tags
	 * @return string
	 */
	protected function _toHtml() 
	{
        /** @var FW_IGo_Helper_Data $helper */
        $helper = Mage::helper('fw_igo');     // Load the iGo helper
	    
        if (!$helper->isiGoAvailable()) return;   // iGo is disabled or missing required conf data

        Mage::dispatchEvent('fw_igo_to_html', array('block' => $this));   // Dispatch event before rendering HTML
        
        $this->_rta = array('rtaRetailer' => $helper->getRetailerID()) + $this->_rta;   // Merge rtaRetailerID to array

        return <<<HTML
<script>

Event.observe(window, 'load', function() {
    var doRtaCall = function () {
      if (window.rtaReadyState == true) {
        callRTA();
        return;
      } else {
        setTimeout(doRtaCall,100);
      }
    }
    s=new Element('script',{'src':'//{$helper->getRetailerID()}.collect.igodigital.com/igdrta.js','async':'true'});
    s.onload=s.onreadystatechange=function(){
        var r=this.readyState;if(r&&r!='complete'&&r!='loaded')return;
        try{{$this->getRtaCode()} doRtaCall(); }catch(e){}
    };
    $(document.head).insert({bottom:s});
});
</script>
HTML;
	}

    /**
     * Return the rta vars in JS format
     * @return string
     */
    private function getRtaCode() {
        $return = '';
        foreach ($this->_rta as $key => $value) {
            $return .= "window.{$key}=\"{$value}\";";
        }
        return $return;
    }

    /**
     * Merge the iGo block's s array with data
     * @param array
     */
    public function setRta($data) {
        $this->_rta = array_merge($this->_rta, (array)$data);
    }
}
