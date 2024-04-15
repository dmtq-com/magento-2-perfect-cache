<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace DMTQ\PerfectCache\Observer;

use Magento\Cms\Helper\Page;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Layout;
use Magento\Store\Model\ScopeInterface;
use DMTQ\PerfectCache\Model\Cache\CmsData;

class CacheProcessLayoutRenderElement implements ObserverInterface
{

    /**
     * Request
     * @var RequestInterface
     */
    protected RequestInterface $_request;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @var StateInterface
     */
    protected StateInterface $cacheState;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * Class constructor
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param CacheInterface $cache
     * @param StateInterface $cacheState
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        CacheInterface $cache,
        StateInterface $cacheState,
        StoreManagerInterface $storeManager,
    )
    {
        $this->_request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->storeManager = $storeManager;
    }

    /**
     * Cache enabled
     *
     * @return bool
     */
    public function isCmsDataCacheEnabled(): bool
    {
        return $this->cacheState->isEnabled(CmsData::TYPE_IDENTIFIER);
    }

    /**
     * Add comment cache containers to private blocks
     * Blocks are wrapped only if page is cacheable
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        /** @var Layout $layout */
        $layout = $event->getLayout();
        if ($this->isCmsDataCacheEnabled() && $layout->isCacheable()) {
            $name = $event->getElementName();
            $transport = $event->getTransport();
            $output = $transport->getData('output');

            if ($output && $name === 'cms_page' && $this->_request->getModuleName() === 'cms') {
                $pageId = trim($this->_request->getOriginalPathInfo(), '/');
                if (!$pageId && $this->_request->getControllerName() === 'index' && $this->_request->getActionName() === 'index') {
                    $pageId = $this->scopeConfig->getValue(Page::XML_PATH_HOME_PAGE, ScopeInterface::SCOPE_STORE);
                }
                if ($pageId && $this->_request->getControllerName() !== 'noroute') {
                    $cacheKey = $this->_request->getModuleName()
                        . '_' . $pageId
                        . '_' . $this->storeManager->getStore()->getCurrentCurrencyCode()
                        . '_' . $this->storeManager->getStore()->getId();
                    $cacheTag = CmsData::CACHE_TAG;
                    $cacheData = $this->cache->load($cacheKey);
                    if (!$cacheData) {
                        $this->cache->save(
                            $output,
                            $cacheKey,
                            [$cacheTag],
                            86400

                        );
                    }
                }
            }
        }
    }
}
