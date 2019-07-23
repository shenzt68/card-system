<?php
namespace App\Library\Pay\Youzan; use App\Library\Pay\ApiInterface; class Api implements ApiInterface { private $url_notify = ''; private $url_return = ''; public function __construct($sp3c46ab) { $this->url_notify = SYS_URL_API . '/pay/notify/' . $sp3c46ab; $this->url_return = SYS_URL . '/pay/return/' . $sp3c46ab; } private function getAccessToken($sp9d4382) { $sp1ffb50 = $sp9d4382['client_id']; $spaaecc8 = $sp9d4382['client_secret']; $sp9cc8bf = array('kdt_id' => $sp9d4382['kdt_id']); $spb72f32 = (new Open\Token($sp1ffb50, $spaaecc8))->getToken('self', $sp9cc8bf); if (!isset($spb72f32['access_token'])) { \Log::error('Pay.Youzan.goPay.getToken Error: ' . json_encode($spb72f32)); throw new \Exception('平台支付Token获取失败'); } return $spb72f32['access_token']; } function goPay($sp9d4382, $sp2e47fc, $spd4e90d, $spd0789a, $sp076ec7) { $spf9ad85 = strtolower($sp9d4382['payway']); try { $sp31a710 = $this->getAccessToken($sp9d4382); $sp7854df = new Open\Client($sp31a710); } catch (\Exception $sp3f4aab) { \Log::error('Pay.Youzan.goPay getAccessToken error', array('exception' => $sp3f4aab)); throw new \Exception('支付渠道响应超时，请刷新重试'); } $spc0e525 = array('qr_type' => 'QR_TYPE_DYNAMIC', 'qr_price' => $sp076ec7, 'qr_name' => $spd4e90d, 'qr_source' => $sp2e47fc); $spb72f32 = $sp7854df->get('youzan.pay.qrcode.create', '3.0.0', $spc0e525); $spb72f32 = isset($spb72f32['response']) ? $spb72f32['response'] : $spb72f32; if (!isset($spb72f32['qr_url'])) { \Log::error('Pay.Youzan.goPay.getQrcode Error: ' . json_encode($spb72f32)); throw new \Exception('平台支付二维码获取失败'); } \App\Order::whereOrderNo($sp2e47fc)->update(array('pay_trade_no' => $spb72f32['qr_id'])); header('location: /qrcode/pay/' . $sp2e47fc . '/youzan_' . strtolower($spf9ad85) . '?url=' . urlencode($spb72f32['qr_url'])); die; } function verify($sp9d4382, $sp9a4d97) { $sp7b2182 = isset($sp9d4382['isNotify']) && $sp9d4382['isNotify']; $sp1ffb50 = $sp9d4382['client_id']; $spaaecc8 = $sp9d4382['client_secret']; if ($sp7b2182) { $spd7d1b1 = file_get_contents('php://input'); $sp6fd648 = json_decode($spd7d1b1, true); if (@$sp6fd648['test']) { echo 'test success'; return false; } try { $sp417133 = $sp6fd648['msg']; } catch (\Exception $sp3f4aab) { \Log::error('Pay.Youzan.verify get input error#1', array('exception' => $sp3f4aab, 'post_raw' => $spd7d1b1)); echo 'fatal error'; return false; } $sp75b070 = $sp1ffb50 . '' . $sp417133 . '' . $spaaecc8; $sp964415 = md5($sp75b070); if ($sp964415 != $sp6fd648['sign']) { \Log::error('Pay.Youzan.verify, sign error $sign_string:' . $sp75b070 . ', $sign' . $sp964415); echo 'fatal error'; return false; } else { echo json_encode(array('code' => 0, 'msg' => 'success')); } $sp417133 = json_decode(urldecode($sp417133), true); if ($sp6fd648['type'] === 'TRADE_ORDER_STATE' && $sp417133['status'] === 'TRADE_SUCCESS') { try { $sp31a710 = $this->getAccessToken($sp9d4382); $sp7854df = new Open\Client($sp31a710); } catch (\Exception $sp3f4aab) { \Log::error('Pay.Youzan.verify getAccessToken error#1', array('exception' => $sp3f4aab)); echo 'fatal error'; return false; } $spc0e525 = array('tid' => $sp417133['tid']); $spb72f32 = $sp7854df->get('youzan.trade.get', '3.0.0', $spc0e525); if (isset($spb72f32['error_response'])) { \Log::error('Pay.Youzan.verify with error：' . $spb72f32['error_response']['msg']); echo 'fatal error'; return false; } $spe4caa0 = $spb72f32['response']['trade']; $spe0613f = \App\Order::where('pay_trade_no', $spe4caa0['qr_id'])->first(); if ($spe0613f) { $spca4fc7 = $sp417133['tid']; $sp9a4d97($spe0613f->order_no, (int) round($spe4caa0['payment'] * 100), $spca4fc7); } } return true; } else { $sp2e47fc = @$sp9d4382['out_trade_no']; if (strlen($sp2e47fc) < 5) { throw new \Exception('交易单号未传入'); } $spe0613f = \App\Order::whereOrderNo($sp2e47fc)->firstOrFail(); if (!$spe0613f->pay_trade_no || !strlen($spe0613f->pay_trade_no)) { return false; } try { $sp31a710 = $this->getAccessToken($sp9d4382); $sp7854df = new Open\Client($sp31a710); } catch (\Exception $sp3f4aab) { \Log::error('Pay.Youzan.verify getAccessToken error#2', array('exception' => $sp3f4aab)); throw new \Exception('支付渠道响应超时，请刷新重试'); } $spc0e525 = array('qr_id' => $spe0613f->pay_trade_no, 'status' => 'TRADE_RECEIVED'); $spb72f32 = $sp7854df->get('youzan.trades.qr.get', '3.0.0', $spc0e525); $sp3a8ac9 = isset($spb72f32['response']) ? $spb72f32['response'] : $spb72f32; if (!isset($sp3a8ac9['total_results'])) { \Log::error('Pay.Youzan.verify with error：The result of [youzan.trades.qr.get] has no key named [total_results]', array('result' => $spb72f32)); return false; } if ($sp3a8ac9['total_results'] > 0 && count($sp3a8ac9['qr_trades']) > 0 && isset($sp3a8ac9['qr_trades'][0]['qr_id']) && $sp3a8ac9['qr_trades'][0]['qr_id'] === $spe0613f->pay_trade_no) { $sp2c5a7a = $sp3a8ac9['qr_trades'][0]; $spca4fc7 = $sp2c5a7a['tid']; $sp9a4d97($sp2e47fc, (int) round($sp2c5a7a['real_price'] * 100), $spca4fc7); return true; } else { return false; } } } }