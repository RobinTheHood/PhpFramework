<?php
namespace RobinTheHood\PhpFramework;


class Redirect
{
    /**
     * Fuehrt einen redirect aus. Verwendet aber nicht den StatusCode 301
     * sondern 302.
     * Das heißt es handelt sich nicht um eine permanete Umleitung. Die alte
     * und die neue Url werden z.B. als zwei unterschiedliche Seiten
     * von Suchmaschiene behandelt.
     *
     * @param string $url Die URL zu der umgeleitet werden soll.
     */
    public static function status302($url, $domain = '')
    {
        $host  = $_SERVER['HTTP_HOST'];
        if ($domain) {
            $host = $domain;
        }

        $protocoll = self::getProtocoll();

        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header("Location: $protocoll://$host/$uri$url");
        exit;
    }

    public static function redirect($url, $domain = '')
    {
        self::status302($url, $domain);
        exit;
    }

    public static function status404($url)
    {
        $host  = $_SERVER['HTTP_HOST'];

        $protocoll = self::getProtocoll();

        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header("HTTP/1.0 404 Not Found");
        header("Location: $protocoll://$host$uri$url");
        exit;
    }


    /**
     * Fuehrt eine Seitenumleiteung durch. Vewendet wird der Statuscode 301.
     * Das bedeutet, dass es sich um eine "echte" Umleitung handelt.
     * Beide Seiten, die alte und die neue, erscheinen fuer den Aufrufer, wie
     * zum Beispiel google, als die selbe Seite.
     *
     * @param string $url Die url zu der umgeleitet werden soll.
     */
    public static function status301($url)
    {
        header ("HTTP/1.1 301 Moved Permanently");
        header ("Location: $url");
        exit();
    }

    public static function getProtocoll()
    {
        if (isset($_SERVER['HTTPS'])) {
            return 'https';
        } else {
            return 'http';
        }
    }
}
