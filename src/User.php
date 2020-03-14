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
     * @var UserFactory
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
        $this->factory->setName($this->name);
        $this->factory->setPassword($this->password);
        $this->factory->setHost($this->host);
    }

    public function asCreate(): string
    {
        return $this->factory->asCreate();
    }

    public function asDrop(): string
    {
        return $this->factory->asDrop();
    }
}
