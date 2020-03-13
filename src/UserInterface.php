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

use FlexPHP\Database\Factories\UserFactory;

interface UserInterface
{
    public function __construct(string $name, string $password, string $host);
    
    public function setFactory(UserFactory $factory);
}
