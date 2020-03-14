<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database;

use FlexPHP\Database\Factories\User\MySQLUserFactory;
use FlexPHP\Database\Interfaces\UserFactoryInterface;

class User implements UserInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $host;

    /**
     * @var UserFactoryInterface
     */
    private $factory;

    public function __construct(string $name, string $password, string $host = '%')
    {
        $this->name = $name;
        $this->password = $password;
        $this->host = $host;

        $this->setFactory(new MySQLUserFactory());
    }

    public function setFactory(UserFactoryInterface $factory): void
    {
        $this->factory = $factory;
    }

    public function setGrant(string $permission, string $database = '*', string $table = '*'): void
    {
        $this->factory->setGrant($permission, $database, $table);
    }

    public function setGrants(array $permissions, string $database = '*', string $table = '*'): void
    {
        foreach ($permissions as $permission) {
            $this->setGrant($permission, $database, $table);
        }
    }

    public function asCreate(): string
    {
        $this->factory->setName($this->name);
        $this->factory->setPassword($this->password);
        $this->factory->setHost($this->host);

        return $this->factory->asCreate();
    }

    public function asDrop(): string
    {
        $this->factory->setName($this->name);
        $this->factory->setPassword($this->password);
        $this->factory->setHost($this->host);

        return $this->factory->asDrop();
    }

    public function asPrivileges(): string
    {
        $this->factory->setName($this->name);
        $this->factory->setHost($this->host);

        return $this->factory->asPrivileges();
    }
}
