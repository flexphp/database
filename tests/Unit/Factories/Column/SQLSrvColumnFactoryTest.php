<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Tests\Unit\Factories\Column;

use FlexPHP\Database\Column;
use FlexPHP\Database\Tests\TestCase;

class SQLSrvColumnFactoryTest extends TestCase
{
    /**
     * @dataProvider getDataType
     */
    public function testItColumnAsAddDefault(string $dataType): void
    {
        $name = 'foo';
        $type = $this->getType($dataType);

        $column = new Column($name, $dataType);
        $column->setPlatform('SQLServer');
        $this->assertEquals(<<<T
$name $type DEFAULT NULL COLLATE utf8mb4
T, $column->asAdd(), $dataType);
    }

    public function getType($dataType): string
    {
        switch ($dataType) {
            case 'integer':
                $type = 'INT';
                break;
            case 'smallint':
                $type = 'SMALLINT';
                break;
            case 'decimal':
                $type = 'NUMERIC(10, 0)';
                break;
            case 'binary':
                $type = 'VARBINARY(255)';
                break;
            case 'guid':
                $type = 'CHAR(36)';
                break;
            case 'blob':
                $type = 'LONGBLOB';
                break;
            case 'boolean':
                $type = 'TINYINT(1)';
                break;
            case 'datetime':
            case 'datetime_immutable':
            case 'datetimetz':
            case 'datetimetz_immutable':
                $type = 'DATETIME';
                break;
            case 'date':
            case 'date_immutable':
                $type = 'DATE';
                break;
            case 'float':
                $type = 'DOUBLE PRECISION';
                break;
            case 'bigint':
                $type = 'BIGINT';
                break;
            case 'time':
            case 'time_immutable':
                $type = 'TIME';
                break;
            case 'json':
                $type = 'JSON';
                break;
            case 'array':
            case 'simple_array':
            case 'object':
            case 'text':
                $type = 'LONGTEXT';
                break;
            default:
                $type = 'VARCHAR(255)';
                break;
        }

        return $type;
    }
}
