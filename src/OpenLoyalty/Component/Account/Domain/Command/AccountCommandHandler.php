<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Component\Account\Domain\Account;
use OpenLoyalty\Component\Account\Domain\AccountRepository;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountCreatedSystemEvent;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountSystemEvents;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AvailablePointsAmountChangedSystemEvent;

/**
 * Class AccountCommandHandler.
 */
class AccountCommandHandler extends SimpleCommandHandler
{
    /**
     * @var AccountRepository
     */
    protected $repository;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * AccountCommandHandler constructor.
     *
     * @param AccountRepository $repository
     * @param EventDispatcher   $eventDispatcher
     */
    public function __construct(AccountRepository $repository, EventDispatcher $eventDispatcher = null)
    {
        $this->repository = $repository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleCreateAccount(CreateAccount $command)
    {
        /** @var Account $account */
        $account = Account::createAccount($command->getAccountId(), $command->getCustomerId());
        $this->repository->save($account);
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(
                AccountSystemEvents::ACCOUNT_CREATED,
                [new AccountCreatedSystemEvent($account->getId(), $command->getCustomerId())]
            );
        }
    }

    public function handleAddPoints(AddPoints $command)
    {
        /** @var Account $account */
        $account = $this->repository->load($command->getAccountId());
        $account->addPoints($command->getPointsTransfer());
        $this->repository->save($account);
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(
                AccountSystemEvents::AVAILABLE_POINTS_AMOUNT_CHANGED,
                [
                    new AvailablePointsAmountChangedSystemEvent(
                        $account->getId(),
                        $account->getCustomerId(),
                        $account->getAvailableAmount(),
                        $command->getPointsTransfer()->getValue(),
                        AvailablePointsAmountChangedSystemEvent::OPERATION_TYPE_ADD
                    ),
                ]
            );
        }
    }

    public function handleSpendPoints(SpendPoints $command)
    {
        /** @var Account $account */
        $account = $this->repository->load($command->getAccountId());
        $account->spendPoints($command->getPointsTransfer());
        $this->repository->save($account);
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(
                AccountSystemEvents::AVAILABLE_POINTS_AMOUNT_CHANGED,
                [
                    new AvailablePointsAmountChangedSystemEvent(
                        $account->getId(),
                        $account->getCustomerId(),
                        $account->getAvailableAmount(),
                        $command->getPointsTransfer()->getValue()
                    ),
                ]
            );
        }
    }

    public function handleCancelPointsTransfer(CancelPointsTransfer $command)
    {
        /** @var Account $account */
        $account = $this->repository->load($command->getAccountId());
        $account->cancelPointsTransfer($command->getPointsTransferId());
        $this->repository->save($account);
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(
                AccountSystemEvents::AVAILABLE_POINTS_AMOUNT_CHANGED,
                [
                    new AvailablePointsAmountChangedSystemEvent(
                        $account->getId(),
                        $account->getCustomerId(),
                        $account->getAvailableAmount()
                    ),
                ]
            );
        }
    }

    public function handleExpirePointsTransfer(ExpirePointsTransfer $command)
    {
        /** @var Account $account */
        $account = $this->repository->load($command->getAccountId());
        $account->expirePointsTransfer($command->getPointsTransferId());
        $this->repository->save($account);
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(
                AccountSystemEvents::AVAILABLE_POINTS_AMOUNT_CHANGED,
                [
                    new AvailablePointsAmountChangedSystemEvent(
                        $account->getId(),
                        $account->getCustomerId(),
                        $account->getAvailableAmount()
                    ),
                ]
            );
        }
    }
}
