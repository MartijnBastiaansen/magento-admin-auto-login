<?php

namespace Onlinepets\AutoLoginAdmin\Models;

class UrlModel
{
    protected $url;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return \Onlinepets\AutoLoginAdmin\Models\UrlModel
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
