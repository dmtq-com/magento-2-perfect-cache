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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Layout;
use DMTQ\PerfectCache\Model\Cache\CmsData;

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
    public function isCacheEnabled(): bool
    {
        return $this->cacheState->isEnabled(CmsData::TYPE_IDENTIFIER);
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var Layout $layout */
        if ($this->isCacheEnabled()) {
            $page = $event->getPage();
            if ($page->getIdentifier() && ($page->getIdentifier() !== 'no-route')) {
                $cacheKey = CmsData::KEY_PREFIX . $page->getIdentifier()
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
