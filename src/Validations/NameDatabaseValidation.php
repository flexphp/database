<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Validations;

use FlexPHP\Database\Exception\DatabaseValidationException;
use FlexPHP\Database\Validators\NameDatabaseValidator;

final class NameDatabaseValidation implements ValidationInterface
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function validate(): void
    {
        $validator = new NameDatabaseValidator();

        $violations = $validator->validate($this->name);

        if (\count($violations)) {
            throw new DatabaseValidationException(
                \sprintf('Database name [%1$s] invalid', $this->name)
            );
        }
    }
}
