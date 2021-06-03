<?php


namespace Parad0xe\Bundle\FilterBundle\Configuration\Options;


class StoreOptions
{
    /**
     * @var string
     */
    private $sessionkey;

    /**
     * @var string
     */
    private $requestkey;

    /**
     * @var string
     */
    private $cleanerkey;

    /**
     * @var bool
     */
    private $cached;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $auto_clean_timeout;

    public function __construct($options)
    {
        $this->sessionkey = $options->session_key;
        $this->requestkey = $options->request_key;
        $this->cleanerkey = $options->cleaner_key;
        $this->cached = $options->cached;
        $this->method = strtolower($options->method);
        $this->auto_clean_timeout = strtolower($options->auto_clean_timeout);
    }

    /**
     * @return string
     */
    public function getSessionkey(): string
    {
        return $this->sessionkey;
    }

    /**
     * @return string
     */
    public function getRequestkey(): string
    {
        return $this->requestkey;
    }

    /**
     * @return string
     */
    public function getCleanerkey(): string
    {
        return $this->cleanerkey;
    }

    /**
     * @return bool
     */
    public function withCache(): bool
    {
        return $this->cached;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return int
     */
    public function getAutoCleanTimeout(): int
    {
        return $this->auto_clean_timeout;
    }

    public function isMethod($method): bool {
        return $this->method === strtolower($method);
    }

    /**
     * @return bool
     */
    public function isGETMethod(): bool {
        return $this->isMethod("get");
    }

    /**
     * @return bool
     */
    public function isPOSTMethod(): bool {
        return $this->isMethod("post");
    }
}
