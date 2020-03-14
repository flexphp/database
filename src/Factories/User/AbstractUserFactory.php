<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Factories\User;

use FlexPHP\Database\Interfaces\UserFactoryInterface;
use FlexPHP\Database\Validations\NameUserValidation;

abstract class AbstractUserFactory implements UserFactoryInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var array<string>
     */
    protected $permissions;

    public function setName(string $name): void
    {
        (new NameUserValidation($name))->validate();

        $this->name = $name;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    // public function setGrants(array $permissions, string $database = '*', string $table = '*'): void
    // {
    // $this->permissions = $permissions;
    // }

    abstract public function asCreate(): string;

    abstract public function asDrop(): string;
}
