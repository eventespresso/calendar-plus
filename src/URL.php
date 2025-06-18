<?php

namespace EventEspresso\CalendarPlus;

/**
 * URL
 *
 * @package     Event Espresso
 * @subpackage  ${NAMESPACE}
 * @author      Brent Christensen
 * @since       1.0.4
 */
class URL
{
    private array $url;

    private string $host;

    private ?string $path = null;

    private ?array $query = null;


    public function __construct(string $url = '')
    {
        // if no URL is provided, use the current request URI
        $url        = $url ?: $_SERVER['REQUEST_URI'];
        // sanitize -> remove slashes -> trim -> parse_url
        $this->url  = parse_url(trim(wp_unslash(sanitize_text_field($url))));
        // sanitize -> remove slashes -> trim
        $this->host = trim(wp_unslash(sanitize_text_field($_SERVER['HTTP_HOST'])));
    }


    private function component($key)
    {
        return $this->url[ $key ] ?? '';
    }


    public function asArray(): array
    {
        return $this->url;
    }


    public function host(): string
    {
        return $this->host;
    }


    public function path(): string
    {
        if ($this->path === null) {
            $this->path = trim(sanitize_text_field($this->component('path')), '/');
        }
        return $this->path;
    }


    public function query(): array
    {
        if ($this->query === null) {
            $this->query = [];
            $params      = explode('&', $this->component('query'));
            if (empty($params) || (count($params) === 1 && empty($params[0]))) {
                return $this->query;
            }
            foreach ($params as $param) {
                $key_value = explode('=', $param);
                if (count($key_value) === 2) {
                    [$key, $value] = $key_value;
                } else {
                    $key   = $key_value[0];
                    $value = null;
                }
                $this->query[ sanitize_text_field($key) ] = sanitize_text_field($value);
            }
        }
        return $this->query;
    }


    public function queryParam(string $key)
    {
        $query = $this->query();
        return $query[ $key ] ?? null;
    }
}
