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

use Tobento\App\Seeding\AbstractFactory;
use Tobento\Service\Seeder\SeedInterface;
use Tobento\Service\Seeder\Str;
use Tobento\Service\Seeder\Arr;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\UserFactoryInterface;
use Tobento\App\User\PasswordHasherInterface;

class UserFactory extends AbstractFactory
{
    protected null|string $password = null;
    
    public function withEmail(string $email): static
    {
        return $this->modify(fn() => ['email' => $email]);
    }
    
    public function withSmartphone(string $phone): static
    {
        return $this->modify(fn() => ['smartphone' => $phone]);
    }
    
    public function withUsername(string $username): static
    {
        return $this->modify(fn() => ['username' => $username]);
    }
    
    public function withPassword(string $plainPassword): static
    {
        // hash once as time-consuming!
        if (is_null($this->password)) {
            $this->password = $this->getService(PasswordHasherInterface::class)->hash(plainPassword: $plainPassword);
        }
        
        return $this->modify(fn() => ['password' => $this->password]);
    }
    
    public function withRoleKey(string $roleKey): static
    {
        return $this->modify(fn() => ['role_key' => $roleKey]);
    }
    
    /**
     * @psalm-suppress UnusedClosureParam
     */    
    public function withAddress(array $address): static
    {
        return $this->modify(
            fn(SeedInterface $seed, array $definition)
            => ['address' => array_merge($definition['address'] ?? [], $address)]
        );
    }

    /**
     * Returns the definition.
     *
     * @return array
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function definition(): array
    {
        $firstname = $this->seed->firstname();
        $lastname = $this->seed->lastname();
        $locale = Arr::item(['en', 'de', 'fr']);
        
        return [
            'password' => '$2y$10$/yEk5a93TMNtRSz8MnlS4eA15b.olnmOv7l2Ll87O2.XMQmjlsL4a',
            'email' => $this->seed->email(from: $firstname.' '.$lastname),
            'locale' => $locale,
            'birthday' => $this->seed->dateTime(from: '-30 years', to:  '-10 years')->format('Y-m-d'),
            // primary address:
            'address' => [
                //'key' => 'primary',
                'salutation' => 'mr',
                'firstname' => $firstname,
                'lastname' => $lastname,
                'city' => $this->seed->city(),
                'address1' => $this->seed->street(),
                'postcode' => $this->seed->postcode(),
                'locale' => $locale,
                'country_key' => Arr::item(['US', 'CH', 'DE', 'FR']),
            ],
        ];
    }
    
    /**
     * Create an entity from definition.
     *
     * @param array $definition
     * @return object
     */
    protected function createEntity(array $definition): object
    {
        return $this->getService(UserFactoryInterface::class)->createEntityFromArray($definition);
    }
    
    /**
     * Store an entity.
     *
     * @param array $definition
     * @return object
     */
    protected function storeEntity(array $definition): object
    {
        return $this->getService(UserRepositoryInterface::class)->createWithAddress(
            user: $definition,
            address: $definition['address'] ?? [],
        );
    }
}