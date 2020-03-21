<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Validators;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

/**
 * @Annotation
 */
class NameUserValidator
{
    /**
     * @var int
     */
    private $minLength = 1;

    /**
     * @var int
     */
    private $maxLength = 32;

    public function validate(string $name): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        return $validator->validate($name, [
            new Length([
                'min' => $this->minLength,
                'max' => $this->maxLength,
                'allowEmptyString' => false,
            ]),
            new Regex([
                'pattern' => '/^[a-zA-Z][a-zA-Z0-9_\-\.]*$/',
            ]),
        ]);
    }
}
