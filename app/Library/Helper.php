<?php
namespace App\Library; use Hashids\Hashids; class Helper { public static function getMysqlDate($spb6cc34 = 0) { return date('Y-m-d', time() + $spb6cc34 * 24 * 3600); } public static function getIP() { if (isset($_SERVER)) { if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $sped2854 = $_SERVER['HTTP_X_FORWARDED_FOR']; } else { if (isset($_SERVER['HTTP_CLIENT_IP'])) { $sped2854 = $_SERVER['HTTP_CLIENT_IP']; } else { $sped2854 = @$_SERVER['REMOTE_ADDR']; } } } else { if (getenv('HTTP_X_FORWARDED_FOR')) { $sped2854 = getenv('HTTP_X_FORWARDED_FOR'); } else { if (getenv('HTTP_CLIENT_IP')) { $sped2854 = getenv('HTTP_CLIENT_IP'); } else { $sped2854 = getenv('REMOTE_ADDR'); } } } if (strpos($sped2854, ',') !== FALSE) { $sp133ccc = explode(',', $sped2854); return $sp133ccc[0]; } return $sped2854; } public static function getClientIP() { if (isset($_SERVER)) { $sped2854 = $_SERVER['REMOTE_ADDR']; } else { $sped2854 = getenv('REMOTE_ADDR'); } if (strpos($sped2854, ',') !== FALSE) { $sp133ccc = explode(',', $sped2854); return $sp133ccc[0]; } return $sped2854; } public static function filterWords($sp14800d, $spe817ab) { if (!$sp14800d) { return false; } if (!is_array($spe817ab)) { $spe817ab = explode('|', $spe817ab); } foreach ($spe817ab as $sp6b9ea7) { if ($sp6b9ea7 && strpos($sp14800d, $sp6b9ea7) !== FALSE) { return $sp6b9ea7; } } return false; } public static function is_idcard($sp71fb38) { if (strlen($sp71fb38) == 18) { return self::idcard_checksum18($sp71fb38); } elseif (strlen($sp71fb38) == 15) { $sp71fb38 = self::idcard_15to18($sp71fb38); return self::idcard_checksum18($sp71fb38); } else { return false; } } private static function idcard_verify_number($spad94b0) { if (strlen($spad94b0) != 17) { return false; } $spba8e69 = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); $sp592d04 = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); $spca74c3 = 0; for ($sp6b283c = 0; $sp6b283c < strlen($spad94b0); $sp6b283c++) { $spca74c3 += substr($spad94b0, $sp6b283c, 1) * $spba8e69[$sp6b283c]; } $spd8b895 = $spca74c3 % 11; $spabadfa = $sp592d04[$spd8b895]; return $spabadfa; } private static function idcard_15to18($sp6d4e81) { if (strlen($sp6d4e81) != 15) { return false; } else { if (array_search(substr($sp6d4e81, 12, 3), array('996', '997', '998', '999')) !== false) { $sp6d4e81 = substr($sp6d4e81, 0, 6) . '18' . substr($sp6d4e81, 6, 9); } else { $sp6d4e81 = substr($sp6d4e81, 0, 6) . '19' . substr($sp6d4e81, 6, 9); } } $sp6d4e81 = $sp6d4e81 . self::idcard_verify_number($sp6d4e81); return $sp6d4e81; } private static function idcard_checksum18($sp6d4e81) { if (strlen($sp6d4e81) != 18) { return false; } $spad94b0 = substr($sp6d4e81, 0, 17); if (self::idcard_verify_number($spad94b0) != strtoupper(substr($sp6d4e81, 17, 1))) { return false; } else { return true; } } public static function str_between($sp14800d, $sp974326, $sp0480e1) { $spe3a4eb = strpos($sp14800d, $sp974326); if ($spe3a4eb === false) { return ''; } $sp368574 = strpos($sp14800d, $sp0480e1, $spe3a4eb + strlen($sp974326)); if ($sp368574 === false || $spe3a4eb >= $sp368574) { return ''; } $spc24183 = strlen($sp974326); $sp9b52fe = substr($sp14800d, $spe3a4eb + $spc24183, $sp368574 - $spe3a4eb - $spc24183); return $sp9b52fe; } public static function str_between_longest($sp14800d, $sp974326, $sp0480e1) { $spe3a4eb = strpos($sp14800d, $sp974326); if ($spe3a4eb === false) { return ''; } $sp368574 = strrpos($sp14800d, $sp0480e1, $spe3a4eb + strlen($sp974326)); if ($sp368574 === false || $spe3a4eb >= $sp368574) { return ''; } $spc24183 = strlen($sp974326); $sp9b52fe = substr($sp14800d, $spe3a4eb + $spc24183, $sp368574 - $spe3a4eb - $spc24183); return $sp9b52fe; } public static function format_url($spd2457c) { if (!strlen($spd2457c)) { return $spd2457c; } if (!starts_with($spd2457c, 'http://') && !starts_with($spd2457c, 'https://')) { $spd2457c = 'http://' . $spd2457c; } while (ends_with($spd2457c, '/')) { $spd2457c = substr($spd2457c, 0, -1); } return $spd2457c; } public static function lite_hash($sp14800d) { $spa1389c = crc32((string) $sp14800d); if ($spa1389c < 0) { $spa1389c &= 1 << 7; } return $spa1389c; } const ID_TYPE_USER = 0; const ID_TYPE_CATEGORY = 1; const ID_TYPE_PRODUCT = 2; public static function id_encode($sp3c46ab, $sp4f56c1) { $sp96a32f = new Hashids(config('app.key'), 8, 'abcdefghijklmnopqrstuvwxyz1234567890'); return @$sp96a32f->encode(self::lite_hash($sp3c46ab), $sp3c46ab, self::lite_hash($sp4f56c1), $sp4f56c1); } public static function id_decode($sp8aa1e1, $sp4f56c1) { if (strlen($sp8aa1e1) < 8) { $sp96a32f = new Hashids(config('app.key')); if ($sp4f56c1 === self::ID_TYPE_USER) { return intval(@$sp96a32f->decodeHex($sp8aa1e1)); } else { return intval(@$sp96a32f->decode($sp8aa1e1)[0]); } } $sp96a32f = new Hashids(config('app.key'), 8, 'abcdefghijklmnopqrstuvwxyz1234567890'); return intval(@$sp96a32f->decode($sp8aa1e1)[1]); } public static function is_mobile() { if (isset($_SERVER['HTTP_USER_AGENT'])) { if (preg_match('/(iPhone|iPod|Android|ios|SymbianOS|Windows Phone)/i', $_SERVER['HTTP_USER_AGENT'])) { return true; } } return false; } public static function b1_rand_background() { if (self::is_mobile()) { $sp96d357 = array('//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpgyq8n5j20u01hcne2.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpfyjbd0j20u01hcte2.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpw3b5mkj20u01hcnfh.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxoybkicbj20u01hc7de.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpes8rmmj20u01hctn7.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxp8ond6gj20u01hctji.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxp4ljhhvj20u01hck0r.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpstrwnsj20u01hc7he.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxq2a1vthj20u01hc4gs.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpiebjztj20u01hcaom.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxow4b14kj20u01hc43x.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxohtyvgfj20u01hc7gk.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxp6vexa3j20u01hcdj3.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxqa0zhc6j20u01hc14e.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxomnbr0gj20u01hc79r.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpx57f0sj20u01hcqmd.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxoozjilyj20u01hcgt9.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxprigfw1j20u01hcam9.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxod70fcpj20u01hcajj.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpzb5p1tj20u01hcnca.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxozvry57j20u01hcgwo.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpv092lfj20u01hcx1o.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpdz6s0bj20u01hcaqj.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxoso79ayj20u01hcq9c.jpg', '//ww2.sinaimg.cn/large/ac1a0c4agy1ftxpqjrtjhj20u01hcapi.jpg'); } else { $sp96d357 = array('//ww1.sinaimg.cn/large/ac1a0c4agy1ftz78cfrj2j21hc0u0kio.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz7qj6l3xj21hc0u0b29.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ft9tqa2fvpj21hc0u017a.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz71m76skj21hc0u0nnq.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz709py6fj21hc0u0wx2.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ft9sgqv33lj21hc0u04qp.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ft9s9soh4sj21hc0u01kx.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ft9s9r2vkzj21hc0u0x4e.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz7etbcs8j21hc0u07p3.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ft9sgn1bluj21hc0u0kiy.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz7r6tmv1j21hc0u0anj.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz7c4h0xzj21hc0u01kx.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ft9tq7uypvj21hc0u01be.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1fwr4pjgbncj21hc0u0kjl.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz7i6u1gxj21hc0u0tyk.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1fwr4s0fb2tj21hc0u01ky.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz72wkr9dj21hc0u0h1r.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz7tj5ohrj21hc0u0qnp.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ft9sgp23zbj21hc0u0txl.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz7l9dcokj21hc0u0k9k.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1fwr4lvumu1j21hc0u0x6p.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz7alxyhnj21hc0u0nkh.jpg', '//ww1.sinaimg.cn/large/ac1a0c4agy1ftz799gvb3j21hc0u0qdt.jpg'); } return $sp96d357[rand(0, count($sp96d357))]; } }