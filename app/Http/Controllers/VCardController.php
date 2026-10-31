<?php

namespace App\Http\Controllers;

use App\Http\Requests\VCardContactRequest;
use App\Models\VCardContact;
use App\Services\QrCodeService;
use App\Services\VCardService;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VCardController extends Controller
{
    public function __construct(private VCardService $vCards, private QrCodeService $qrs) {}
    private function owned(int $id): VCardContact { return VCardContact::where('user_id', auth()->id())->findOrFail($id); }
    public function dashboard() { $query = VCardContact::where('user_id', auth()->id()); return view('vcards.dashboard', ['total' => $query->count(), 'active' => (clone $query)->where('status', true)->count(), 'recent' => $query->latest()->take(5)->get()]); }
    public function index(Request $request) { $q = VCardContact::where('user_id', auth()->id())->with('qrCode'); if ($search = $request->get('search')) $q->where(fn($x) => $x->where('first_name','like',"%$search%")->orWhere('last_name','like',"%$search%")->orWhere('company_name','like',"%$search%")->orWhere('mobile_number','like',"%$search%")->orWhere('primary_email','like',"%$search%")); if ($request->filled('status')) $q->where('status', $request->status); return view('vcards.index', ['contacts' => $q->latest()->paginate(15)->withQueryString()]); }
    public function generateUrlQr(Request $request) { $data = $request->validate(['url' => ['required', 'url', 'max:2048']]); $url = $data['url']; $qr = QrCode::create($url)->setSize(1000)->setMargin(4)->setErrorCorrectionLevel(ErrorCorrectionLevel::Medium); return $this->urlQrIndex($request, $url, base64_encode((new PngWriter())->write($qr)->getString())); }
    public function create() { return view('vcards.form', ['contact' => new VCardContact()]); }
    public function store(VCardContactRequest $request) { $contact = auth()->user()->vcardContacts()->create($this->data($request)); if ($request->boolean('generate_qr')) return $this->regenerate($contact->id); return redirect()->route('vcards.edit', $contact->id)->with('success', 'Contact saved.'); }
    public function edit(int $id) { return view('vcards.form', ['contact' => $this->owned($id)]); }
    public function update(VCardContactRequest $request, int $id) { $contact = $this->owned($id); $contact->update($this->data($request, $contact)); if ($request->boolean('generate_qr')) return $this->regenerate($contact->id); return redirect()->route('vcards.edit', $contact->id)->with('success', 'Contact updated.'); }
    public function show(int $id) { return view('vcards.show', ['contact' => $this->owned($id)->load('qrCode')]); }
    public function regenerate(int $id) { $contact = $this->owned($id); $this->qrs->generate($contact, $this->vCards->make($contact)); return redirect()->route('vcards.show', $contact->id)->with('success', 'QR code generated with vCard 3.0 contact data.'); }
    public function downloadQr(int $id, string $format) { $contact = $this->owned($id)->load('qrCode'); $path = $contact->qrCode ? $contact->qrCode->{$format.'_path'} : null; abort_unless(in_array($format, ['png','svg']) && $path, 404); if (str_starts_with($path, 'uploads/')) { $file = public_path($path); abort_unless(is_file($file), 404); return response()->download($file, 'vcard-'.$contact->id.'.'.$format); } abort_unless(Storage::disk('public')->exists($path), 404); return Storage::disk('public')->download($path, 'vcard-'.$contact->id.'.'.$format); }
    public function downloadVcf(int $id) { $contact = $this->owned($id); return response($this->vCards->make($contact), 200, ['Content-Type' => 'text/vcard; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="'.str($contact->full_name)->slug('-').'.vcf"']); }
    public function destroy(int $id) { $this->owned($id)->delete(); return redirect()->route('vcards.index')->with('success', 'Contact deleted.'); }
    private function urlQrIndex(Request $request, string $url, string $png): \Illuminate\View\View { $q = VCardContact::where('user_id', auth()->id())->with('qrCode'); return view('vcards.index', ['contacts' => $q->latest()->paginate(15)->withQueryString(), 'urlQr' => ['url' => $url, 'png' => $png]]); }
    private function data(VCardContactRequest $request, ?VCardContact $contact = null): array { $data = $request->safe()->except(['profile_photo','company_logo','generate_qr']); foreach (['profile_photo','company_logo'] as $field) if ($request->hasFile($field)) { if ($contact?->$field) Storage::disk('public')->delete($contact->$field); $data[$field] = $request->file($field)->store('vcards/assets', 'public'); } $data['status'] = $request->boolean('status', true); return $data; }
}
