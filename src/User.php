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

use FlexPHP\Database\Interfaces\UserFactoryInterface;

final class User implements UserInterface
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
     * @var array<int, array>
     */
    private $grants;

    /**
     * @var string
     */
    private $platform = 'MySQL';

    public function __construct(string $name, string $password, string $host = '%')
    {
        $this->name = $name;
        $this->password = $password;
        $this->host = $host;
    }

    public function setPlatform(string $platform): void
    {
        $this->platform = $platform;
    }

    public function setGrant(string $permission, string $database = '*', string $table = '*'): void
    {
        $this->grants[] = [$permission, $database, $table];
    }

    public function setGrants(array $permissions, string $database = '*', string $table = '*'): void
    {
        foreach ($permissions as $permission) {
            $this->setGrant($permission, $database, $table);
        }
    }

    public function toSqlCreate(): string
    {
        $factory = $this->getFactory();
        $factory->setName($this->name);
        $factory->setPassword($this->password);
        $factory->setHost($this->host);

        return $factory->asCreate();
    }

    public function toSqlDrop(): string
    {
        $factory = $this->getFactory();
        $factory->setName($this->name);
        $factory->setHost($this->host);

        return $factory->asDrop();
    }

    public function toSqlPrivileges(): string
    {
        $factory = $this->getFactory();
        $factory->setName($this->name);
        $factory->setHost($this->host);

        foreach ($this->grants as $grant) {
            $factory->setGrant(...$grant);
        }

        return $factory->asPrivileges();
    }

    private function getFactory(): UserFactoryInterface
    {
        $fqdn = \sprintf('FlexPHP\Database\Factories\User\%sUserFactory', $this->platform);

        return new $fqdn();
    }
}
