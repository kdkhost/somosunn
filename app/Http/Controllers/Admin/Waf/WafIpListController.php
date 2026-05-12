<?php

namespace App\Http\Controllers\Admin\Waf;

use App\Http\Controllers\Controller;
use App\Models\Waf\WafIpAllowlistEntry;
use App\Models\Waf\WafIpBlocklistEntry;
use App\Services\Waf\IpListService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WafIpListController extends Controller
{
    public function blocklist(Request $request)
    {
        if (! Schema::hasTable('waf_ip_blocklist')) {
            return view('admin.waf.ip-lists.blocklist', ['entries' => collect(), 'hasTable' => false]);
        }

        $query = WafIpBlocklistEntry::query()->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('cidr', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('reason', 'like', '%' . $request->input('search') . '%');
        }

        $entries = $query->paginate(25)->withQueryString();

        return view('admin.waf.ip-lists.blocklist', [
            'entries'  => $entries,
            'hasTable' => true,
        ]);
    }

    public function allowlist(Request $request)
    {
        if (! Schema::hasTable('waf_ip_allowlist')) {
            return view('admin.waf.ip-lists.allowlist', ['entries' => collect(), 'hasTable' => false]);
        }

        $query = WafIpAllowlistEntry::query()->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('cidr', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('reason', 'like', '%' . $request->input('search') . '%');
        }

        $entries = $query->paginate(25)->withQueryString();

        return view('admin.waf.ip-lists.allowlist', [
            'entries'  => $entries,
            'hasTable' => true,
        ]);
    }

    public function storeBlock(Request $request)
    {
        if (! Schema::hasTable('waf_ip_blocklist')) {
            return back()->with('error', 'Tabela waf_ip_blocklist nao disponivel.');
        }

        $request->validate([
            'cidr'       => 'required|string|max:45',
            'reason'     => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $ipService = app(IpListService::class);
        $expiresAt = $request->filled('expires_at') ? Carbon::parse($request->input('expires_at')) : null;

        $entry = $ipService->block(
            $request->input('cidr'),
            $expiresAt,
            $request->input('reason'),
            auth()->id(),
            'manual'
        );

        if (! $entry) {
            return back()->with('error', 'IP/CIDR invalido.');
        }

        try {
            Log::channel('security')->info('WAF IP adicionado a blocklist', [
                'cidr'      => $request->input('cidr'),
                'reason'    => $request->input('reason'),
                'actor_id'  => auth()->id(),
                'ip'        => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'IP adicionado a blocklist.');
    }

    public function storeAllow(Request $request)
    {
        if (! Schema::hasTable('waf_ip_allowlist')) {
            return back()->with('error', 'Tabela waf_ip_allowlist nao disponivel.');
        }

        $request->validate([
            'cidr'       => 'required|string|max:45',
            'reason'     => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $ipService = app(IpListService::class);
        $expiresAt = $request->filled('expires_at') ? Carbon::parse($request->input('expires_at')) : null;

        $entry = $ipService->allow(
            $request->input('cidr'),
            $expiresAt,
            $request->input('reason'),
            auth()->id()
        );

        if (! $entry) {
            return back()->with('error', 'IP/CIDR invalido.');
        }

        try {
            Log::channel('security')->info('WAF IP adicionado a allowlist', [
                'cidr'      => $request->input('cidr'),
                'reason'    => $request->input('reason'),
                'actor_id'  => auth()->id(),
                'ip'        => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'IP adicionado a allowlist.');
    }

    public function destroyBlock(Request $request, $id)
    {
        if (! Schema::hasTable('waf_ip_blocklist')) {
            return back()->with('error', 'Tabela nao disponivel.');
        }

        $entry = WafIpBlocklistEntry::findOrFail($id);
        $cidr = $entry->cidr;
        $entry->delete();

        try {
            Log::channel('security')->info('WAF IP removido da blocklist', [
                'cidr'     => $cidr,
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', "IP {$cidr} removido da blocklist.");
    }

    public function destroyAllow(Request $request, $id)
    {
        if (! Schema::hasTable('waf_ip_allowlist')) {
            return back()->with('error', 'Tabela nao disponivel.');
        }

        $entry = WafIpAllowlistEntry::findOrFail($id);
        $cidr = $entry->cidr;
        $entry->delete();

        try {
            Log::channel('security')->info('WAF IP removido da allowlist', [
                'cidr'     => $cidr,
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', "IP {$cidr} removido da allowlist.");
    }
}
