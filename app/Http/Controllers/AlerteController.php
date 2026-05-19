<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Alerte;

class AlerteController extends Controller
{
    public function index()
    {
        // TenantScope filtre automatiquement selon le rôle
        $alertes = Alerte::with(['service','etablissement'])->orderByDesc('created_at')->paginate(10);
        return view('alertes.index', compact('alertes'));
    }

    public function marquerLu($id)
    {
        $alerte = Alerte::findOrFail($id);
        $alerte->update(['lu'=>true,'lu_par'=>Auth::id(),'lu_at'=>now()]);
        return back()->with('success', __('alertes.flash_marked_read'));
    }

    public function toutLire()
    {
        Alerte::where('lu', false)->update(['lu'=>true,'lu_par'=>Auth::id(),'lu_at'=>now()]);
        return back()->with('success', __('alertes.flash_all_marked_read'));
    }
}
