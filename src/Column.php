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

use Doctrine\DBAL\Schema\Column as DBALColumn;
use Doctrine\DBAL\Types\Type as DBALType;
use FlexPHP\Database\Factories\User\SQLCreator;

class Column implements ColumnInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array<string, string>
     */
    private $options;

    /**
     * @var string
     */
    private $platform = 'MySQL';

    /**
     * @var array<string, string>
     */
    private $defaultOptions = [
        'collation' => 'utf8mb4',
    ];

    public function __construct(string $name, string $dataType, array $options = [])
    {
        $this->name = $name;
        $this->options = \array_merge($this->defaultOptions, ['type' => DBALType::getType($dataType)], $options);
    }

    public function setPlatform(string $platform): void
    {
        $this->$platform = $platform;
    }

    public function asAdd(): string
    {
        $creator = new SQLCreator($this->platform);

        return $creator->getPlatform()->getColumnDeclarationSQL($this->name, $this->options);
    }
}
