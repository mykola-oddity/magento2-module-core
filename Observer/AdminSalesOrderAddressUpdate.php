<?php

namespace Yotpo\Core\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Yotpo\Core\Model\Sync\Orders\Processor as OrdersProcessor;
use Yotpo\Core\Model\Config;
use Magento\Sales\Model\OrderRepository;

/**
 * Class SalesOrderSaveAfter
 * Observer is called when order is created/updated
 */
class AdminSalesOrderAddressUpdate implements ObserverInterface
{
    /**
     * Custom attribute name
     */
    const SYNCED_TO_YOTPO_ORDER = 'synced_to_yotpo_order';

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrdersProcessor
     */
    protected $ordersProcessor;

    /**
     * @var Config
     */
    protected $yotpoConfig;

    /**
     * AdminSalesOrderAddressUpdate constructor.
     * @param OrderRepository $orderRepository
     * @param OrdersProcessor $ordersProcessor
     * @param Config $yotpoConfig
     */
    public function __construct(
        OrderRepository $orderRepository,
        OrdersProcessor $ordersProcessor,
        Config $yotpoConfig
    ) {
        $this->orderRepository = $orderRepository;
        $this->ordersProcessor = $ordersProcessor;
        $this->yotpoConfig = $yotpoConfig;
    }

    /**
     * @param Observer $observer
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var  Order $order */
        $order = $this->orderRepository->get($observer->getOrderId());
        $this->ordersProcessor->updateOrderAttribute(
            [$order->getEntityId()],
            self::SYNCED_TO_YOTPO_ORDER,
            0
        );
        if ($this->yotpoConfig->isOrdersSyncActive($order->getStoreId())) {
            $this->ordersProcessor->processOrder($order);
        }
    }
}
