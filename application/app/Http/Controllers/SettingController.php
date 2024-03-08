<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    /**
     * Show the form for editing the setting.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('setting.edit');
    }

    /**
     * Update the profile
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $rules = [];
        foreach (Setting::isChildren()->get() as $item) {
            $rules[$item->key] = $item->rule;
        }

        $request->validate($rules);

        foreach (Setting::all() as $setting) {
            if (
                ($setting->type == 'input' || $setting->type == 'textarea' || $setting->type == 'select')
                && $request->has($setting->key)
                && $setting->value != $request->get($setting->key)
            ) {
                $setting->update(['value' => $request->get($setting->key)]);
            }
        }
        return back()->withStatus(__('Setting successfully updated.'));
    }
}
