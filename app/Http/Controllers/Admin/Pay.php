<?php
namespace App\Http\Controllers\Admin; use App\Library\Helper; use Carbon\Carbon; use function foo\func; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use App\Library\Response; class Pay extends Controller { function get(Request $spf066f3) { $sp5044a7 = \App\Pay::orderBy('sort'); $sp4ec09d = $spf066f3->post('enabled'); if (strlen($sp4ec09d)) { $sp5044a7->whereIn('enabled', explode(',', $sp4ec09d)); } $spca736c = $spf066f3->post('search', false); $sp56dec1 = $spf066f3->post('val', false); if ($spca736c && $sp56dec1) { if ($spca736c == 'simple') { return Response::success($sp5044a7->get(array('id', 'name'))); } elseif ($spca736c == 'id') { $sp5044a7->where('id', $sp56dec1); } else { $sp5044a7->where($spca736c, 'like', '%' . $sp56dec1 . '%'); } } $sp293456 = $sp5044a7->get(); return Response::success(array('list' => $sp293456, 'urls' => array('url' => config('app.url'), 'url_api' => config('app.url_api')))); } function stat(Request $spf066f3) { $this->validate($spf066f3, array('day' => 'required|integer|between:1,30')); $sp403e35 = (int) $spf066f3->input('day'); if ($sp403e35 === 30) { $sp586aba = Carbon::now()->addMonths(-1); } else { $sp586aba = Carbon::now()->addDays(-$sp403e35); } $sp293456 = $this->authQuery($spf066f3, \App\Order::class)->where(function ($sp5044a7) { $sp5044a7->where('status', \App\Order::STATUS_PAID)->orWhere('status', \App\Order::STATUS_SUCCESS); })->where('paid_at', '>=', $sp586aba)->with(array('pay' => function ($sp5044a7) { $sp5044a7->select(array('id', 'name')); }))->groupBy('pay_id')->selectRaw('`pay_id`,COUNT(*) as "count",SUM(`paid`) as "sum"')->get()->toArray(); $sp9b52fe = array(); foreach ($sp293456 as $sp10eb73) { if (isset($sp10eb73['pay']) && isset($sp10eb73['pay']['name'])) { $sp3437bd = $sp10eb73['pay']['name']; } else { $sp3437bd = '未知方式#' . $sp10eb73['pay_id']; } $sp9b52fe[$sp3437bd] = array((int) $sp10eb73['count'], (int) $sp10eb73['sum']); } return Response::success($sp9b52fe); } function edit(Request $spf066f3) { $this->validate($spf066f3, array('id' => 'required|integer', 'name' => 'required|string', 'img' => 'required|string', 'driver' => 'required|string', 'way' => 'required|string', 'config' => 'required|string')); $sp3c46ab = (int) $spf066f3->post('id'); $sp34e4b5 = $spf066f3->post('name'); $sp3334e2 = $spf066f3->post('img'); $spd73e08 = $spf066f3->post('comment'); $spd1dcf7 = $spf066f3->post('driver'); $spf9a85f = $spf066f3->post('way'); $sp9d4382 = $spf066f3->post('config'); $sp4ec09d = (int) $spf066f3->post('enabled'); $sp5de949 = \App\Pay::find($sp3c46ab); if (!$sp5de949) { $sp5de949 = new \App\Pay(); } $sp5de949->name = $sp34e4b5; $sp5de949->img = $sp3334e2; $sp5de949->comment = $spd73e08; $sp5de949->driver = $spd1dcf7; $sp5de949->way = $spf9a85f; $sp5de949->config = $sp9d4382; $sp5de949->enabled = $sp4ec09d; $sp5de949->fee_system = $spf066f3->post('fee_system'); $sp5de949->saveOrFail(); return Response::success(); } function comment(Request $spf066f3) { $this->validate($spf066f3, array('id' => 'required|integer')); $sp3c46ab = (int) $spf066f3->post('id'); $sp5de949 = \App\Pay::findOrFail($sp3c46ab); $sp5de949->comment = $spf066f3->post('comment'); $sp5de949->save(); return Response::success(); } function sort(Request $spf066f3) { $this->validate($spf066f3, array('id' => 'required|integer')); $sp3c46ab = (int) $spf066f3->post('id'); $sp5de949 = \App\Pay::findOrFail($sp3c46ab); $sp5de949->sort = (int) $spf066f3->post('sort', 1000); $sp5de949->save(); return Response::success(); } function fee_system(Request $spf066f3) { $this->validate($spf066f3, array('id' => 'required|integer')); $sp3c46ab = (int) $spf066f3->post('id'); $sp5de949 = \App\Pay::findOrFail($sp3c46ab); $sp5de949->fee_system = $spf066f3->post('fee_system'); $sp5de949->saveOrFail(); return Response::success(); } function enable(Request $spf066f3) { $this->validate($spf066f3, array('ids' => 'required|string', 'enabled' => 'required|integer|between:0,3')); $sp1f71d9 = $spf066f3->post('ids'); $sp4ec09d = (int) $spf066f3->post('enabled'); \App\Pay::whereIn('id', explode(',', $sp1f71d9))->update(array('enabled' => $sp4ec09d)); return Response::success(); } function delete(Request $spf066f3) { $this->validate($spf066f3, array('id' => 'required|integer')); $sp3c46ab = (int) $spf066f3->post('id'); \App\Pay::whereId($sp3c46ab)->delete(); return Response::success(); } }