<?php

namespace ApiComponent\Filter;

use Zend\Filter\AbstractFilter;

class DateSelect extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (\is_array($value)) {
            if (isset($value['day'], $value['month'], $value['year'])) {
                try {
                    return new \DateTime($value['year'] . '-' . $value['month'] . '-' . $value['day']);
                } catch (\Exception $e) {
                    return null;
                }
            }
        } else {
            $value = str_replace('\/', '-', $value);
            if (preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\d|3[0-1])$/', $value)) {
                return new \DateTime($value);
            }

            if (preg_match('/^(0[1-9]|[1-2]\d|3[0-1])-(0[1-9]|1[0-2])-\d{4}$/', $value)) {
                return new \DateTime($value);
            }
        }

        return null;
    }
}
