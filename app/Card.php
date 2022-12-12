<?php
namespace App; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Support\Facades\DB; class Card extends Model { protected $guarded = array(); use SoftDeletes; protected $dates = array('deleted_at'); const STATUS_NORMAL = 0; const STATUS_SOLD = 1; const STATUS_USED = 2; const TYPE_ONETIME = 0; const TYPE_REPEAT = 1; function orders() { return $this->hasMany(Order::class); } function product() { return $this->belongsTo(Product::class); } function getCountAttribute() { return $this->count_all - $this->count_sold; } public static function add_cards($spacf00d, $spb429e3, $sp22c639, $sp0194a7, $sp84f9ba, $sp12232b) { DB::statement('call add_cards(?,?,?,?,?,?)', array($spacf00d, $spb429e3, $sp22c639, $sp0194a7, $sp84f9ba, (int) $sp12232b)); } public static function _trash($sp30241a) { DB::transaction(function () use($sp30241a) { $sp005ad9 = clone $sp30241a; $sp005ad9->selectRaw('`product_id`,SUM(`count_all`-`count_sold`) as `count_left`')->groupBy('product_id')->orderByRaw('`product_id`')->chunk(100, function ($sp355056) { foreach ($sp355056 as $spe8264d) { $sp427eba = \App\Product::where('id', $spe8264d->product_id)->lockForUpdate()->first(); if ($sp427eba) { $sp427eba->count_all -= $spe8264d->count_left; $sp427eba->saveOrFail(); } } }); $sp30241a->delete(); return true; }); } public static function _restore($sp30241a) { DB::transaction(function () use($sp30241a) { $sp005ad9 = clone $sp30241a; $sp005ad9->selectRaw('`product_id`,SUM(`count_all`-`count_sold`) as `count_left`')->groupBy('product_id')->orderByRaw('`product_id`')->chunk(100, function ($sp355056) { foreach ($sp355056 as $spe8264d) { $sp427eba = \App\Product::where('id', $spe8264d->product_id)->lockForUpdate()->first(); if ($sp427eba) { $sp427eba->count_all += $spe8264d->count_left; $sp427eba->saveOrFail(); } } }); $sp30241a->restore(); return true; }); } }