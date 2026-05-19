<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;


// ─── QR CODE CONTROLLER ──────────────────────────────────────────────────────
class QrCodeController extends Controller
{
    public function generateLocal($etablissementId)
    {
        $etablissement = DB::table('etablissements')->find($etablissementId);
        $qrData        = json_encode(['etablissement_id' => $etablissementId, 'type' => 'local_stockage']);
        return view('qrcode.local', compact('etablissement', 'qrData'));
    }

    public function scan($qr)
    {
        $decoded = base64_decode($qr, true);
        if ($decoded === false) {
            return redirect('/dashboard')->with('error', __('common.qr_invalid'));
        }

        $data = json_decode($decoded, true);
        if (!is_array($data)) {
            return redirect('/dashboard')->with('error', __('common.qr_invalid'));
        }

        // Only allow expected keys and validate they are integers
        $allowed = ['declaration_id', 'etablissement_id'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        foreach ($filtered as $key => $value) {
            if (!is_numeric($value) || intval($value) != $value || intval($value) < 1) {
                return redirect('/dashboard')->with('error', __('common.qr_invalid'));
            }
            $filtered[$key] = (int) $value;
        }

        if (empty($filtered)) {
            return redirect('/dashboard')->with('error', __('common.qr_invalid'));
        }

        return redirect()->route('collectes.create')->with('qr_data', $filtered);
    }
}
