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

class PurgeCmsCache implements ObserverInterface
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
    public function isCacheEnabled(): bool
    {
        return $this->cacheState->isEnabled(CmsData::TYPE_IDENTIFIER);
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->isCacheEnabled()) {
            $object = $observer->getEvent()->getObject();
            if ($object && $object->getData('identifier')) {
                $cacheTagId = CmsData::TAG_PREFIX . $object->getData('identifier');
                $this->cache->clean([$cacheTagId]);
            }
        }
    }
}
