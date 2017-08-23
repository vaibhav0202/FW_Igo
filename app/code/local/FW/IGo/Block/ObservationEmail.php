<?php
/**
 * @category    FW
 * @package     FW_IGo
 * @copyright   Copyright (c) 2015 F+W (http://www.fwcommunity.com)
 * @author		J.P. Daniel <jp.daniel@fwcommunity.com>
 */
class FW_IGo_Block_ObservationEmail extends Mage_Core_Block_Text
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

        $customer = Mage::helper('customer')->getCustomer();                    // Get the customer object
        if ($email = $customer->getEmail()) $this->_rta['rtaEmail'] = $email;   // Get the customer's email address
        $this->_rta['rtaReadyState'] = TRUE;
        return "<script>".$this->getRtaCode()."</script>";
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

}
