<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use Broadway\ReadModel\Projector;
use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasMovedToLevel;
use OpenLoyalty\Component\Customer\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelRepository;

/**
 * Class CustomersBelongingToOneLevelProjector.
 */
class CustomersBelongingToOneLevelProjector extends Projector
{
    /**
     * @var Repository
     */
    private $customerDetailsRepository;

    /**
     * @var Repository
     */
    private $customersBelongingToOneLevelRepository;

    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * CustomersBelongingToOneLevelProjector constructor.
     *
     * @param Repository      $customerDetailsRepository
     * @param Repository      $customersBelongingToOneLevelRepository
     * @param LevelRepository $levelRepository
     */
    public function __construct(
        Repository $customerDetailsRepository,
        Repository $customersBelongingToOneLevelRepository,
        LevelRepository $levelRepository
    ) {
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->customersBelongingToOneLevelRepository = $customersBelongingToOneLevelRepository;
        $this->levelRepository = $levelRepository;
    }

    /**
     * @param CustomerWasMovedToLevel $event
     */
    public function applyCustomerWasMovedToLevel(CustomerWasMovedToLevel $event)
    {
        $customerId = $event->getCustomerId();
        $levelId = $event->getLevelId();

        /** @var CustomerDetails $customer */
        $customer = $this->customerDetailsRepository->find($customerId->__toString());
        $currentLevel = $customer->getLevelId();

        if ($currentLevel) {
            $oldReadModel = $this->getReadModel($currentLevel, false);
            if ($oldReadModel) {
                $oldReadModel->removeCustomer($customer);
                $this->customersBelongingToOneLevelRepository->save($oldReadModel);
                /** @var Level $level */
                $level = $this->levelRepository->byId(new \OpenLoyalty\Component\Level\Domain\LevelId($oldReadModel->getLevelId()->__toString()));
                if ($level) {
                    $level->setCustomersCount(count($oldReadModel->getCustomers()));
                    $this->levelRepository->save($level);
                }
            }
        }

        if ($levelId) {
            $readModel = $this->getReadModel($levelId);
            $readModel->addCustomer($customer);
            $this->customersBelongingToOneLevelRepository->save($readModel);

            /** @var Level $level */
            $level = $this->levelRepository->byId(new \OpenLoyalty\Component\Level\Domain\LevelId($readModel->getLevelId()->__toString()));
            if ($level) {
                $level->setCustomersCount(count($readModel->getCustomers()));
                $this->levelRepository->save($level);
            }
        }

        $this->customerDetailsRepository->save($customer);
    }

    /**
     * @param LevelId $levelId
     * @param bool    $createIfNull
     *
     * @return null|CustomersBelongingToOneLevel
     */
    private function getReadModel(LevelId $levelId, $createIfNull = true): ? CustomersBelongingToOneLevel
    {
        $readModel = $this->customersBelongingToOneLevelRepository->find($levelId->__toString());

        if (null === $readModel && $createIfNull) {
            $readModel = new CustomersBelongingToOneLevel($levelId);
        }

        return $readModel;
    }
}
