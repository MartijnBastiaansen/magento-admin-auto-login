<?php

namespace Onlinepets\AutoLoginAdmin\Validator;

class Validator
{
    private $rules = [];

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param array $rules
     *
     * @return \Onlinepets\AutoLoginAdmin\Validator\Validator
     */
    public function setRules(array $rules): self
    {
        foreach ($rules as $key => $rule) {
            $this->addRule($key, $rule);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param array  $rule
     *
     * @return \Onlinepets\AutoLoginAdmin\Validator\Validator
     */
    public function addRule(string $key, array $rule): self
    {
        foreach ($rule as $row) {
            if (strpos($row, ':') !== false) {
                $rowExploded = explode(':', $row, 2);
                $this->rules[$key][$rowExploded[0]] = $rowExploded[1];
            } else {
                $this->rules[$key][$row] = 1;
            }
        }

        return $this;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    public function validate(array $values): array
    {
        $errors = [];
        $required = false;

        foreach ($this->rules as $key => $rules) {
            if (!empty($rules['required']) && !isset($values[$key])) {
                $errors[] = 'required.' . $key;
                $required = true;
            }

            if (!empty($rules['type'])) {
                if ($required || (isset($values[$key]) && !(gettype($values[$key]) === $rules['type']))) {
                    $errors[] = 'type.' . $key . '.' . $rules['type'];
                }
            }
        }

        return $errors;
    }
}
