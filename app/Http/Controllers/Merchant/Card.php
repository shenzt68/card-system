<?php
namespace App\Http\Controllers\Merchant; use App\Library\Response; use App\System; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Storage; class Card extends Controller { function get(Request $sp13451b, $spdf44ce = false, $sp5be447 = false, $specad63 = false) { $sp8e2ceb = $this->authQuery($sp13451b, \App\Card::class)->with(array('product' => function ($sp8e2ceb) { $sp8e2ceb->select(array('id', 'name')); })); $sp54e0e6 = $sp13451b->input('search', false); $sp59b33b = $sp13451b->input('val', false); if ($sp54e0e6 && $sp59b33b) { if ($sp54e0e6 == 'id') { $sp8e2ceb->where('id', $sp59b33b); } else { $sp8e2ceb->where($sp54e0e6, 'like', '%' . $sp59b33b . '%'); } } $sp3955fa = (int) $sp13451b->input('category_id'); $spcaeba2 = $sp13451b->input('product_id', -1); if ($sp3955fa > 0) { if ($spcaeba2 > 0) { $sp8e2ceb->where('product_id', $spcaeba2); } else { $sp8e2ceb->whereHas('product', function ($sp8e2ceb) use($sp3955fa) { $sp8e2ceb->where('category_id', $sp3955fa); }); } } $sp6002b5 = $sp13451b->input('status'); if (strlen($sp6002b5)) { $sp8e2ceb->whereIn('status', explode(',', $sp6002b5)); } $sp9367d1 = (int) $sp13451b->input('onlyCanSell'); if ($sp9367d1) { $sp8e2ceb->whereRaw('`count_all`>`count_sold`'); } $sp797c94 = $sp13451b->input('type'); if (strlen($sp797c94)) { $sp8e2ceb->whereIn('type', explode(',', $sp797c94)); } $sp21c95c = $sp13451b->input('trashed') === 'true'; if ($sp21c95c) { $sp8e2ceb->onlyTrashed(); } if ($sp5be447 === true) { if ($sp21c95c) { $sp8e2ceb->forceDelete(); } else { \App\Card::_trash($sp8e2ceb); } return Response::success(); } else { if ($sp21c95c && $specad63 === true) { \App\Card::_restore($sp8e2ceb); return Response::success(); } else { $sp8e2ceb->orderByRaw('`product_id`,`type`,`status`,`id`'); if ($spdf44ce === true) { $spbd7cdd = ''; $sp8e2ceb->chunk(100, function ($spa07a9b) use(&$spbd7cdd) { foreach ($spa07a9b as $spbcc049) { $spbd7cdd .= $spbcc049->card . '
'; } }); $spb72e8c = 'export_cards_' . $this->getUserIdOrFail($sp13451b) . '_' . date('YmdHis') . '.txt'; $spdcbcbb = array('Content-type' => 'text/plain', 'Content-Disposition' => sprintf('attachment; filename="%s"', $spb72e8c), 'Content-Length' => strlen($spbd7cdd)); return response()->make($spbd7cdd, 200, $spdcbcbb); } $sp26dcb6 = (int) $sp13451b->input('current_page', 1); $sp769c3e = (int) $sp13451b->input('per_page', 20); $spa67529 = $sp8e2ceb->paginate($sp769c3e, array('*'), 'page', $sp26dcb6); return Response::success($spa67529); } } } function export(Request $sp13451b) { return self::get($sp13451b, true); } function trash(Request $sp13451b) { $this->validate($sp13451b, array('ids' => 'required|string')); $sp60bb7e = $sp13451b->post('ids'); $sp8e2ceb = $this->authQuery($sp13451b, \App\Card::class)->whereIn('id', explode(',', $sp60bb7e)); \App\Card::_trash($sp8e2ceb); return Response::success(); } function restoreTrashed(Request $sp13451b) { $this->validate($sp13451b, array('ids' => 'required|string')); $sp60bb7e = $sp13451b->post('ids'); $sp8e2ceb = $this->authQuery($sp13451b, \App\Card::class)->whereIn('id', explode(',', $sp60bb7e)); \App\Card::_restore($sp8e2ceb); return Response::success(); } function deleteTrashed(Request $sp13451b) { $this->validate($sp13451b, array('ids' => 'required|string')); $sp60bb7e = $sp13451b->post('ids'); $this->authQuery($sp13451b, \App\Card::class)->whereIn('id', explode(',', $sp60bb7e))->forceDelete(); return Response::success(); } function deleteAll(Request $sp13451b) { return $this->get($sp13451b, false, true); } function restoreAll(Request $sp13451b) { return $this->get($sp13451b, false, false, true); } function add(Request $sp13451b) { $spcaeba2 = (int) $sp13451b->post('product_id'); $spa07a9b = $sp13451b->post('card'); $sp797c94 = (int) $sp13451b->post('type', \App\Card::TYPE_ONETIME); $sp864dfc = $sp13451b->post('is_check') === 'true'; if (str_contains($spa07a9b, '<') || str_contains($spa07a9b, '>')) { return Response::fail('卡密不能包含 < 或 > 符号'); } $spc2138c = $this->getUserIdOrFail($sp13451b); $sp3d861f = $this->authQuery($sp13451b, \App\Product::class)->where('id', $spcaeba2); $sp3d861f->firstOrFail(array('id')); if ($sp797c94 === \App\Card::TYPE_REPEAT) { if ($sp864dfc) { if (\App\Card::where('product_id', $spcaeba2)->where('card', $spa07a9b)->exists()) { return Response::fail('该卡密已经存在，添加失败'); } } $spbcc049 = new \App\Card(array('user_id' => $spc2138c, 'product_id' => $spcaeba2, 'card' => $spa07a9b, 'type' => \App\Card::TYPE_REPEAT, 'count_sold' => 0, 'count_all' => (int) $sp13451b->post('count_all', 1))); if ($spbcc049->count_all < 1 || $spbcc049->count_all > 10000000) { return Response::forbidden('可售总次数不能超过10000000'); } return DB::transaction(function () use($sp3d861f, $spbcc049) { $spbcc049->saveOrFail(); $sp863814 = $sp3d861f->lockForUpdate()->firstOrFail(); $sp863814->count_all += $spbcc049->count_all; $sp863814->saveOrFail(); return Response::success(); }); } else { $sp5aa60c = explode('
', $spa07a9b); $sp95c65f = count($sp5aa60c); $spa95b7e = 50000; if ($sp95c65f > $spa95b7e) { return Response::fail('每次添加不能超过 ' . $spa95b7e . ' 张'); } $sp61bd29 = array(); if ($sp864dfc) { $sp3e4fef = \App\Card::where('user_id', $spc2138c)->where('product_id', $spcaeba2)->get(array('card'))->all(); foreach ($sp3e4fef as $spd95c2b) { $sp61bd29[] = $spd95c2b['card']; } } $sp2d7001 = array(); $sp29b267 = 0; for ($sp51a993 = 0; $sp51a993 < $sp95c65f; $sp51a993++) { $sp46f3f8 = trim($sp5aa60c[$sp51a993]); if (strlen($sp46f3f8) < 1) { continue; } if (strlen($sp46f3f8) > 1024) { return Response::fail('第 ' . $sp51a993 . ' 张卡密 ' . $sp46f3f8 . ' 长度错误<br>卡密最大长度为1024'); } if ($sp864dfc) { if (in_array($sp46f3f8, $sp61bd29)) { continue; } $sp61bd29[] = $sp46f3f8; } $sp2d7001[] = array('user_id' => $spc2138c, 'product_id' => $spcaeba2, 'card' => $sp46f3f8, 'type' => \App\Card::TYPE_ONETIME); $sp29b267++; } if ($sp29b267 === 0) { return Response::success(); } return DB::transaction(function () use($sp3d861f, $sp2d7001, $sp29b267) { \App\Card::insert($sp2d7001); $sp863814 = $sp3d861f->lockForUpdate()->firstOrFail(); $sp863814->count_all += $sp29b267; $sp863814->saveOrFail(); return Response::success(); }); } } function edit(Request $sp13451b) { $sp7df839 = (int) $sp13451b->post('id'); $spbcc049 = $this->authQuery($sp13451b, \App\Card::class)->findOrFail($sp7df839); if ($spbcc049) { $sp2f5ad6 = $sp13451b->post('card'); $sp797c94 = (int) $sp13451b->post('type', \App\Card::TYPE_ONETIME); $sp724556 = (int) $sp13451b->post('count_all', 1); return DB::transaction(function () use($spbcc049, $sp2f5ad6, $sp797c94, $sp724556) { $spbcc049 = \App\Card::where('id', $spbcc049->id)->lockForUpdate()->firstOrFail(); $spbcc049->card = $sp2f5ad6; $spbcc049->type = $sp797c94; if ($spbcc049->type === \App\Card::TYPE_REPEAT) { if ($sp724556 < $spbcc049->count_sold) { return Response::forbidden('可售总次数不能低于当前已售次数'); } if ($sp724556 < 1 || $sp724556 > 10000000) { return Response::forbidden('可售总次数不能超过10000000'); } $spbcc049->count_all = $sp724556; } else { $spbcc049->count_all = 1; } $spbcc049->saveOrFail(); $sp863814 = $spbcc049->product()->lockForUpdate()->firstOrFail(); $sp863814->count_all -= $spbcc049->count_all; $sp863814->count_all += $sp724556; $sp863814->saveOrFail(); return Response::success(); }); } return Response::success(); } }