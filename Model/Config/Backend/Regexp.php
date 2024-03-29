<?php
/**
 * Copyright © 2016 ToBai. All rights reserved.
 */
namespace Tobai\GeoStoreSwitcher\Model\Config\Backend;

class Regexp extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    private $config;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
        $this->config = $config;
        parent::__construct($context, $registry, $config, $cacheTypeList,$resource, $resourceCollection, $data);
    }


    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $creator = $objectManager->create('\Magento\Framework\App\Config\Storage\WriterInterface');
//        $creator->save('tobai_geo_store_switcher/BD/store',1,'default',1);
        $request = $objectManager->create('\Magento\Framework\App\RequestInterface');
        $generalConfig = $objectManager->create('\Tobai\GeoStoreSwitcher\Model\Config\General');
        $countries = $this->config->getValue('tobai_geo_store_switcher/general/country_list');
        if(!empty($countries)){
           $countryCodes = explode(',',$countries);
           foreach($countryCodes as $countrCod) {
              $path = 'tobai_geo_store_switcher/'.$countrCod.'/store';
             $creator->save($path,1,'default',1);
           }          
        }
        $submittedValues = $request->getPost();
        $groups = $submittedValues->groups;
        $countries = $groups['general']['fields']['country_list']['value'];
        foreach($countries as $countryCode) {
         if(isset($groups[$countryCode])) {
          $values = $groups[$countryCode]['fields']['store']['value'];
          $path = 'tobai_geo_store_switcher/'.$countryCode.'/store';
          //if(isset($values)) {
            $creator->save($path,$values,'default',1);
         }
        }
        if (!empty($this->getValue()) && !$this->_isRegexp($this->getValue())) {
            $this->messageManager->addNotice(
                    __('Invalid regular expression: %value', ['value' => $this->getValue()])
            );
            $this->setValue(null);
        }
        return parent::beforeSave();
    }

    /**
     * @param string $expression
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException on invalid regular expression
     */
    protected function _isRegexp($expression)
    {
        return @preg_match($expression, '') !== false;
    }
}
