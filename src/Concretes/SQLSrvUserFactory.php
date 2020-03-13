<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Concretes;

use FlexPHP\Database\Factories\UserFactory;

class SQLSrvUserFactory extends UserFactory
{
    public function asCreate(): string
    {
        return \sprintf(<<<T
CREATE LOGIN %1\$s WITH PASSWORD = '%2\$s';
GO
CREATE USER %1\$s FOR LOGIN %1\$s;
GO
T, $this->name, $this->password);
    }

    public function asDrop(): string
    {
        return \sprintf(<<<T
DROP USER %s;
GO
T, $this->name);
    }
}
