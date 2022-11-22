<?php

namespace James\OrderSaver\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class onOrder implements ObserverInterface {
	
	const ENABLED = 'ordersaver/general/enabled';
	const FILENAME_PATH = 'ordersaver/general/filename_path';

	/**
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface
	 * @param \Magento\Framework\Filesystem\DirectoryList
	 */
    public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration,
		\Magento\Framework\Filesystem\DirectoryList $directoryList
	) {
		$this->_scopeConfiguration = $scopeConfiguration;
		$this->_directoryList = $directoryList;
    }

	/** @return string */
	public function getMagentoRootFolder() {
		return $this->_directoryList->getRoot();
	}
	
	/** @return bool */
	public function getEnabled() {
	    return $this->_scopeConfiguration->getValue(self::ENABLED, ScopeInterface::SCOPE_STORE) == 1;
	}

	/** @return string */
	public function getFilenamePath() {
	    return $this->_scopeConfiguration->getValue(self::FILENAME_PATH, ScopeInterface::SCOPE_STORE);
	}

	/**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer ) {        

		if ($this->getEnabled()) {

			/** @var $order \Magento\Sales\Model\Order */
			$order = $observer->getEvent()->getOrder();
				
			/** @var $filename string */
			$filename=$this->getFilenamePath();
			$filename=str_replace("MAGENTO_ROOT",$this->getMagentoRootFolder(),$filename);
			
			// append order ID and total cost to file
			$fp=fopen($filename,"a");
			if ($fp !== false) {
				fputs($fp,"Order ID:".$order->getIncrementId());
				fputs($fp,"\n");
				fputs($fp,"Total Cost:".$order->getGrandTotal());
				fputs($fp,"\n");
				fclose($fp);
			}

		}
    }
}
