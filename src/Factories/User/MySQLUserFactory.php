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

use FlexPHP\Database\Factories\User\AbstractUserFactory;

class MySQLUserFactory extends AbstractUserFactory
{
    public function asCreate(): string
    {
        return \sprintf("CREATE USER '%s'@'%s' IDENTIFIED BY '%s';", $this->name, $this->host, $this->password);
    }

    public function asDrop(): string
    {
        return \sprintf("DROP USER '%s'@'%s';", $this->name, $this->host);
    }
}
