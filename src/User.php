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

use FlexPHP\Database\Validations\NameUserValidation;

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
     * @var array<string>
     */
    private $permissions;

    /**
     * @var string
     */
    private $driver;

    public function __construct(string $name, string $password, string $host = '%', string $driver = 'mysql')
    {
        $this->setName($name);
        $this->setPassword($password);
        $this->setHost($host);
        $this->setDriver($driver);
    }

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

    public function setGrants(array $permissions, string $database = '*', string $table = '*'): void
    {
        $this->permissions = $permissions;
    }

    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    public function asCreate(): string
    {
        return \sprintf("CREATE USER '%s'@'%s' IDENTIFIED BY '%s';", $this->name, $this->host, $this->password);
    }

    public function asDrop(): string
    {
        return \sprintf("DROP USER '%s'@'%s';", $this->name, $this->host);
    }
}
