<?php
/**
 * @author      Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace ApiComponent\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception;

class FloatFilter extends AbstractFilter
{
    /**
     * @param  string $value
     * @return float
     * @throws Exception\RuntimeException If filtering $value is impossible
     */
    public function filter($value): float
    {
        $value = preg_replace('/[^0-9\.\,]/', '', (string)$value);
        $value = str_replace(',', '.', $value);

        return (float)$value;
    }
}
