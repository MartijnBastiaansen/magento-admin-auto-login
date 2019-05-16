<?php

namespace Onlinepets\AutoLoginAdmin\Http\Requests;

class CreateRequest extends Request
{
    protected $container = 'request';

    protected $fillable = [
        'user_name',
    ];

    protected $rules = [
        'user_name' => ['required', 'type:string'],
    ];

    protected $userName;

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     *
     * @return \Onlinepets\AutoLoginAdmin\Http\Requests\CreateRequest
     */
    public function setUserName(string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }
}
