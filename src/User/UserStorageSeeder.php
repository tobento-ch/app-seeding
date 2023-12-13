<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Seeding\User;

use Tobento\App\Seeding\SeederInterface;
use Tobento\App\Seeding\User\UserFactory;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\AddressRepositoryInterface;
use Tobento\Service\Iterable\ItemFactoryIterator;

class UserStorageSeeder implements SeederInterface
{
    /**
     * @var int The number of users to create
     */
    protected int $numberOfUsers = 100;
    
    /**
     * Create a new UserStorageSeeder.
     *
     * @param UserRepositoryInterface $userRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected AddressRepositoryInterface $addressRepository,
    ) {}
    
    /**
     * Run the seeder.
     *
     * @return \Generator
     * @psalm-suppress UndefinedInterfaceMethod
     * @psalm-suppress UnusedVariable
     */
    public function run(): \Generator
    {
        $count = $this->userRepository->query()->count();
        
        yield from $this->userRepository
            ->query()
            ->chunk(length: 10000)
            ->insertItems(new ItemFactoryIterator(
                factory: function (): array {
                    return UserFactory::new()->definition();
                },
                create: $this->numberOfUsers,
            ));
        
        yield from $this->addressRepository
            ->query()
            ->chunk(length: 10000)
            ->insertItems(new ItemFactoryIterator(
                factory: function () use ($count): array {
                    $address = UserFactory::new()->definition()['address'];
                    $address['user_id'] = $count++;
                    return $address;
                },
                create: $this->numberOfUsers,
            ));
    }
}