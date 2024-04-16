<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace DMTQ\PerfectCache\Observer;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use DMTQ\PerfectCache\Model\Cache\CmsData;
use DMTQ\PerfectCache\Model\Cache\ProductData;

class PurgeProductCache implements ObserverInterface
{

    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @var StateInterface
     */
    protected StateInterface $cacheState;


    /**
     * Class constructor
     * @param CacheInterface $cache
     * @param StateInterface $cacheState
     */
    public function __construct(
        CacheInterface $cache,
        StateInterface $cacheState,
    )
    {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
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
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->isCmsDataCacheEnabled()) {
            $product = $observer->getEvent()->getProduct();
            if ($product && $product->getId()) {
                $cacheTagId = ProductData::TAG_PREFIX . $product->getId();
                $this->cache->clean([$cacheTagId]);
            }
        }
    }
}
