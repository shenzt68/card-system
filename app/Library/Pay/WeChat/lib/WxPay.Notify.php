<?php
class WxPayNotify extends WxPayNotifyReply { public final function Handle($spad580d = true) { $spb72f32 = WxpayApi::notify(array($this, 'NotifyCallBack'), $sp417133); if ($spb72f32 == false) { $this->SetReturn_code('FAIL'); $this->SetReturn_msg($sp417133); $this->ReplyNotify(false); return; } else { $this->SetReturn_code('SUCCESS'); $this->SetReturn_msg('OK'); } $this->ReplyNotify($spad580d); } public function NotifyProcess($sp6fd648, &$sp417133) { return true; } public final function NotifyCallBack($sp6fd648) { $sp417133 = 'OK'; $spb72f32 = $this->NotifyProcess($sp6fd648, $sp417133); if ($spb72f32 == true) { $this->SetReturn_code('SUCCESS'); $this->SetReturn_msg('OK'); } else { $this->SetReturn_code('FAIL'); $this->SetReturn_msg($sp417133); } return $spb72f32; } private final function ReplyNotify($spad580d = true) { if ($spad580d == true && $this->GetReturn_code() == 'SUCCESS') { $this->SetSign(); } WxpayApi::replyNotify($this->ToXml()); } }