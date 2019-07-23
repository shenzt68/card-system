<?php
namespace App\Library; use Illuminate\Support\Facades\Log; class CurlRequest { private static function curl($spd2457c, $spd0c59e = 0, $spe25017 = '', $spc69671 = array(), $spdecb3f = 5, &$sp5ee4b6 = false) { if (!isset($spc69671['Accept'])) { $spc69671['Accept'] = '*/*'; } if (!isset($spc69671['Referer'])) { $spc69671['Referer'] = $spd2457c; } if (!isset($spc69671['Content-Type'])) { $spc69671['Content-Type'] = 'application/x-www-form-urlencoded'; } if (!isset($spc69671['User-Agent'])) { $spc69671['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'; } if ($sp5ee4b6 !== false) { $spc69671['Cookie'] = $sp5ee4b6; } $sp3db035 = array(); foreach ($spc69671 as $sp517f03 => $sp438dc5) { $sp3db035[] = $sp517f03 . ': ' . $sp438dc5; } $sp3db035[] = 'Expect:'; $sp9f83d6 = curl_init(); curl_setopt($sp9f83d6, CURLOPT_URL, $spd2457c); curl_setopt($sp9f83d6, CURLOPT_SSL_VERIFYPEER, true); curl_setopt($sp9f83d6, CURLOPT_SSL_VERIFYHOST, 2); curl_setopt($sp9f83d6, CURLOPT_FOLLOWLOCATION, true); curl_setopt($sp9f83d6, CURLOPT_MAXREDIRS, 3); if ($spd0c59e == 1) { curl_setopt($sp9f83d6, CURLOPT_CUSTOMREQUEST, 'POST'); curl_setopt($sp9f83d6, CURLOPT_POST, 1); if ($spe25017 !== '') { curl_setopt($sp9f83d6, CURLOPT_POSTFIELDS, $spe25017); curl_setopt($sp9f83d6, CURLOPT_POSTREDIR, 3); } } if (defined('MY_PROXY')) { $spa0fa4a = MY_PROXY; $spec22d9 = CURLPROXY_HTTP; if (strpos($spa0fa4a, 'http://') || strpos($spa0fa4a, 'https://')) { $spa0fa4a = str_replace('http://', $spa0fa4a, $spa0fa4a); $spa0fa4a = str_replace('https://', $spa0fa4a, $spa0fa4a); $spec22d9 = CURLPROXY_HTTP; } elseif (strpos($spa0fa4a, 'socks4://')) { $spa0fa4a = str_replace('socks4://', $spa0fa4a, $spa0fa4a); $spec22d9 = CURLPROXY_SOCKS4; } elseif (strpos($spa0fa4a, 'socks4a://')) { $spa0fa4a = str_replace('socks4a://', $spa0fa4a, $spa0fa4a); $spec22d9 = CURLPROXY_SOCKS4A; } elseif (strpos($spa0fa4a, 'socks5://')) { $spa0fa4a = str_replace('socks5://', $spa0fa4a, $spa0fa4a); $spec22d9 = CURLPROXY_SOCKS5_HOSTNAME; } curl_setopt($sp9f83d6, CURLOPT_PROXY, $spa0fa4a); curl_setopt($sp9f83d6, CURLOPT_PROXYTYPE, $spec22d9); if (defined('MY_PROXY_PASS')) { curl_setopt($sp9f83d6, CURLOPT_PROXYUSERPWD, MY_PROXY_PASS); } } curl_setopt($sp9f83d6, CURLOPT_TIMEOUT, $spdecb3f); curl_setopt($sp9f83d6, CURLOPT_CONNECTTIMEOUT, $spdecb3f); curl_setopt($sp9f83d6, CURLOPT_RETURNTRANSFER, 1); curl_setopt($sp9f83d6, CURLOPT_HEADER, 1); curl_setopt($sp9f83d6, CURLOPT_HTTPHEADER, $sp3db035); $spdc5091 = curl_exec($sp9f83d6); $sp4c5a1f = curl_getinfo($sp9f83d6, CURLINFO_HEADER_SIZE); $sp06401d = substr($spdc5091, 0, $sp4c5a1f); $spd0789a = substr($spdc5091, $sp4c5a1f); curl_close($sp9f83d6); if ($sp5ee4b6 !== false) { $spc69671 = explode('
', $sp06401d); $spa46b28 = ''; foreach ($spc69671 as $sp06401d) { if (strpos($sp06401d, 'Set-Cookie') !== false) { if (strpos($sp06401d, ';') !== false) { $spa46b28 = $spa46b28 . trim(Helper::str_between($sp06401d, 'Set-Cookie:', ';')) . ';'; } else { $spa46b28 = $spa46b28 . trim(str_replace('Set-Cookie:', '', $sp06401d)) . ';'; } } } $sp5ee4b6 = self::combineCookie($sp5ee4b6, $spa46b28); } return $spd0789a; } public static function get($spd2457c, $spc69671 = array(), $spdecb3f = 5, &$sp5ee4b6 = false) { return self::curl($spd2457c, 0, '', $spc69671, $spdecb3f, $sp5ee4b6); } public static function post($spd2457c, $spe25017 = '', $spc69671 = array(), $spdecb3f = 5, &$sp5ee4b6 = false) { return self::curl($spd2457c, 1, $spe25017, $spc69671, $spdecb3f, $sp5ee4b6); } public static function combineCookie($sp7c77cf, $spc96365) { $sp556d33 = explode(';', $sp7c77cf); $spf9952d = explode(';', $spc96365); foreach ($sp556d33 as $spb0a0bf) { if (self::cookieIsExists($spf9952d, self::cookieGetName($spb0a0bf)) == false) { array_push($spf9952d, $spb0a0bf); } } $sp491331 = ''; foreach ($spf9952d as $spb0a0bf) { if (substr($spb0a0bf, -8, 8) != '=deleted' && strlen($spb0a0bf) > 1) { $sp491331 .= $spb0a0bf . '; '; } } return substr($sp491331, 0, strlen($sp491331) - 2); } public static function cookieGetName($spf7924d) { $sp3437bd = strpos($spf7924d, '='); return substr($spf7924d, 0, $sp3437bd); } public static function cookieGetValue($spf7924d) { $sp3437bd = strpos($spf7924d, '='); $sp8a97b1 = substr($spf7924d, $sp3437bd + 1, strlen($spf7924d) - $sp3437bd); return $sp8a97b1; } public static function cookieGet($sp5ee4b6, $sp34e4b5, $sp432313 = false) { $sp5ee4b6 = str_replace(' ', '', $sp5ee4b6); if (substr($sp5ee4b6, -1, 1) != ';') { $sp5ee4b6 = ';' . $sp5ee4b6 . ';'; } else { $sp5ee4b6 = ';' . $sp5ee4b6; } $sp70089e = Helper::str_between($sp5ee4b6, ';' . $sp34e4b5 . '=', ';'); if (!$sp432313 || $sp70089e == '') { return $sp70089e; } else { return $sp34e4b5 . '=' . $sp70089e; } } private static function cookieIsExists($spf904d7, $sp8534d0) { foreach ($spf904d7 as $spb0a0bf) { if (self::cookieGetName($spb0a0bf) == $sp8534d0) { return true; } } return false; } function test() { $sp8a97b1 = self::combineCookie('a=1;b=2;c=3', 'c=5'); var_dump($sp8a97b1); } }