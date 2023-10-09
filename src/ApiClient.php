<?php

namespace Glamstack\GoogleWorkspace;

use Exception;
use Glamstack\GoogleAuth\AuthClient;
use Glamstack\GoogleWorkspace\Models\ApiClientModel;
use Glamstack\GoogleWorkspace\Resources\Calendar\Calendar;
use Glamstack\GoogleWorkspace\Resources\Directory\Directory;
use Glamstack\GoogleWorkspace\Resources\Drive\Drive;
use Glamstack\GoogleWorkspace\Resources\Gmail\Gmail;
use Glamstack\GoogleWorkspace\Resources\LicenseManager\LicenseManager;
use Glamstack\GoogleWorkspace\Resources\Sheets\Sheets;
use Glamstack\GoogleWorkspace\Resources\Vault\Vault;
use Glamstack\GoogleWorkspace\Traits\ResponseLog;

class ApiClient
{
    use ResponseLog;

    public string $config_path;
    public array $connection_config;
    public ?string $connection_key;
    public array $log_channels;
    public array $request_headers;
    protected string $auth_token;

    /**
     * This function takes care of the initialization of authentication using
     * the `Glamstack\GoogleAuth\AuthClient` class to connect to Google OAuth2
     * servers to retrieve an API token to be used with Google API endpoints.
     *
     * @see https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/gitlab-sdk/-/blob/main/README.md
     *
     * @see https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/list#:~:text=must%20be%20provided.-,domain,-string
     *
     * @see https://support.google.com/a/answer/162106
     *
     * @param ?string $connection_key
     *      (Optional) The connection key to use from the configuration file to
     *      set the appropriate Google Auth and Google Workspace settings.
     *
     *      Default: `workspace`
     * @throws Exception
     */
    function __construct(
        ?string $connection_key = null,
        ?array  $connection_config = [],
    )
    {
        $api_client_model = new ApiClientModel();

        $this->setConfigPath();

        if (empty($connection_config)) {
            $this->setConnectionKey($connection_key);
            $this->connection_config = [];
        } else {
            $this->connection_config = $api_client_model->verifyConfigArray($connection_config);
            $this->connection_key = null;
        }

        // Set the request headers to be used by the API client
        $this->setRequestHeaders();

        $this->setLogChannels();

        if ($this->connection_key) {
            $config_file_array = $this->parseConfigFile($this->connection_key);

            $google_auth = new AuthClient($config_file_array);

            $this->logInfo('Success - Parsing the configuration file', [
                'api_scopes' => $config_file_array['api_scopes'],
                'subject_email' => $config_file_array['subject_email'],
                'json_key_file_path' => $config_file_array['json_key_file_path']
            ]);
        } else {
            $config_array = $this->parseConnectionConfigArray($this->connection_config);

            $google_auth = new AuthClient($config_array);

            $this->logInfo('Success - Parsing the connection_config array', [
                'api_scopes' => $config_array['api_scopes'],
                'subject_email' => $config_array['subject_email'],
                'json_key_file_path' => $config_array['json_key_file_path'] != null ? $config_array['json_key_file_path'] : null,
                'json_key' => $config_array['json_key'] != null ? 'Json key was utilized' : null
            ]);
        }

        // Authenticate with Google OAuth2 Server auth_token
        try {
            // Try to authenticate with Google OAuth2 Server using the Glamstack google-auth-sdk
            $this->auth_token = $google_auth->authenticate();
            $this->logInfo('Success - Authenticating with Google Auth SDK');
        } catch (Exception $exception) {
            $this->logError('Failed - Authenticating with Google Auth SDK',
                [
                    'exception_code' => $exception->getCode(),
                    'exception_message' => $exception->getMessage()
                ]
            );
            throw $exception;
        }
    }

    /**
     * Set the config path
     */
    public function setConfigPath()
    {
        $this->config_path = env('GLAMSTACK_GOOGLE_WORKSPACE_CONFIG_PATH', 'glamstack-google-workspace');
    }

    /**
     * Set the connection_key class variable. The connection_key variable by default
     * will be set to `workspace`. This can be overridden when initializing the
     * SDK with a different connection key which is passed into this function to
     * set the class variable to the provided key.
     *
     * @param ?string $connection_key (Optional) The connection key to use from the
     * configuration file.
     *
     * @return void
     */
    protected function setConnectionKey(?string $connection_key): void
    {
        if ($connection_key == null) {
            $this->connection_key = config(
                $this->config_path . '.default.connection'
            );
        } else {
            $this->connection_key = $connection_key;
        }
    }

    /**
     * Set the request headers for the Google Cloud API request
     *
     * @return void
     */
    protected function setRequestHeaders(): void
    {
        // Get Laravel and PHP Version
        $laravel = 'laravel/' . app()->version();
        $php = 'php/' . phpversion();

        // Decode the composer.lock file
        $composer_lock_json = json_decode(
            (string)file_get_contents(base_path('composer.lock')),
            true
        );

        // Use Laravel collection to search for the package. We will use the
        // array to get the package name (in case it changes with a fork) and
        // return the version key. For production, this will show a release
        // number. In development, this will show the branch name.
        /** @phpstan-ignore-next-line */
        $composer_package = collect($composer_lock_json['packages'])
            ->where('name', 'glamstack/google-workspace-sdk')
            ->first();

        /** @phpstan-ignore-next-line */
        if ($composer_package) {
            $package = $composer_package['name'] . '/' . $composer_package['version'];
        } else {
            $package = 'dev-google-workspace-sdk';
        }

        // Define request headers
        $this->request_headers = [
            'User-Agent' => $package . ' ' . $laravel . ' ' . $php
        ];
    }

    /**
     * Set the log_channels class variable
     *
     * @return void
     */
    protected function setLogChannels(): void
    {
        if ($this->connection_key) {
            $this->log_channels = config(
                $this->config_path . '.connections.' .
                $this->connection_key . '.log_channels'
            );
        } else {
            $this->log_channels = $this->connection_config['log_channels'];
        }
    }

    /**
     * Parse the configuration file to get config parameters
     *
     * @param string $connection_key
     *      The connection key provided during initialization of the SDK
     *
     * @return array
     * @throws Exception
     */
    protected function parseConfigFile(string $connection_key): array
    {
        return [
            'api_scopes' => $this->getConfigApiScopes($connection_key),
            'subject_email' => $this->getConfigSubjectEmail($connection_key),
            'json_key_file_path' => $this->getConfigJsonFilePath($connection_key),
        ];
    }

    /**
     * Get the api_scopes from the configuration file
     *
     * @param string $connection_key
     *     The connection key provided during initialization of the SDK
     *
     * @return array
     * @throws Exception
     */
    protected function getConfigApiScopes(string $connection_key): array
    {
        $api_scope_path = $this->config_path . '.connections.' . $connection_key . '.api_scopes';
        if (config($api_scope_path)) {

            $this->logInfo('Success - Getting configuration file api_scopes value', [
                'api_scopes' => config($api_scope_path)
            ]);

            return config($this->config_path . '.connections.' . $connection_key . '.api_scopes');
        } else {
            throw new Exception('No api_scopes have been set in the configuration file you are using.');
        }
    }

    /**
     * Get the subject_email from the configuration file
     *
     * Subject email is not required so if not set then return null
     *
     * @param string $connection_key
     *      The connection key provided during initialization of the SDK
     *
     * @return string|null
     */
    protected function getConfigSubjectEmail(string $connection_key): string|null
    {
        $config_path = $this->config_path . '.connections.' . $connection_key;
        if (array_key_exists('subject_email', config($config_path))) {
            if (config($config_path . '.subject_email')) {
                $this->logInfo('Success - Getting configuration file subject_email value', [
                    'subject_email' => config($config_path . '.subject_email')
                ]);
                return config($config_path . '.subject_email');
            } else {
                $this->logInfo('Success - Setting subject_email value to null');
                return null;
            }
        } else {
            $this->logInfo('Success - Setting subject_email value to null');
            return null;
        }
    }

    /**
     * Get the json_key_file from the configuration file
     *
     * This is required if using the configuration file
     *
     * @param string $connection_key
     *      The connection key provided during initialization of the SDK
     *
     * @return string|null
     * @throws Exception
     */
    protected function getConfigJsonFilePath(string $connection_key): string|null
    {
        $config_path = $this->config_path . '.connections.' . $connection_key;
        if (array_key_exists('json_key_file_path', config($config_path))) {
            if (config($config_path . '.json_key_file_path')) {

                $this->logInfo('Success - Getting configuration file json_key_file_path value', [
                    'json_key_file_path' => config($config_path . '.json_key_file_path')
                ]);

                return config($config_path . '.json_key_file_path');

            } else {
                $message = 'The configuration file does not contain a json_key_file_path';
                $this->logError('Failed - ' . $message);
                throw new Exception($message);
            }
        } else {
            $message = 'The configuration file does not contain a json_key_file_path';
            $this->logError('Failed - ' . $message);
            throw new Exception($message);
        }
    }

    /**
     * Parse the connection_config array to get the configuration parameters
     *
     * @param array $connection_config
     *      The connection config array provided during initialization of the SDK
     *
     * @return array
     */
    protected function parseConnectionConfigArray(array $connection_config): array
    {
        return [
            'api_scopes' => $this->getConfigArrayApiScopes($connection_config),
            'subject_email' => $this->getConfigArraySubjectEmail($connection_config),
            'json_key_file_path' => $this->getConfigArrayFilePath($connection_config),
            'json_key' => $this->getConfigArrayJsonKey($connection_config)
        ];
    }

    /**
     * Get the api_scopes from the connection_config array
     *
     * @param array $connection_config
     *      The connection config array provided during initialization of the SDK
     *
     * @return array
     */
    protected function getConfigArrayApiScopes(array $connection_config): array
    {
        return $connection_config['api_scopes'];
    }

    /**
     * Get the subject_email from the connection_config array
     *
     * Subject Email is not required so if not set return null
     *
     * @param array $connection_config
     *      The connection config array provided during initialization of the SDK
     *
     * @return string|null
     */
    protected function getConfigArraySubjectEmail(array $connection_config): string|null
    {
        if (array_key_exists('subject_email', $connection_config)) {
            return $connection_config['subject_email'];
        } else {
            return null;
        }
    }

    /**
     * Get the file_path from the connection_config array
     *
     * file_path is not required to be set so if not set return null
     *
     * @param array $connection_config
     *      The connection config array provided during initialization of the SDK
     *
     * @return string|null
     */
    protected function getConfigArrayFilePath(array $connection_config): string|null
    {
        if (array_key_exists('json_key_file_path', $connection_config)) {
            return $connection_config['json_key_file_path'];
        } else {
            return null;
        }
    }

    /**
     * Get the json_key from the connection_config array
     *
     * json_key i9s not required to be set so if not set return null
     *
     * @param array $connection_config
     *      The connection config array provided during initialization of the SDK
     *
     * @return mixed|null
     */
    protected function getConfigArrayJsonKey(array $connection_config): mixed
    {
        if (array_key_exists('json_key', $connection_config)) {
            return $connection_config['json_key'];
        } else {
            return null;
        }
    }

    /**
     * Get the log_channels class level variable
     *
     * @return array
     */
    protected function getLogChannels(): array
    {
        return $this->log_channels;
    }

    /**
     * @throws Exception
     */
    public function drive(): Drive
    {
        return new Drive($this);
    }

    /**
     * @throws Exception
     */
    public function directory(): Directory
    {
        return new Directory($this);
    }

    public function gmail(): Gmail
    {
        return new Gmail($this);
    }

    public function sheets(): Sheets
    {
        return new Sheets($this);
    }

    public function licenseManager(): LicenseManager
    {
        return new LicenseManager($this);
    }

    public function calendar(): Calendar
    {
        return new Calendar($this);
    }

    public function vault(): Vault
    {
        return new Vault($this);
    }
}


