<?php
use Illuminate\Database\Seeder; class ProductsSeeder extends Seeder { public function run() { $sp6fe8b9 = \App\User::first()->id; $spdd6a6c = new \App\Category(); $spdd6a6c->user_id = $sp6fe8b9; $spdd6a6c->name = '测试分组'; $spdd6a6c->enabled = true; $spdd6a6c->save(); $spdd6a6c = new \App\Category(); $spdd6a6c->user_id = $sp6fe8b9; $spdd6a6c->name = '这里是一个啦啦啦啦啦啦超级无敌爆炸螺旋长的商品类别标题'; $spdd6a6c->enabled = true; $spdd6a6c->save(); $spdd6a6c = new \App\Category(); $spdd6a6c->user_id = $sp6fe8b9; $spdd6a6c->name = '密码123456'; $spdd6a6c->enabled = true; $spdd6a6c->password = '123456'; $spdd6a6c->password_open = true; $spdd6a6c->save(); $sp0a72f9 = new \App\Product(); $sp0a72f9->id = 1; $sp0a72f9->user_id = $sp6fe8b9; $sp0a72f9->category_id = 1; $sp0a72f9->name = '测试商品'; $sp0a72f9->description = '这里是测试商品的一段简短的描述'; $sp0a72f9->price = 1; $sp0a72f9->enabled = true; $sp0a72f9->support_coupon = true; $sp0a72f9->count_sold = 1; $sp0a72f9->count_all = 3; $sp0a72f9->instructions = '充值网址: XXXXX'; $sp0a72f9->save(); $sp0a72f9 = new \App\Product(); $sp0a72f9->id = 2; $sp0a72f9->user_id = $sp6fe8b9; $sp0a72f9->category_id = 1; $sp0a72f9->name = '重复测试密码123456'; $sp0a72f9->description = '<h2>商品描述</h2>所十二星座运势查询,提前预测2016年十二星座运势内容,让你能够占卜吉凶;2016年生肖运势测算,生肖开运,周易风水。'; $sp0a72f9->instructions = '充值网址: XXXXX'; $sp0a72f9->password = '123456'; $sp0a72f9->password_open = true; $sp0a72f9->support_coupon = true; $sp0a72f9->price = 10; $sp0a72f9->price_whole = '[["2","8"],["10","5"]]'; $sp0a72f9->enabled = true; $sp0a72f9->count_sold = 2; $sp0a72f9->count_all = 100; $sp0a72f9->count_warn = 10; $sp0a72f9->save(); $sp0a72f9 = new \App\Product(); $sp0a72f9->user_id = $sp6fe8b9; $sp0a72f9->category_id = 2; $sp0a72f9->name = '测试商品_2'; $sp0a72f9->description = '这里是测试商品的一段简短的描述, 可以插入多媒体文本'; $sp0a72f9->price = 1; $sp0a72f9->enabled = true; $sp0a72f9->save(); $sp0a72f9 = new \App\Product(); $sp0a72f9->user_id = $sp6fe8b9; $sp0a72f9->category_id = 3; $sp0a72f9->name = '测试商品_3'; $sp0a72f9->description = '这里是测试商品的一段简短的描述, 可以插入多媒体文本'; $sp0a72f9->price = 1; $sp0a72f9->enabled = true; $sp0a72f9->save(); } }