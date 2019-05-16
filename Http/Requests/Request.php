<?php

namespace Onlinepets\AutoLoginAdmin\Http\Requests;

use Magento\Framework\Webapi\Exception;
use Onlinepets\BackendUserApi\Validator\Validator;

abstract class Request
{
    protected $container = '';
    protected $fillable = [];
    protected $rules = [];
    protected $messages = [];

    private $defaultMessages = [
        'required' => 'The :argument argument is required',
        'type' => 'The type of :argument must be :argument',
    ];

    /**
     * Request constructor.
     *
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function __construct()
    {
        if (empty($this->getContainer())) {
            throw new Exception(__('No container set in code'), '500', Exception::HTTP_INTERNAL_ERROR);
        }

        if (count($this->getFillable()) < 1) {
            throw new Exception(__('Request has no fillable values'), '500', Exception::HTTP_BAD_REQUEST);
        }

        /** @var array $request */
        $request = json_decode(file_get_contents("php://input"), true);

        if (!isset($request[$this->getContainer()])) {
            throw new Exception(
                __('Container is wrong in code'),
                '500',
                Exception::HTTP_BAD_REQUEST,
                ['errors' => 'Container is: ' . $this->getContainer()]
            );
        }

        $assignError = false;
        $assignErrors = [];
        foreach ($request[$this->getContainer()] as $key => $value) {
            if (!in_array($key, $this->fillable)) {
                $assignError = true;
                $assignErrors[] = $key . ' is not fillable';
            }
        }

        if ($assignError) {
            throw new Exception(
                __('Values are not fillable'),
                '400',
                Exception::HTTP_BAD_REQUEST,
                ['errors' => $assignErrors]
            );
        }

        if (count($this->getRules()) > 0) {
            $validator = new Validator();
            $validator->setRules($this->rules);

            $errors = $validator->validate($request[$this->getContainer()]);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $exploded = explode('.', $error);
                    $key = $exploded[0] . '.' . $exploded[1];

                    if (array_key_exists($key, $this->messages)) {
                        $errorMessages[] = $this->messages[$key];
                    } else {
                        $errorMessage = $this->defaultMessages[$exploded[0]];
                        unset($exploded[0]);

                        foreach ($exploded as $argument) {
                            $errorMessage = preg_replace('/:argument/m', $argument, $errorMessage, 1);
                        }

                        $errorMessages[] = $errorMessage;
                    }
                }

                throw new Exception(
                    __("Values aren't correct filled"),
                    '400',
                    Exception::HTTP_BAD_REQUEST,
                    ['errors' => $errorMessages]
                );
            }
        }
    }

    /**
     * @return array
     */
    protected function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * @param $rules
     *
     * @return \Onlinepets\BackendUserApi\Http\Requests\Request
     */
    protected function setRules($rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return array
     */
    protected function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @return string
     */
    protected function getContainer(): string
    {
        return $this->container;
    }
}
