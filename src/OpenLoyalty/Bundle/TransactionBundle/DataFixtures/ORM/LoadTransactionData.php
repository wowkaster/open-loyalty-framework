<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\DataFixtures\ORM;

use Broadway\CommandHandling\CommandBus;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use OpenLoyalty\Bundle\PosBundle\DataFixtures\ORM\LoadPosData;
use OpenLoyalty\Component\Transaction\Domain\Command\RegisterTransaction;
use OpenLoyalty\Component\Transaction\Domain\PosId;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;

/**
 * Class LoadTransactionData.
 */
class LoadTransactionData extends ContainerAwareFixture implements FixtureInterface, OrderedFixtureInterface
{
    const TRANSACTION_ID = '00000000-0000-1111-0000-000000000000';
    const TRANSACTION2_ID = '00000000-0000-1111-0000-000000000002';
    const TRANSACTION3_ID = '00000000-0000-1111-0000-000000000003';
    const TRANSACTION4_ID = '00000000-0000-1111-0000-000000000004';
    const TRANSACTION5_ID = '00000000-0000-1111-0000-000000000005';
    const TRANSACTION6_ID = '00000000-0000-1111-0000-000000000006';

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $phoneNumber = $faker->e164PhoneNumber;

        $transactionData = [
            'documentNumber' => '123',
            'purchasePlace' => 'wroclaw',
            'purchaseDate' => (new \DateTime('+1 day'))->getTimestamp(),
            'documentType' => 'sell',
        ];
        $items = [
            [
                'sku' => ['code' => 'SKU1'],
                'name' => 'item 1',
                'quantity' => 1,
                'grossValue' => 1,
                'category' => 'aaa',
                'maker' => 'sss',
                'labels' => [
                    [
                        'key' => 'test',
                        'value' => 'label',
                    ],
                    [
                        'key' => 'test',
                        'value' => 'label2',
                    ],
                ],
            ],
            [
                'sku' => ['code' => 'SKU2'],
                'name' => 'item 2',
                'quantity' => 2,
                'grossValue' => 2,
                'category' => 'bbb',
                'maker' => 'ccc',
            ],
        ];

        /** @var CommandBus $bus */
        $bus = $this->container->get('broadway.command_handling.command_bus');
        $customerData = [
            'name' => 'Jan Nowak',
            'email' => 'ol@oy.com',
            'nip' => 'aaa',
            'phone' => $phoneNumber,
            'loyaltyCardNumber' => '222',
            'address' => [
                'street' => 'Bagno',
                'address1' => '12',
                'city' => 'Warszawa',
                'country' => 'PL',
                'province' => 'Mazowieckie',
                'postal' => '00-800',
            ],
        ];

        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION_ID),
                $transactionData,
                $customerData,
                $items,
                new PosId(LoadPosData::POS_ID)
            )
        );

        $transactionData['documentNumber'] = '345';

        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION2_ID),
                $transactionData,
                [
                    'name' => 'Jan Nowak',
                    'email' => 'open@oloy.com',
                    'nip' => 'aaa',
                    'phone' => $phoneNumber,
                    'loyaltyCardNumber' => 'sa2222',
                    'address' => [
                        'street' => 'Bagno',
                        'address1' => '12',
                        'city' => 'Warszawa',
                        'country' => 'PL',
                        'province' => 'Mazowieckie',
                        'postal' => '00-800',
                    ],
                ],
                $items
            )
        );

        $transactionData['documentNumber'] = '888';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION5_ID),
                $transactionData,
                [
                    'name' => 'Jan Nowak',
                    'email' => 'o@lo.com',
                    'nip' => 'aaa',
                    'phone' => $phoneNumber,
                    'loyaltyCardNumber' => 'sa21as222',
                    'address' => [
                        'street' => 'Bagno',
                        'address1' => '12',
                        'city' => 'Warszawa',
                        'country' => 'PL',
                        'province' => 'Mazowieckie',
                        'postal' => '00-800',
                    ],
                ],
                $items
            )
        );

        $transactionData['documentNumber'] = '456';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION3_ID),
                $transactionData,
                [
                    'name' => 'Jan Nowak',
                    'email' => 'user@oloy.com',
                    'nip' => 'aaa',
                    'phone' => $phoneNumber,
                    'loyaltyCardNumber' => 'sa2222',
                    'address' => [
                        'street' => 'Bagno',
                        'address1' => '12',
                        'city' => 'Warszawa',
                        'country' => 'PL',
                        'province' => 'Mazowieckie',
                        'postal' => '00-800',
                    ],
                ],
                $items
            )
        );

        $transactionData['documentNumber'] = '789';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION4_ID),
                $transactionData,
                [
                    'name' => 'Jan Nowak',
                    'email' => 'user-temp@oloy.com',
                    'nip' => 'aaa',
                    'phone' => $phoneNumber,
                    'loyaltyCardNumber' => 'sa2222',
                    'address' => [
                        'street' => 'Bagno',
                        'address1' => '12',
                        'city' => 'Warszawa',
                        'country' => 'PL',
                        'province' => 'Mazowieckie',
                        'postal' => '00-800',
                    ],
                ],
                $items
            )
        );

        $transactionData['documentNumber'] = '999';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION6_ID),
                $transactionData,
                [
                    'name' => 'Jan Nowak',
                    'email' => 'o@lo.com',
                    'nip' => 'aaa',
                    'phone' => '123',
                    'loyaltyCardNumber' => 'sa21as222',
                    'address' => [
                        'street' => 'Bagno',
                        'address1' => '12',
                        'city' => 'Warszawa',
                        'country' => 'PL',
                        'province' => 'Mazowieckie',
                        'postal' => '00-800',
                    ],
                ],
                $items
            )
        );
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
