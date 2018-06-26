<?php

namespace App\Http\Controllers\power;

use App\Model\PowerRole;
use App\Model\PowerUrl;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PowerController extends Controller
{
    //
    public function getPowerList()
    {
        $power_url = PowerUrl::get();
        $power_role = PowerRole::get();
        return respSuc(['power_url' => $power_url, 'power_role' => $power_role]);
    }

    public function setPower()
    {
        $pro = array(
            'id' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }

        PowerRole::where('id', $p['id'])->update(['power' => $p['power']]);
        return respSuc();
    }

    public function addPowerRole()
    {
        $pro = array(
            'name' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        $power_role = new PowerRole();
        $power_role->power = 0;
        $power_role->name = $p['name'];
        $power_role->created_at = now();
        $power_role->updated_at = now();
        $power_role->save();
        return respSuc($power_role);
    }

    public function delPowerRole()
    {
        $pro = array(
            'id' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        PowerRole::where('id', $p['id'])->delete();
        return respSuc();
    }

    public function addPowerUrl()
    {
        $pro = array(
            'name' => 'required',
            'url' => 'required'
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        $power_url = new PowerUrl();
        $mak_power_mark = $power_url->max('power_mark');
        $power_url->url = $p['url'];
        $power_url->name = $p['name'];
        $power_url->power_mark = $mak_power_mark * 2;
        $power_url->created_at = now();
        $power_url->updated_at = now();
        $power_url->save();
        return respSuc($power_url);
    }

    public function delPowerUrl()
    {
        $pro = array(
            'id' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        $power_url = PowerUrl::where('id', $p['id'])->first();
        if(!$power_url){
            return respErr(1005);
        }
        PowerRole::where('power',"&",$power_url->power_mark)->increment('power',-$power_url->power_mark);
        PowerUrl::where('id', $p['id'])->delete();
        return respSuc();
    }
    public function setWebUrlPower()
    {
        $pro = array(
            'id' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }

        PowerRole::where('id', $p['id'])->update(['web_url_power' => $p['power']]);
        return respSuc();
    }
}
