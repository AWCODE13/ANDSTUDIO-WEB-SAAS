<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Halaman pengaturan pengguna.
     */
    public function show()
    {
        $user = Auth::user();

        $countries = [
            'ID' => 'Indonesia',
            'MY' => 'Malaysia',
            'SG' => 'Singapura',
            'TH' => 'Thailand',
            'VN' => 'Vietnam',
            'PH' => 'Filipina',
            'BN' => 'Brunei Darussalam',
            'KH' => ' Kamboja',
            'LA' => 'Laos',
            'MM' => 'Myanmar',
            'CN' => 'China',
            'JP' => 'Jepang',
            'KR' => 'Korea Selatan',
            'KP' => 'Korea Utara',
            'TW' => 'Taiwan',
            'HK' => 'Hong Kong',
            'IN' => 'India',
            'PK' => 'Pakistan',
            'BD' => 'Bangladesh',
            'LK' => 'Sri Lanka',
            'NP' => 'Nepal',
            'BT' => 'Bhutan',
            'MV' => 'Maldiva',
            'AF' => 'Afghanistan',
            'KZ' => 'Kazakhstan',
            'UZ' => 'Uzbekistan',
            'TM' => 'Turkmenistan',
            'KG' => 'Kirgizstan',
            'TJ' => 'Tajikistan',
            'MN' => 'Mongolia',
            'IL' => 'Israel',
            'PS' => 'Palestina',
            'JO' => 'Yordania',
            'LB' => 'Lebanon',
            'SY' => 'Suriah',
            'IQ' => 'Irak',
            'IR' => 'Iran',
            'SA' => 'Arab Saudi',
            'AE' => 'Uni Emirat Arab',
            'YE' => 'Yaman',
            'OM' => 'Oman',
            'KW' => 'Kuwait',
            'QA' => 'Qatar',
            'BH' => 'Bahrain',
            'AM' => 'Armenia',
            'AZ' => 'Azerbaijan',
            'GE' => 'Georgia',
            'CY' => 'Siprus',
            'TR' => 'Turki',
        ];

        return view('settings.index', compact('user', 'countries'));
    }

    /**
     * Simpan preferensi tema, bahasa, dan negara.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'theme'   => 'required|in:dark,light',
            'locale'  => 'required|in:en,id',
            'country' => 'required|string|size:2',
        ]);

        $user->update($validated);

        return back()->with('success', __('settings.saved'));
    }

    /**
     * Simpan data rekening bank tenant untuk pengajuan penarikan saldo.
     */
    public function bank(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name'   => 'required|string|max:100',
        ]);

        $tenant->update($validated);

        return back()->with('success', 'Data rekening bank berhasil disimpan.');
    }
}
