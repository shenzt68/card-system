<?php
namespace App\Http\Controllers\Shop; use App\Card; use App\Category; use App\Library\FundHelper; use App\Library\Helper; use App\Library\LogHelper; use App\Product; use App\Library\Response; use Gateway\Pay\Pay as GatewayPay; use App\Library\Geetest; use App\Mail\ProductCountWarn; use App\System; use Carbon\Carbon; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Log; use Illuminate\Support\Facades\Mail; class Pay extends Controller { public function __construct() { define('SYS_NAME', config('app.name')); define('SYS_URL', config('app.url')); define('SYS_URL_API', config('app.url_api')); } private $payApi = null; public function goPay($sp517903, $sp4c2308, $sp3cb532, $sp38a73e, $sp14bf94) { try { $sp1c6547 = json_decode($sp38a73e->config, true); $sp1c6547['payway'] = $sp38a73e->way; GatewayPay::getDriver($sp38a73e)->goPay($sp1c6547, $sp4c2308, $sp3cb532, $sp3cb532, $sp14bf94); return self::renderResultPage($sp517903, array('success' => false, 'title' => trans('shop.please_wait'), 'msg' => trans('shop.please_wait_for_pay'))); } catch (\Exception $sp0b065e) { if (config('app.debug')) { return self::renderResultPage($sp517903, array('msg' => $sp0b065e->getMessage() . '<br>' . str_replace('
', '<br>', $sp0b065e->getTraceAsString()))); } return self::renderResultPage($sp517903, array('msg' => $sp0b065e->getMessage())); } } function buy(Request $sp517903) { $sp3d6d7b = $sp517903->input('customer'); if (strlen($sp3d6d7b) !== 32) { return self::renderResultPage($sp517903, array('msg' => '提交超时，请刷新购买页面并重新提交<br><br>
当前网址: ' . $sp517903->getQueryString() . '
提交内容: ' . var_export($sp3d6d7b) . ', 提交长度:' . strlen($sp3d6d7b) . '<br>
若您刷新后仍然出现此问题. 请加网站客服反馈')); } if (System::_getInt('vcode_shop_buy') === 1) { try { $this->validateCaptcha($sp517903); } catch (\Throwable $sp0b065e) { return self::renderResultPage($sp517903, array('msg' => trans('validation.captcha'))); } } $spc4a21e = (int) $sp517903->input('category_id'); $spb429e3 = (int) $sp517903->input('product_id'); $sp7aa4d7 = (int) $sp517903->input('count'); $sp53e03c = $sp517903->input('coupon'); $sp566de3 = $sp517903->input('contact'); $sp7726a9 = $sp517903->input('contact_ext') ?? null; $spa3e140 = !empty(@json_decode($sp7726a9, true)['_mobile']); $sp6a791a = (int) $sp517903->input('pay_id'); if (!$spc4a21e || !$spb429e3) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.required'))); } if (strlen($sp566de3) < 1) { return self::renderResultPage($sp517903, array('msg' => trans('shop.contact.required'))); } $sp207045 = null; if (System::_getInt('order_query_password_open')) { $sp207045 = $sp517903->input('query_password'); if (strlen($sp207045) < 1) { return self::renderResultPage($sp517903, array('msg' => trans('shop.query_password.required'))); } if (strlen($sp207045) < 6 || Helper::isWakePassword($sp207045)) { return self::renderResultPage($sp517903, array('msg' => trans('shop.query_password.weak'))); } } $spe223d2 = Category::findOrFail($spc4a21e); $sp427eba = Product::where('id', $spb429e3)->where('category_id', $spc4a21e)->where('enabled', 1)->with(array('user'))->first(); if ($sp427eba == null || $sp427eba->user == null) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.not_found'))); } if (!$sp427eba->enabled) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.not_on_sell'))); } if ($sp427eba->password_open) { if ($sp427eba->password !== $sp517903->input('product_password')) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.password_error'))); } } else { if ($spe223d2->password_open) { if ($spe223d2->password !== $sp517903->input('category_password')) { if ($spe223d2->getTmpPassword() !== $sp517903->input('category_password')) { return self::renderResultPage($sp517903, array('msg' => trans('shop.category.password_error'))); } } } } if ($sp7aa4d7 < $sp427eba->buy_min) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.buy_min', array('num' => $sp427eba->buy_min)))); } if ($sp7aa4d7 > $sp427eba->buy_max) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.buy_max', array('num' => $sp427eba->buy_max)))); } if ($sp427eba->count < $sp7aa4d7) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.out_of_stock'))); } $sp38a73e = \App\Pay::find($sp6a791a); if ($sp38a73e == null || !$sp38a73e->enabled) { return self::renderResultPage($sp517903, array('msg' => trans('shop.pay.not_found'))); } $sp6199d2 = $sp427eba->price; if ($sp427eba->price_whole) { $sp9d4eb4 = json_decode($sp427eba->price_whole, true); for ($sp1148f5 = count($sp9d4eb4) - 1; $sp1148f5 >= 0; $sp1148f5--) { if ($sp7aa4d7 >= (int) $sp9d4eb4[$sp1148f5][0]) { $sp6199d2 = (int) $sp9d4eb4[$sp1148f5][1]; break; } } } $sp0ec680 = $sp7aa4d7 * $sp6199d2; $sp14bf94 = $sp0ec680; $spd17af2 = 0; $sp83ce48 = null; if ($sp427eba->support_coupon && strlen($sp53e03c) > 0) { $sp1da1a4 = \App\Coupon::where('user_id', $sp427eba->user_id)->where('coupon', $sp53e03c)->where('expire_at', '>', Carbon::now())->whereRaw('`count_used`<`count_all`')->get(); foreach ($sp1da1a4 as $spe4da60) { if ($spe4da60->category_id === -1 || $spe4da60->category_id === $spc4a21e && ($spe4da60->product_id === -1 || $spe4da60->product_id === $spb429e3)) { if ($spe4da60->discount_type === \App\Coupon::DISCOUNT_TYPE_AMOUNT && $sp14bf94 >= $spe4da60->discount_val) { $sp83ce48 = $spe4da60; $spd17af2 = $spe4da60->discount_val; break; } if ($spe4da60->discount_type === \App\Coupon::DISCOUNT_TYPE_PERCENT) { $sp83ce48 = $spe4da60; $spd17af2 = (int) round($sp14bf94 * $spe4da60->discount_val / 100); break; } } } if ($sp83ce48 === null) { return self::renderResultPage($sp517903, array('msg' => trans('shop.coupon.invalid'))); } $sp14bf94 -= $spd17af2; } $sp3709d0 = (int) round($sp14bf94 * $sp38a73e->fee_system); $sp9b38c3 = $sp14bf94 - $sp3709d0; $spf83630 = $spa3e140 ? System::_getInt('sms_price', 10) : 0; $sp14bf94 += $spf83630; $sp1d4095 = $sp7aa4d7 * $sp427eba->cost; $sp4c2308 = \App\Order::unique_no(); try { DB::transaction(function () use($sp427eba, $sp4c2308, $sp83ce48, $sp566de3, $sp7726a9, $sp207045, $sp3d6d7b, $sp7aa4d7, $sp1d4095, $sp0ec680, $spf83630, $spd17af2, $sp14bf94, $sp38a73e, $sp3709d0, $sp9b38c3) { if ($sp83ce48) { $sp83ce48->status = \App\Coupon::STATUS_USED; $sp83ce48->count_used++; $sp83ce48->save(); $sp178a23 = '使用优惠券: ' . $sp83ce48->coupon; } else { $sp178a23 = null; } $sp990b3b = new \App\Order(array('user_id' => $sp427eba->user_id, 'order_no' => $sp4c2308, 'product_id' => $sp427eba->id, 'product_name' => $sp427eba->name, 'count' => $sp7aa4d7, 'ip' => Helper::getIP(), 'customer' => $sp3d6d7b, 'contact' => $sp566de3, 'contact_ext' => $sp7726a9, 'query_password' => $sp207045, 'cost' => $sp1d4095, 'price' => $sp0ec680, 'sms_price' => $spf83630, 'discount' => $spd17af2, 'paid' => $sp14bf94, 'pay_id' => $sp38a73e->id, 'fee' => $sp3709d0, 'system_fee' => $sp3709d0, 'income' => $sp9b38c3, 'status' => \App\Order::STATUS_UNPAY, 'remark' => $sp178a23, 'created_at' => Carbon::now())); $sp990b3b->saveOrFail(); }); } catch (\Throwable $sp0b065e) { Log::error('Shop.Pay.buy 下单失败', array('exception' => $sp0b065e)); return self::renderResultPage($sp517903, array('msg' => trans('shop.pay.internal_error'))); } if ($sp14bf94 === 0) { $this->shipOrder($sp517903, $sp4c2308, $sp14bf94, null); return redirect()->away(route('pay.result', array($sp4c2308), false)); } $sp3cb532 = $sp4c2308; return $this->goPay($sp517903, $sp4c2308, $sp3cb532, $sp38a73e, $sp14bf94); } function pay(Request $sp517903, $sp4c2308) { $sp990b3b = \App\Order::whereOrderNo($sp4c2308)->first(); if ($sp990b3b == null) { return self::renderResultPage($sp517903, array('msg' => trans('shop.order.not_found'))); } if ($sp990b3b->status !== \App\Order::STATUS_UNPAY) { return redirect('/pay/result/' . $sp4c2308); } $spca966c = 'pay: ' . $sp990b3b->pay_id; $sp38a73e = $sp990b3b->pay; if (!$sp38a73e) { \Log::error($spca966c . ' cannot find Pay'); return $this->renderResultPage($sp517903, array('msg' => trans('shop.pay.not_found'))); } $spca966c .= ',' . $sp38a73e->driver; $sp1c6547 = json_decode($sp38a73e->config, true); $sp1c6547['payway'] = $sp38a73e->way; $sp1c6547['out_trade_no'] = $sp4c2308; try { $this->payApi = GatewayPay::getDriver($sp38a73e); } catch (\Exception $sp0b065e) { \Log::error($spca966c . ' cannot find Driver: ' . $sp0b065e->getMessage()); return $this->renderResultPage($sp517903, array('msg' => trans('shop.pay.driver_not_found'))); } if ($this->payApi->verify($sp1c6547, function ($sp4c2308, $sp1058e7, $sp7b7d53) use($sp517903) { try { $this->shipOrder($sp517903, $sp4c2308, $sp1058e7, $sp7b7d53); } catch (\Exception $sp0b065e) { $this->renderResultPage($sp517903, array('success' => false, 'msg' => $sp0b065e->getMessage())); } })) { \Log::notice($spca966c . ' already success' . '

'); return redirect('/pay/result/' . $sp4c2308); } if ($sp990b3b->created_at < Carbon::now()->addMinutes(-System::_getInt('order_pay_timeout_minutes', 5))) { return $this->renderResultPage($sp517903, array('msg' => trans('shop.order.expired'))); } $sp427eba = Product::where('id', $sp990b3b->product_id)->where('enabled', 1)->first(); if ($sp427eba == null) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.not_on_sell'))); } $sp427eba->setAttribute('count', count($sp427eba->cards) ? $sp427eba->cards[0]->count : 0); if ($sp427eba->count < $sp990b3b->count) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.out_of_stock'))); } $sp3cb532 = $sp4c2308; return $this->goPay($sp517903, $sp4c2308, $sp3cb532, $sp38a73e, $sp990b3b->paid); } function qrcode(Request $sp517903, $sp4c2308, $sp74d59c) { $sp990b3b = \App\Order::whereOrderNo($sp4c2308)->with('product')->first(); if ($sp990b3b == null) { return self::renderResultPage($sp517903, array('msg' => trans('shop.order.not_found'))); } if ($sp990b3b->created_at < Carbon::now()->addMinutes(-System::_getInt('order_pay_timeout_minutes', 5))) { return $this->renderResultPage($sp517903, array('msg' => trans('shop.order.expired'))); } if ($sp990b3b->product_id !== \App\Product::ID_API) { $sp427eba = $sp990b3b->product; if ($sp427eba == null) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.not_found'))); } if ($sp427eba->count < $sp990b3b->count) { return self::renderResultPage($sp517903, array('msg' => trans('shop.product.out_of_stock'))); } } if (strpos($sp74d59c, '..')) { return $this->msg(trans('shop.you_are_sb')); } return view('pay/' . $sp74d59c, array('pay_id' => $sp990b3b->pay_id, 'name' => $sp990b3b->product_id === \App\Product::ID_API ? $sp990b3b->api_out_no : $sp990b3b->product->name . ' x ' . $sp990b3b->count . '件', 'amount' => $sp990b3b->paid, 'qrcode' => $sp517903->get('url'), 'id' => $sp4c2308)); } function qrQuery(Request $sp517903, $sp6a791a) { $sp240bfc = $sp517903->input('id'); if (isset($sp240bfc[5])) { return self::payReturn($sp517903, $sp6a791a, $sp240bfc); } else { return Response::fail('order_no error'); } } function payReturn(Request $sp517903, $sp6a791a, $sp4c2308 = null) { $spca966c = 'payReturn: ' . $sp6a791a; \Log::debug($spca966c); $sp38a73e = \App\Pay::where('id', $sp6a791a)->first(); if (!$sp38a73e) { return $this->renderResultPage($sp517903, array('success' => 0, 'msg' => trans('shop.pay.not_found'))); } $spca966c .= ',' . $sp38a73e->driver; if ($sp4c2308 && isset($sp4c2308[5])) { $sp990b3b = \App\Order::whereOrderNo($sp4c2308)->firstOrFail(); if ($sp990b3b && ($sp990b3b->status === \App\Order::STATUS_PAID || $sp990b3b->status === \App\Order::STATUS_SUCCESS)) { \Log::notice($spca966c . ' already success' . '

'); if ($sp517903->ajax()) { return self::renderResultPage($sp517903, array('success' => 1, 'data' => '/pay/result/' . $sp4c2308), array('order' => $sp990b3b)); } else { return redirect('/pay/result/' . $sp4c2308); } } } try { $this->payApi = GatewayPay::getDriver($sp38a73e); } catch (\Exception $sp0b065e) { \Log::error($spca966c . ' cannot find Driver: ' . $sp0b065e->getMessage()); return $this->renderResultPage($sp517903, array('success' => 0, 'msg' => trans('shop.pay.driver_not_found'))); } $sp1c6547 = json_decode($sp38a73e->config, true); $sp1c6547['out_trade_no'] = $sp4c2308; $sp1c6547['payway'] = $sp38a73e->way; Log::debug($spca966c . ' will verify'); if ($this->payApi->verify($sp1c6547, function ($sp9ce84d, $sp1058e7, $sp7b7d53) use($sp517903, $spca966c, &$sp4c2308) { $sp4c2308 = $sp9ce84d; try { Log::debug($spca966c . " shipOrder start, order_no: {$sp4c2308}, amount: {$sp1058e7}, trade_no: {$sp7b7d53}"); $this->shipOrder($sp517903, $sp4c2308, $sp1058e7, $sp7b7d53); Log::debug($spca966c . ' shipOrder end, order_no: ' . $sp4c2308); } catch (\Exception $sp0b065e) { Log::error($spca966c . ' shipOrder Exception: ' . $sp0b065e->getMessage(), array('exception' => $sp0b065e)); } })) { Log::debug($spca966c . ' verify finished: 1' . '

'); if ($sp517903->ajax()) { return self::renderResultPage($sp517903, array('success' => 1, 'data' => '/pay/result/' . $sp4c2308)); } else { return redirect('/pay/result/' . $sp4c2308); } } else { Log::debug($spca966c . ' verify finished: 0' . '

'); return $this->renderResultPage($sp517903, array('success' => 0, 'msg' => trans('shop.pay.verify_failed'))); } } function payNotify(Request $sp517903, $sp6a791a) { $spca966c = 'payNotify pay_id: ' . $sp6a791a; Log::debug($spca966c); $sp38a73e = \App\Pay::where('id', $sp6a791a)->first(); if (!$sp38a73e) { Log::error($spca966c . ' cannot find PayModel'); echo 'fail'; die; } $spca966c .= ',' . $sp38a73e->driver; try { $this->payApi = GatewayPay::getDriver($sp38a73e); } catch (\Exception $sp0b065e) { Log::error($spca966c . ' cannot find Driver: ' . $sp0b065e->getMessage()); echo 'fail'; die; } $sp1c6547 = json_decode($sp38a73e->config, true); $sp1c6547['payway'] = $sp38a73e->way; $sp1c6547['isNotify'] = true; Log::debug($spca966c . ' will verify'); $spd9807c = $this->payApi->verify($sp1c6547, function ($sp4c2308, $sp1058e7, $sp7b7d53) use($sp517903, $spca966c) { try { Log::debug($spca966c . " shipOrder start, order_no: {$sp4c2308}, amount: {$sp1058e7}, trade_no: {$sp7b7d53}"); $this->shipOrder($sp517903, $sp4c2308, $sp1058e7, $sp7b7d53); Log::debug($spca966c . ' shipOrder end, order_no: ' . $sp4c2308); } catch (\Exception $sp0b065e) { Log::error($spca966c . ' shipOrder Exception: ' . $sp0b065e->getMessage()); } }); Log::debug($spca966c . ' notify finished: ' . (int) $spd9807c . '

'); die; } function result(Request $sp517903, $sp4c2308) { $sp990b3b = \App\Order::where('order_no', $sp4c2308)->first(); if ($sp990b3b == null) { return self::renderResultPage($sp517903, array('msg' => trans('shop.order.not_found'))); } if ($sp990b3b->status === \App\Order::STATUS_PAID) { $sp788d0e = $sp990b3b->user->qq; if ($sp990b3b->product) { if ($sp990b3b->product->delivery === \App\Product::DELIVERY_MANUAL) { $spfc43d6 = trans('shop.order.msg_product_manual_please_wait'); } else { $spfc43d6 = trans('shop.order.msg_product_out_of_stock_not_send'); } } else { $spfc43d6 = trans('shop.order.msg_product_deleted'); } if ($sp788d0e) { $spfc43d6 .= '<br><a href="http://wpa.qq.com/msgrd?v=3&uin=' . $sp788d0e . '&site=qq&menu=yes" target="_blank">客服QQ:' . $sp788d0e . '</a>'; } return self::renderResultPage($sp517903, array('success' => false, 'title' => trans('shop.order_is_paid'), 'msg' => $spfc43d6), array('order' => $sp990b3b)); } elseif ($sp990b3b->status >= \App\Order::STATUS_SUCCESS) { return self::showOrderResult($sp517903, $sp990b3b); } return self::renderResultPage($sp517903, array('success' => false, 'msg' => $sp990b3b->remark ? trans('shop.order_process_failed_because', array('reason' => $sp990b3b->remark)) : trans('shop.order_process_failed_default')), array('order' => $sp990b3b)); } function renderResultPage(Request $sp517903, $sp1b1403, $spfd94a6 = array()) { if ($sp517903->ajax()) { if (@$sp1b1403['success']) { return Response::success($sp1b1403['data']); } else { return Response::fail('error', $sp1b1403['msg']); } } else { return view('pay.result', array_merge(array('result' => $sp1b1403, 'data' => $spfd94a6), $spfd94a6)); } } function shipOrder($sp517903, $sp4c2308, $sp1058e7, $sp7b7d53) { $sp990b3b = \App\Order::whereOrderNo($sp4c2308)->first(); if ($sp990b3b === null) { Log::error('shipOrder: No query results for model [App\\Order:' . $sp4c2308 . ',trade_no:' . $sp7b7d53 . ',amount:' . $sp1058e7 . ']. die(\'success\');'); die('success'); } if ($sp990b3b->paid > $sp1058e7) { Log::alert('shipOrder, price may error, order_no:' . $sp4c2308 . ', paid:' . $sp990b3b->paid . ', $amount get:' . $sp1058e7); $sp990b3b->remark = '支付金额(' . sprintf('%0.2f', $sp1058e7 / 100) . ') 小于 订单金额(' . sprintf('%0.2f', $sp990b3b->paid / 100) . ')'; $sp990b3b->save(); throw new \Exception($sp990b3b->remark); } $sp427eba = null; if ($sp990b3b->status === \App\Order::STATUS_UNPAY) { Log::debug('shipOrder.first_process:' . $sp4c2308); if (FundHelper::orderSuccess($sp990b3b->id, function ($spa00d80) use($sp7b7d53, &$sp990b3b, &$sp427eba) { $sp990b3b = $spa00d80; if ($sp990b3b->status !== \App\Order::STATUS_UNPAY) { \Log::debug('Shop.Pay.shipOrder: .first_process:' . $sp990b3b->order_no . ' already processed! #2'); return false; } $sp427eba = $sp990b3b->product()->lockForUpdate()->firstOrFail(); $sp990b3b->pay_trade_no = $sp7b7d53; $sp990b3b->paid_at = Carbon::now(); if ($sp427eba->delivery === \App\Product::DELIVERY_MANUAL) { $sp990b3b->status = \App\Order::STATUS_PAID; $sp990b3b->send_status = \App\Order::SEND_STATUS_CARD_UN; $sp990b3b->saveOrFail(); return true; } if ($sp427eba->delivery === \App\Product::DELIVERY_API) { $sp3d31e0 = $sp427eba->createApiCards($sp990b3b); } else { $sp3d31e0 = Card::where('product_id', $sp990b3b->product_id)->whereRaw('`count_sold`<`count_all`')->take($sp990b3b->count)->lockForUpdate()->get(); } $spf15d61 = false; if (count($sp3d31e0) === $sp990b3b->count) { $spf15d61 = true; } else { if (count($sp3d31e0)) { foreach ($sp3d31e0 as $sp431b94) { if ($sp431b94->type === \App\Card::TYPE_REPEAT && $sp431b94->count >= $sp990b3b->count) { $sp3d31e0 = array($sp431b94); $spf15d61 = true; break; } } } } if ($spf15d61 === false) { Log::alert('Shop.Pay.shipOrder: 订单:' . $sp990b3b->order_no . ', 购买数量:' . $sp990b3b->count . ', 卡数量:' . count($sp3d31e0) . ' 卡密不足(已支付 未发货)'); $sp990b3b->status = \App\Order::STATUS_PAID; $sp990b3b->saveOrFail(); return true; } else { $sp769673 = array(); foreach ($sp3d31e0 as $sp431b94) { $sp769673[] = $sp431b94->id; } $sp990b3b->cards()->attach($sp769673); if (count($sp3d31e0) === 1 && $sp3d31e0[0]->type === \App\Card::TYPE_REPEAT) { \App\Card::where('id', $sp769673[0])->update(array('status' => \App\Card::STATUS_SOLD, 'count_sold' => DB::raw('`count_sold`+' . $sp990b3b->count))); } else { \App\Card::whereIn('id', $sp769673)->update(array('status' => \App\Card::STATUS_SOLD, 'count_sold' => DB::raw('`count_sold`+1'))); } $sp990b3b->status = \App\Order::STATUS_SUCCESS; $sp990b3b->saveOrFail(); $sp427eba->count_sold += $sp990b3b->count; $sp427eba->saveOrFail(); return FundHelper::ACTION_CONTINUE; } })) { if ($sp427eba->count_warn > 0 && $sp427eba->count < $sp427eba->count_warn) { try { Mail::to($sp990b3b->user->email)->Queue(new ProductCountWarn($sp427eba, $sp427eba->count)); } catch (\Throwable $sp0b065e) { LogHelper::setLogFile('mail'); Log::error('shipOrder.count_warn error', array('product_id' => $sp990b3b->product_id, 'email' => $sp990b3b->user->email, 'exception' => $sp0b065e->getMessage())); LogHelper::setLogFile('card'); } } if (System::_getInt('mail_send_order')) { $sp376fbd = @json_decode($sp990b3b->contact_ext, true)['_mail']; if ($sp376fbd) { $sp990b3b->sendEmail($sp376fbd); } } if ($sp990b3b->status === \App\Order::STATUS_SUCCESS && System::_getInt('sms_send_order')) { $sp85b5f0 = @json_decode($sp990b3b->contact_ext, true)['_mobile']; if ($sp85b5f0) { $sp990b3b->sendSms($sp85b5f0); } } } else { if ($sp990b3b->status !== \App\Order::STATUS_UNPAY) { } else { Log::error('Pay.shipOrder.orderSuccess Failed.'); return FALSE; } } } else { Log::debug('Shop.Pay.shipOrder: .order_no:' . $sp990b3b->order_no . ' already processed! #1'); } return FALSE; } private function showOrderResult($sp517903, $sp990b3b) { return self::renderResultPage($sp517903, array('success' => true, 'msg' => $sp990b3b->getSendMessage()), array('card_txt' => join('&#013;&#010;', $sp990b3b->getCardsArray()), 'order' => $sp990b3b, 'product' => $sp990b3b->product)); } }