<?php
/**
 * @category    FW
 * @package     FW_IGo
 * @copyright   Copyright (c) 2013 F+W Media, Inc. (http://www.fwmedia.com)
 * @author      J.P. Daniel <jp.daniel@fwmedia.com>
 */
class FW_IGo_Helper_Data extends Mage_Core_Helper_Abstract 
{
    /**
     * Config path for using throughout the code
     * @var string $XML_PATH
     */
    const XML_PATH  = 'thirdparty/iGo/';
    
    /**
     * Whether iGoDigital is enabled
     *
     * @param mixed $store
     * @return bool
     */
    public function isiGoEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH.'active', $store);
    }
    
    /**
     * Whether iGoDigital Recommendations is enabled
     *
     * @param mixed $store
     * @return bool
     */
    public function isRecsEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH.'recs', $store);
    }

    /**
     * Get the iGoDigital rtaRetailer ID
     *
     * @param mixed $store
     * @return string
     */
    public function getRetailerID($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH.'rid', $store);
    }

    /**
     * Whether iGoDigital is ready to use
     *
     * @param mixed $store
     * @return bool
     */
    public function isiGoAvailable($store = null)
    {
        return $this->isiGoEnabled($store) && $this->getRetailerID($store);
    }

    /**
     * Whether iGoDigital Recommendations is ready to use
     *
     * @param mixed $store
     * @return bool
     */
    public function isRecsAvailable($store = null)
    {
        return $this->isRecsEnabled($store) && $this->getRetailerID($store);
    }

    /**
     * Get the FTP Host
     * @param mixed $store
     * @return string
     */
    public function getFtpHost($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH.'ftp_host', $store);
    }

    /**
     * Get the FTP User
     * @param mixed $store
     * @return string
     */
    public function getFtpUser($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH.'ftp_user', $store);
    }

    /**
     * Get the FTP Password
     * @param mixed $store
     * @return string
     */
    public function getFtpPassword($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH.'ftp_password', $store);
    }

    /**
     * Get the FTP Location
     * @param mixed $store
     * @return string
     */
    public function getFtpLocation($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH.'ftp_location', $store);
    }

    /**
     * Get the Image CDN Address
     * @param mixed $store
     * @return string
     */

    public function getImageCdnAddress($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH.'image_cdn_address', $store);
    }

    /**
     * Get the Email Notice Address(s)
     * @param mixed $store
     * @return string
     */
    public function getEmailNotice($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH.'emailnotice', $store);
    }
}
