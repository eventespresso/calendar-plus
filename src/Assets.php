<?php

namespace EventEspresso\CalendarPlus;

use WP_Scripts;
use WP_Styles;
use _WP_Dependency;

class Assets
{
    private const PATH         = 'src/assets';

    private const FILE_EXT_CSS = '.css';

    private const FILE_EXT_JS  = '.js';

    private const FILE_EXT_PHP = '.php';

    private string $barista_dir;

    private string $barista_url;

    private array $assets = [
        'css' => [],
        'js'  => [],
    ];

    private array $entry_points = [];

    private array $manifest = [];

    private string $version;


    /**
     * @param string $version
     */
    public function __construct(string $version)
    {
        $this->version     = $version;
        $this->barista_dir = defined('EE_BARISTA_DIR') ? EE_BARISTA_DIR : '';
        $this->barista_url = defined('EE_BARISTA_URL') ? EE_BARISTA_URL : '';
    }


    public function registerHooks(): void
    {
        if ($this->isWordPressThemesAdmin()) {
            return;
        }

        add_action('wp_default_scripts', [$this, 'registerScripts']);
        add_action('wp_default_styles', [$this, 'registerPackagesStyles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScriptsAndStyles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueuePublicScriptsAndStyles']);
    }


    private function isWordPressThemesAdmin(): bool
    {
        $request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        if (! $request_uri) {
            $request_uri = '';
        }
        $sanitized_uri = esc_url_raw($request_uri);
        return strpos($sanitized_uri, 'wp-admin/themes.php') !== false;
    }


    /**
     * Retrieves a URL to a file in the ee_barista plugin.
     *
     * @param string $path Relative path of the desired file.
     *
     * @return string       Fully qualified URL pointing to the desired file.
     */
    public function url(string $path): string
    {
        return $this->barista_url ? $this->barista_url . $path : CALENDAR_PLUS_BASE_URL . $path;
    }


    /**
     * @param string $asset_filename
     * @return string
     */
    protected function isCalendarPlusAsset(string $asset_filename): string
    {
        return strpos($asset_filename, 'calendarPlus') === 0;
    }


    /**
     * @param string $asset
     * @return string
     */
    protected function assetHandle(string $asset): string
    {
        return $this->isCalendarPlusAsset($asset) ? $asset : 'eventespresso-' . $asset;
    }


    /**
     * @return string
     */
    protected function assetsPath(): string
    {
        return $this->barista_dir ? 'build' : Assets::PATH;
    }


    /**
     * @return string
     */
    protected function assetsPathBase(): string
    {
        return $this->barista_dir ?: CALENDAR_PLUS_BASE_PATH;
    }


    /**
     * @return array
     */
    protected function getEntryPoints(): array
    {
        if (! $this->entry_points) {
            $this->entry_points = array_keys($this->getManifest('entrypoints'));
        }
        return $this->entry_points;
    }


    /**
     * @param string $key
     * @return array
     */
    protected function getManifest(string $key = 'files'): array
    {
        if (! $this->manifest) {
            $manifest_path = $this->assetsPathBase() . $this->assetsPath() . '/asset-manifest.json';

            if (! file_exists($manifest_path)) {
                wp_die('No manifest file found! Try running `yarn build` in a terminal');
            }
            $this->manifest = wp_json_file_decode($manifest_path, ['associative' => true]);
        }

        if (! isset($this->manifest[ $key ])) {
            wp_die(sprintf('No entry for %1$s found in manifest file.', esc_html($key)));
        }

        return $this->manifest[ $key ];
    }


    /**
     * Registers a script according to `wp_register_script`. Honors this request by
     * reassigning internal dependency properties of any script handle already
     * registered by that name. It does not deregister the original script, to
     * avoid losing inline scripts which may have been attached.
     *
     * @param WP_Scripts       $scripts   WP_Scripts instance.
     * @param string           $handle    Name of the script. Should be unique.
     * @param string           $src       Full URL of the script, or path of the script relative to the WordPress root
     *                                    directory.
     * @param array            $deps      Optional. An array of registered script handles this script depends on.
     *                                    Default empty array.
     * @param string|bool|null $ver       Optional. String specifying script version number, if it has one, which is
     *                                    added to the URL as a query string for cache busting purposes. If version is
     *                                    set to false, a version number is automatically added equal to current
     *                                    installed WordPress version. If set to null, no version is added.
     * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the
     *                                    <head>. Default 'false'.
     */
    protected function overrideScript(
        WP_Scripts $scripts,
        string $handle,
        string $src,
        array $deps = [],
        $ver = false,
        bool $in_footer = false
    ): void {
        $script = $scripts->query($handle);
        if ($script instanceof _WP_Dependency) {
            $script->src  = $src;
            $script->deps = $deps;
            $script->ver  = $ver;
            $script->args = $in_footer;
        } else {
            $scripts->add($handle, $src, $deps, $ver, $in_footer);
            $script = $scripts->query($handle);
        }

        if ($script instanceof _WP_Dependency) {
            /*
            * The script's `group` designation is an indication of whether it is
            * to be printed in the header or footer. The behaviour here defers to
            * the arguments as passed. Specifically, group data is not assigned
            * for a script unless it is designated to be printed in the footer.
            */
            // See: `wp_register_script` .
            unset($script->extra['group']);
            if ($in_footer) {
                $script->add_data('group', 1);
            }
            $this->assets['js'][ $handle ] = $script;
        }
    }


    /**
     * Registers a style according to `wp_register_style`. Honors this request by
     * de-registering any style by the same handler before registration.
     *
     * @param WP_Styles        $styles WP_Styles instance.
     * @param string           $handle Name of the stylesheet. Should be unique.
     * @param string           $src    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress
     *                                 root directory.
     * @param array            $deps   Optional. An array of registered stylesheet handles this stylesheet depends on.
     *                                 Default empty array.
     * @param string|bool|null $ver    Optional. String specifying stylesheet version number, if it has one, which is
     *                                 added to the URL as a query string for cache busting purposes. If version is set
     *                                 to false, a version number is automatically added equal to current installed
     *                                 WordPress version. If set to null, no version is added.
     * @param string           $media  Optional. The media for which this stylesheet has been defined.
     *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media
     *                                 queries like
     *                                 '(orientation: portrait)' and '(max-width: 640px)'.
     *
     */
    protected function overrideStyle(
        WP_Styles $styles,
        string $handle,
        string $src,
        array $deps = [],
        $ver = false,
        string $media = 'all'
    ): void {
        $style = $styles->query($handle);
        if ($style instanceof _WP_Dependency) {
            $styles->remove($handle);
        }
        $styles->add($handle, $src, $deps, $ver, $media);
        $this->assets['css'][ $handle ] = $styles->query($handle);
    }


    /**
     * Registers all the WordPress packages scripts that are in the standardized
     * `build/` location.
     *
     * @param WP_Scripts $scripts WP_Scripts instance.
     */
    public function registerScripts(WP_Scripts $scripts): void
    {
        $assets_path  = $this->assetsPathBase() . $this->assetsPath();
        $asset_files  = $this->getManifest();
        $entry_points = $this->getEntryPoints();

        foreach ($entry_points as $entry_point) {
            $handle = $this->assetHandle($entry_point);

            // Get the path from root directory as expected by `$this->url`.
            $package_path = $this->assetsPath() . $asset_files[ $entry_point . Assets::FILE_EXT_JS ];

            $dependencies = [];

            if (! empty($asset_files[ $entry_point . Assets::FILE_EXT_PHP ])) {
                $asset_file   = $asset_files[ $entry_point . Assets::FILE_EXT_PHP ];
                $asset_file   = $assets_path . $asset_file;
                $asset        = file_exists($asset_file) ? require($asset_file) : null;
                $dependencies = $asset['dependencies'] ?? $dependencies;

                // remove cyclical dependencies, if any
                if (($key = array_search($handle, $dependencies, true)) !== false) {
                    unset($dependencies[ $key ]);
                }
            }

            $this->overrideScript(
                $scripts,
                $handle,
                $this->url($package_path),
                $dependencies,
                $this->version,
                true
            );
        }
    }


    /**
     * Registers all the packages and domain styles that are in the build folder.
     *
     * @param WP_Styles $styles WP_Styles instance.
     */
    public function registerPackagesStyles(WP_Styles $styles): void
    {
        $asset_files  = $this->getManifest();
        $entry_points = $this->getEntryPoints();

        foreach ($entry_points as $entry_point) {
            $handle = $this->assetHandle($entry_point);
            if (! empty($asset_files[ $entry_point . Assets::FILE_EXT_CSS ])) {
                $css_relative_path = $this->assetsPath() . $asset_files[ $entry_point . Assets::FILE_EXT_CSS ];
                $css_absolute_path = CALENDAR_PLUS_BASE_PATH . $css_relative_path;

                if (file_exists($css_absolute_path)) {
                    $this->overrideStyle(
                        $styles,
                        $handle,
                        $this->url($css_relative_path),
                        [],
                        $this->version
                    );
                }
            }
        }
    }


    public function enqueueScript(_WP_Dependency $script)
    {
        wp_enqueue_script($script->handle, $script->src, $script->deps, $script->ver, $script->args);
    }


    public function enqueueStyle(_WP_Dependency $script)
    {
        wp_enqueue_style($script->handle, $script->src, $script->deps, $script->ver, $script->args);
    }


    public function enqueueScriptsAndStyles(array $scripts)
    {
        foreach ($scripts as $handle) {
            $script = $this->assets['js'][ $handle ] ?? null;
            if ($script instanceof _WP_Dependency) {
                $this->enqueueScript($script);
            }
            $script = $this->assets['css'][ $handle ] ?? null;
            if ($script instanceof _WP_Dependency) {
                $this->enqueueStyle($script);
            }
        }
    }


    public function enqueueAdminScriptsAndStyles()
    {
        $script = $this->assets['js']['calendarPlusAdmin'] ?? null;
        if ($script instanceof _WP_Dependency) {
            $this->enqueueScript($script);
            $this->enqueueScriptsAndStyles($script->deps);
        }
        $style = $this->assets['css']['calendarPlusAdmin'] ?? null;
        if ($style instanceof _WP_Dependency) {
            $this->enqueueStyle($style);
            $this->enqueueScriptsAndStyles($style->deps);
        }
    }


    public function enqueuePublicScriptsAndStyles()
    {
        $script = $this->assets['js']['calendarPlus'] ?? null;
        if ($script instanceof _WP_Dependency) {
            $this->enqueueScript($script);
            $this->enqueueScriptsAndStyles($script->deps);
        }
        $style = $this->assets['css']['calendarPlus'] ?? null;
        if ($style instanceof _WP_Dependency) {
            $this->enqueueStyle($style);
            $this->enqueueScriptsAndStyles($style->deps);
        }
    }
}
