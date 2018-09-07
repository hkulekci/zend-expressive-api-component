<?php
/**
 * @author      Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace ApiComponent;

use ApiComponent\Filter\DateSelect;
use ApiComponent\Filter\DateTimeSelect;
use ApiComponent\Filter\FloatFilter;
use Zend\Filter\Boolean;
use Zend\Filter\Callback;
use Zend\Filter\Digits;
use Zend\Filter\File\RenameUpload;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\Filter\ToNull;
use Zend\Validator\Callback as CallbackValidator;
use Zend\Validator\CreditCard;
use Zend\Validator\Date;
use Zend\Validator\EmailAddress;
use Zend\Validator\File\Extension;
use Zend\Validator\GreaterThan;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;

trait InputFilterHelperTrait
{
    /**
     * @param string $name
     * @param bool   $required
     * @param string $format
     * @return array
     */
    protected function datetime(string $name, bool $required = true, string $format = 'Y-m-d H:i:s'): array
    {
        return $this->date($name, $required, $format);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param string $target
     * @param array $extensions
     * @return array
     */
    protected function file(string $name, bool $required = true, string $target = '', array $extensions = []): array
    {
        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                [
                    'name' => RenameUpload::class,
                    'options' => [
                        'target'               => $target,
                        'use_upload_name'      => true,
                        'use_upload_extension' => true,
                        'overwrite'            => true,
                        'randomize'            => false,
                    ]
                ],
                ['name' => ToNull::class],
            ],
            'validators'  => [
                ['name' => Extension::class, 'options' => ['extension' => implode(',', $extensions)]],
            ],
        ];
    }

    protected function dateSelect(string $name, bool $required = true, string $format = 'Y-m-d'): array
    {
        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                ['name' => DateSelect::class],
                ['name' => ToNull::class],
            ],
            'validators'  => [
                ['name' => Date::class, 'options' => ['format' => $format]]
            ],
        ];
    }

    /**
     * @param string $name
     * @param bool   $required
     * @param string $format
     * @return array
     */
    protected function date(string $name, bool $required = true, string $format = 'Y-m-d'): array
    {
        $validators = [
            [
                'name'    => Date::class,
                'options' => [
                    'format'   => $format,
                    'messages' => array(
                        Date::INVALID      => __('Invalid type given. String, integer, array or DateTime expected'),
                        Date::INVALID_DATE => __('The input does not appear to be a valid date'),
                        Date::FALSEFORMAT  => __('The input does not fit the date format \'%format%\''),
                    ),
                ]
            ]
        ];
        $this->addNotEmptyValidator($validators, $required);

        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                ['name' => DateTimeSelect::class],
                ['name' => ToNull::class],
            ],
            'validators'  => $validators,
        ];
    }

    /**
     * @param string $name
     * @param bool   $required
     * @return array
     */
    protected function money(string $name, bool $required = true): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);

        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => array(
                ['name' => FloatFilter::class],
                ['name' => ToNull::class],
            ),
            'validators'  => $validators,
        ];
    }

    /**
     * @param string $name
     * @param bool   $required
     * @return array
     */
    protected function float(string $name, bool $required = true): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);

        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => array(
                ['name' => FloatFilter::class],
                ['name' => ToNull::class],
            ),
            'validators'  => $validators,
        ];
    }

    /**
     * @param string   $name
     * @param bool     $required
     * @param null|int $greaterThan
     * @return array
     */
    protected function integer(string $name, bool $required = true, int $greaterThan = null): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);

        if ($greaterThan !== null) {
            $validators[] = [
                'name'    => GreaterThan::class,
                'options' => [
                    'min'       => $greaterThan,
                    'inclusive' => true,
                    'messages'  => [
                        GreaterThan::NOT_GREATER           => __("The input is not greater than '%min%'"),
                        GreaterThan::NOT_GREATER_INCLUSIVE => __("The input is not greater than or equal to '%min%'"),
                    ],
                ]
            ];
        }

        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                ['name' => ToInt::class],
                ['name' => ToNull::class],
            ],
            'validators' => $validators,
        ];
    }

    /**
     * @param array $inputFilter
     * @param callable $callback
     * @return array
     */
    protected function withCallbackValidator(array $inputFilter, callable $callback): array
    {
        $this->addCallbackValidator($inputFilter['validators'], $callback);

        return $inputFilter;
    }

    /**
     * @param array $inputFilter
     * @param callable $callback
     * @return array
     */
    protected function withCallbackFilter(array $inputFilter, callable $callback): array
    {
        $this->addCallbackFilter($inputFilter['filters'], $callback);

        return $inputFilter;
    }

    /**
     * @param string $name
     * @param bool   $required
     * @return array
     */
    protected function integerArray(string $name, bool $required = true): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);

        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                [
                    'name'    => Callback::class,
                    'options' => [
                        'callback' => function ($value) {
                            if (\is_array($value)) {
                                $toIntFilter = new ToInt();
                                foreach ($value as $k => $v) {
                                    $v = $toIntFilter->filter($v);
                                    if (!$v) {
                                        unset($value[$k]);
                                    }
                                    $value[$k] = $v;
                                }

                                return array_unique($value);
                            }

                            return null;
                        },
                    ]
                ],
                ['name' => ToNull::class],
            ],
            'validators'  => $validators,
        ];
    }

    /**
     * @param string $name
     * @param bool   $required
     * @return array
     */
    protected function booleanWithNullValue(string $name, bool $required = false): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);

        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => array(
                [
                    'name'    => Boolean::class,
                    'options' => [
                        'type' => [
                            Boolean::TYPE_BOOLEAN,
                            Boolean::TYPE_INTEGER,
                            Boolean::TYPE_ZERO_STRING,
                            Boolean::TYPE_FALSE_STRING,
                        ],
                        'casting' => false,
                    ],
                ],
                [
                    'name'    => ToNull::class,
                    'options' => [
                        'type' => ToNull::TYPE_ALL - ToNull::TYPE_BOOLEAN,
                    ]
                ]
            ),
            'validators'  => $validators,
        ];
    }

    /**
     * @param string $name
     * @param bool   $required
     * @return array
     */
    protected function boolean(string $name, bool $required = false): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);

        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => array(
                [
                    'name'    => Boolean::class,
                    'options' => [
                        'type' => [
                            Boolean::TYPE_BOOLEAN,
                            Boolean::TYPE_INTEGER,
                            Boolean::TYPE_ZERO_STRING,
                            Boolean::TYPE_FALSE_STRING,
                        ],
                    ],
                ]
            ),
            'validators'  => $validators,
        ];
    }

    /**
     * @param string $name
     * @param bool   $required
     * @param array  $stringLength
     * @return array
     */
    protected function text(string $name, bool $required = true, array $stringLength = []): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);
        $this->addStringLengthValidator($validators, $stringLength);

        $filter = [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
            'validators' => $validators,
        ];

        return $filter;
    }

    /**
     * @param array  $filter
     * @param string $regex
     * @return array
     */
    protected function addRegexValidatorToString(array $filter, string $regex) : array
    {
        if (!isset($filter['validators'])) {
            $filter['validators'] = [];
        }
        $filter['validators'][] = [
            'name'    => Regex::class,
            'options' => [
                'pattern' => $regex,
            ]
        ];

        return $filter;
    }

    /**
     * @param string $name
     * @param bool   $required
     * @param array  $stringLength => Example: `$stringLength = ['min' => 1, 'max' => 3];`
     * @return array
     */
    protected function string(string $name, bool $required = true, array $stringLength = []): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);
        $this->addStringLengthValidator($validators, $stringLength);

        $filter = [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class],
                ['name' => StripNewlines::class],
                ['name' => ToNull::class],
            ],
            'validators' => $validators,
        ];

        return $filter;
    }

    /**
     * @param string $name
     * @param bool   $required
     * @param array  $stringLength => Example: `$stringLength = ['min' => 1, 'max' => 3];`
     * @return array
     */
    protected function stringWithNl2br(string $name, bool $required = true, array $stringLength = []): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);
        $this->addStringLengthValidator($validators, $stringLength);

        $filter = [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                ['name' => StripTags::class],
                ['name' => Callback::class, 'options' => ['callback' => function ($v) { return nl2br($v); }]],
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
            'validators' => $validators,
        ];

        return $filter;
    }

    /**
     * @param string $name
     * @param bool   $required
     * @return array
     */
    protected function creditCard(string $name, bool $required = true): array
    {
        $validators = [
            [
                'name'    => CreditCard::class,
                'options' => [
                    'messages' => [
                        CreditCard::CHECKSUM       => __('The input seems to contain an invalid checksum'),
                        CreditCard::CONTENT        => __('The input must contain only digits'),
                        CreditCard::INVALID        => __('Invalid type given. String expected'),
                        CreditCard::LENGTH         => __('The input contains an invalid amount of digits'),
                        CreditCard::PREFIX         => __('The input is not from an allowed institute'),
                        CreditCard::SERVICE        => __('The input seems to be an invalid credit card number'),
                        CreditCard::SERVICEFAILURE => __('An exception has been raised while validating the input'),
                    ]
                ]
            ]
        ];
        $this->addNotEmptyValidator($validators, $required);

        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                ['name' => Digits::class],
                ['name' => ToNull::class],
            ],
            'validators' => $validators,
        ];
    }

    /**
     * @param string $name
     * @param bool $required
     * @param array $stringLength
     * @return array
     */
    protected function digits(string $name, bool $required = true, array $stringLength = []): array
    {
        $validators = [];
        $this->addNotEmptyValidator($validators, $required);
        $this->addStringLengthValidator($validators, $stringLength);

        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                ['name' => Digits::class],
                ['name' => ToNull::class],
            ],
            'validators'  => $validators
        ];
    }

    /**
     * @param string $name
     * @param bool   $required
     * @return array
     */
    protected function email(string $name, bool $required): array
    {
        return [
            'name'        => $name,
            'required'    => $required,
            'allow_empty' => !$required,
            'filters'     => [
                ['name' => StripTags::class],
                ['name' => StripNewlines::class],
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
            'validators' => [
                [
                    'name'    => EmailAddress::class,
                    'options' => [
                        'messages' => [
                            EmailAddress::INVALID            => __('Invalid type given. String expected'),
                            EmailAddress::INVALID_FORMAT     => __('The input is not a valid email address. Use the basic format local-part@hostname'),
                            EmailAddress::INVALID_HOSTNAME   => __("'%hostname%' is not a valid hostname for the email address"),
                            EmailAddress::INVALID_MX_RECORD  => __("'%hostname%' does not appear to have any valid MX or A records for the email address"),
                            EmailAddress::INVALID_SEGMENT    => __("'%hostname%' is not in a routable network segment. The email address should not be resolved from public network"),
                            EmailAddress::DOT_ATOM           => __("'%localPart%' can not be matched against dot-atom format"),
                            EmailAddress::QUOTED_STRING      => __("'%localPart%' can not be matched against quoted-string format"),
                            EmailAddress::INVALID_LOCAL_PART => __("'%localPart%' is not a valid local part for the email address"),
                            EmailAddress::LENGTH_EXCEEDED    => __('The input exceeds the allowed length'),
                        ]
                    ]
                ],
            ],
        ];
    }

    private function addNotEmptyValidator(array &$validators, bool $required): void
    {
        if ($required) {
            $validators[] = [
                'name'    => NotEmpty::class,
                'options' => [
                    'messages' => [
                        NotEmpty::IS_EMPTY => __('Value is required and can\'t be empty'),
                    ],
                ],
            ];
        }
    }

    private function addStringLengthValidator(array &$validators, array $stringLength): void
    {
        if ($stringLength) {
            $options = array_merge(
                $stringLength,
                [
                    'messages' => [
                        StringLength::INVALID   => __('Invalid type given. String expected'),
                        StringLength::TOO_SHORT => __('The input is less than %min% characters long'),
                        StringLength::TOO_LONG  => __('The input is more than %max% characters long'),
                    ]
                ]
            );
            $validators[] = [
                'name'    => StringLength::class,
                'options' => $options
            ];
        }
    }

    protected function withIntegerDefaultValueFilter(array $inputFilter, int $defaultValue): array
    {
        if (isset($inputFilter['filters']) && \is_array($inputFilter['filters'])) {
            $inputFilter['filters'][] = [
                'name'    => Callback::class,
                'options' => [
                    'callback' => function ($value) use ($defaultValue) {
                        if ($value === null) {
                            return $defaultValue;
                        }

                        return $value;
                    },
                ],
            ];
        }

        return $inputFilter;
    }

    private function addCallbackValidator(array &$validators, callable $callback): void
    {
        if (\is_callable($callback)) {
            $validators[] = [
                'name'    => CallbackValidator::class,
                'options' => [
                    'callback' => $callback
                ]
            ];
        }
    }

    private function addCallbackFilter(array &$filters, callable $callback): void
    {
        if (\is_callable($callback)) {
            $filters[] = [
                'name'    => Callback::class,
                'options' => [
                    'callback' => $callback
                ]
            ];
        }
    }
}
