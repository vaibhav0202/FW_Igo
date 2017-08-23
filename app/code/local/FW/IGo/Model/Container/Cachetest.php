<?php
/**
 * This class is used by FPC to in place of a hole in the cached HTML
 */
class FW_IGo_Model_Container_Cachetest extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Get container individual cache id
     *
     * @return string|false
     */
    protected function _getCacheId()
    {
        return $this->_placeholder->getName();		// Return the placeholder name as the cache id
    }
    
    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
      $blockName = $this->_placeholder->getAttribute('block');        // Get the block name from the placeholder
      $block = Mage::app()->getLayout()->createBlock($blockName);     // Create the block
      return $block->toHtml();
    }
    /**
     * Save data to cache storage
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param null|int $lifetime
     * @return Enterprise_PageCache_Model_Container_Abstract
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;    // Return false to disable caching of the block
    }    

}
