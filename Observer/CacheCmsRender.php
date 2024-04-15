<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace DMTQ\PerfectCache\Observer;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Layout;

class CacheCmsRender implements ObserverInterface
{

    /**
     * Request
     * @var RequestInterface
     */
    protected RequestInterface $_request;

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
     * @param CacheInterface $cache
     * @param StateInterface $cacheState
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RequestInterface $request,
        CacheInterface $cache,
        StateInterface $cacheState,
        StoreManagerInterface $storeManager,
    )
    {
        $this->_request = $request;
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
        return $this->cacheState->isEnabled(\DMTQ\PerfectCache\Model\Cache\CmsData::TYPE_IDENTIFIER);
    }

    /**
     * Add comment cache containers to private blocks
     * Blocks are wrapped only if page is cacheable
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var Layout $layout */
        if ($this->isCmsDataCacheEnabled()) {
            $page = $event->getPage();
            if ($page->getIdentifier() && ($page->getIdentifier() !== 'no-route')) {
                $cacheKey = $this->_request->getModuleName()
                    . '_' . $page->getIdentifier()
                    . '_' . $this->storeManager->getStore()->getCurrentCurrencyCode()
                    . '_' . $this->storeManager->getStore()->getId();
                $data = $this->cache->load($cacheKey);
                if ($data) {
                    $page->setContent($data);
                }

            }
        }
    }
}
