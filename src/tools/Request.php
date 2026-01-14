<?php

namespace EventEspresso\CalendarPlus\tools;

/**
 * Request
 *
 * @package     Event Espresso
 * @subpackage  ${NAMESPACE}
 * @author      Brent Christensen
 * @since       1.0.4
 */
class Request
{
    private array $url;

    private string $host;

    private ?string $path = null;

    private ?array $query = null;

    private array $get_params;

    private array $post_params;


    public function __construct(string $url = '')
    {
        // if no URL is provided, use the current request URI
        $url = $url ?: $_SERVER['REQUEST_URI'];
        // sanitize -> remove slashes -> trim -> parse_url
        $this->url = parse_url(trim(wp_unslash(sanitize_text_field($url))));
        // sanitize -> remove slashes -> trim
        $this->host = trim(wp_unslash(sanitize_text_field($_SERVER['HTTP_HOST'])));
        // set get and post params
        $this->get_params  = $_GET;
        $this->post_params = $_POST;
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


    public function getParam(string $key, string $data_type = 'string', $default = null)
    {
        return isset($this->get_params[ $key ])
            ? $this->sanitizeParam($this->get_params[ $key ], $data_type)
            : $default;
    }


    public function postParam(string $key, string $data_type = 'string', $default = null)
    {
        return isset($this->post_params[ $key ])
            ? $this->sanitizeParam($this->post_params[ $key ], $data_type)
            : $default;
    }


    private function sanitizeParam($value, string $data_type)
    {
        $value = wp_unslash(trim($value));
        switch ($data_type) {
            case 'int':
            case 'integer':
                return (int) filter_var($value, FILTER_VALIDATE_INT);
            case 'float':
                return (float) filter_var($value, FILTER_VALIDATE_FLOAT);
            case 'bool':
            case 'boolean':
                return (bool) filter_var($value, FILTER_VALIDATE_BOOL);
            default:
                return sanitize_text_field((string) $value);
        }
    }
}
