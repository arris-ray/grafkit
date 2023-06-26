<?php

namespace Grafkit\Authorization;

use Grafkit\Exception\AuthorizationException;
use Grafkit\Exception\ConfigurationException;
use Grafkit\Lib\Env;
use Psr\Http\Message\RequestInterface;
use SQLite3;

class CookieAuthorizationProvider implements AuthorizationProvider
{
    public const ENV_VAR_CHROME_SAFE_STORAGE = "CHROME_SAFE_STORAGE";
    public const AUTH_COOKIE_NAME = 'oidc_session';
    public const CHROME_PASSPHRASE_SALT = 'saltysalt';
    public const CHROME_ENCRYPTED_COOKIE_PREFIX = 'v10';
    public const COOKIE_JAR = Env::RESOURCE_COOKIES;
    public const NUM_ITERATIONS_MAC = 1003;
    public const NUM_ITERATIONS_LINUX = 1;
    public const KEY_LENGTH = 16;

    /**
     * @var bool
     */
    private bool $isMac = true;

    /**
     * @var string
     */
    private string $prefix;

    /**
     * @var string
     */
    private string $passphrase;

    /**
     * @var SQLite3
     */
    private SQLite3 $sqlite;

    /**
     * Constructor
     */
    public function __construct()
    {
        $password = mb_convert_encoding($this->getChromeSafeStoragePassword(), 'UTF-8');
        $iterations = $this->isMac ? self::NUM_ITERATIONS_MAC : self::NUM_ITERATIONS_LINUX;
        $this->passphrase = openssl_pbkdf2($password, self::CHROME_PASSPHRASE_SALT, self::KEY_LENGTH, $iterations);
        $this->prefix = mb_convert_encoding(self::CHROME_ENCRYPTED_COOKIE_PREFIX, 'UTF-8');
        $this->sqlite = new SQLite3(self::COOKIE_JAR);
    }

    /**
     * @inheritDoc
     */
    public function authorize(RequestInterface $request): RequestInterface
    {
        if (!$request->hasHeader('Host')) {
            throw new AuthorizationException("Missing required Host header.");
        }

        $hostname = $request->getHeader('Host')[0] ?? null;
        if ($hostname === null) {
            throw new AuthorizationException("Host header does not contain a valid hostname.");
        }

        $name = self::AUTH_COOKIE_NAME;
        $result = $this->sqlite->query("select * from cookies where host_key = '$hostname' and name = '$name'");
        if ($result === false ){
            throw new AuthorizationException("Failed to query a cookie named {$name} on the domain {$hostname}.");
        }

        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row === false ){
            throw new AuthorizationException("Failed to fetch a cookie named {$name} on the domain {$hostname}.");
        }

        $name = $row['name'];
        $value = $this->decryptAuthCookie($row['encrypted_value']);
        return $request->withHeader('Cookie', "{$name}={$value}");
    }

    /**
     * @return string
     * @throws ConfigurationException
     */
    private function getChromeSafeStoragePassword(): string
    {
        $password = $_ENV[self::ENV_VAR_CHROME_SAFE_STORAGE] ?? null;
        if ($password === null) {
            throw new ConfigurationException("Chrome Safe Storage password is missing from environment variables.");
        }
        return $password;
    }

    /**
     * @param string $encryptedCookie
     * @return string
     */
    private function decryptAuthCookie(string $encryptedCookie): string
    {
        $encryptedValuePadding = intval(mb_substr($encryptedCookie, -1));
        $iv = str_pad('', 16, ' ', STR_PAD_LEFT);
        $encryptedCookie = mb_substr($encryptedCookie, mb_strlen(self::CHROME_ENCRYPTED_COOKIE_PREFIX));
        if ($encryptedValuePadding > 0) {
            $encryptedCookie = mb_substr($encryptedCookie, 0, $encryptedValuePadding * -1);
        }
        return openssl_decrypt($encryptedCookie, 'aes-128-cbc', $this->passphrase, OPENSSL_RAW_DATA, $iv);
    }
}