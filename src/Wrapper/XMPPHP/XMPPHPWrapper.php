<?php
namespace Wrapper\XMPPHP;

// this is not included anymore, due to license problems
require_once dirname(__FILE__) ."/lib/XMPPHP/XMPP.php";

/**
 * Wrapper class for XMPHP, to include it within King23's classloading
 */
class XMPPHPWrapper {

    /**
     * @static
     * @param $host
     * @param $port
     * @param $user
     * @param $password
     * @param $resource
     * @param null $server
     * @param bool $printlog
     * @param null $loglevel
     * @return XMPPHP_XMPP
     */
    public static function getXMPPHP($host, $port, $user, $password, $resource, $server = null, $printlog = false, $loglevel = null) {
        return new \XMPPHP_XMPP($host, $port, $user, $password, $resource, $server, $printlog, $loglevel);
    }
}