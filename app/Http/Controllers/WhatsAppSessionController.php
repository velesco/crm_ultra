<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppSession;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class WhatsAppSessionController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
        $this->middleware('auth');
        $this->middleware('can:manage-settings')->except(['index', 'show']);
    }

    /**
     * Display a listing of WhatsApp sessions
     */
    public function index()
    {
        $sessions = WhatsAppSession::withCount(['whatsappMessages'])
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add statistics for each session
        foreach ($sessions as $session) {
            $session->inbound_count = $session->whatsappMessages()->where('direction', 'inbound')->count();
            $session->outbound_count = $session->whatsappMessages()->where('direction', 'outbound')->count();
            $session->unread_count = $session->whatsappMessages()
                ->where('direction', 'inbound')
                ->whereNull('read_at')
                ->count();
            $session->last_message_at = $session->whatsappMessages()->latest()->first()?->created_at;
        }

        // Current active session status
        $currentSessionStatus = null;
        $activeSession = WhatsAppSession::where('is_active', true)->first();
        
        if ($activeSession) {
            try {
                $statusResult = $this->whatsappService->getSessionStatus();
                $currentSessionStatus = $statusResult['status'] ?? 'unknown';
            } catch (\Exception $e) {
                $currentSessionStatus = 'error';
            }
        }

        return view('whatsapp.sessions.index', compact('sessions', 'currentSessionStatus'));
    }

    /**
     * Show the form for creating a new WhatsApp session
     */
    public function create()
    {
        // Check if there's already an active session
        $activeSession = WhatsAppSession::where('is_active', true)->first();
        
        if ($activeSession) {
            return redirect()->route('whatsapp.sessions.index')
                ->withErrors(['error' => 'There is already an active WhatsApp session. Disconnect it first before creating a new one.']);
        }

        return view('whatsapp.sessions.create');
    }

    /**
     * Store a newly created WhatsApp session
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'server_url' => 'required|url',
            'webhook_url' => 'nullable|url',
            'api_key' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if there's already an active session
        $activeSession = WhatsAppSession::where('is_active', true)->first();
        if ($activeSession) {
            return back()->withErrors(['error' => 'There is already an active WhatsApp session. Disconnect it first.'])->withInput();
        }

        try {
            $session = WhatsAppSession::create([
                'name' => $request->name,
                'server_url' => rtrim($request->server_url, '/'),
                'webhook_url' => $request->webhook_url,
                'api_key' => $request->api_key,
                'description' => $request->description,
                'user_id' => Auth::id(),
                'status' => 'connecting',
                'is_active' => true,
            ]);

            // Initialize WhatsApp service with new session
            $this->whatsappService->setSession($session);
            
            // Test connection
            $connectionResult = $this->whatsappService->testConnection();
            
            if ($connectionResult['success']) {
                $session->update(['status' => 'connected']);
                
                return redirect()->route('whatsapp.sessions.show', $session)
                    ->with('success', 'WhatsApp session created and connected successfully. Scan the QR code to authenticate.');
            } else {
                $session->update(['status' => 'connection_failed']);
                
                return back()->withErrors(['connection' => 'Session created but connection failed: ' . $connectionResult['error']])
                    ->with('warning', 'WhatsApp session created but connection test failed. Please verify your server configuration.');
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create WhatsApp session: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified WhatsApp session
     */
    public function show(WhatsAppSession $whatsappSession)
    {
        $whatsappSession->loadCount(['whatsappMessages']);
        
        // Get session statistics
        $stats = [
            'total_messages' => $whatsappSession->whatsappMessages()->count(),
            'inbound_messages' => $whatsappSession->whatsappMessages()->where('direction', 'inbound')->count(),
            'outbound_messages' => $whatsappSession->whatsappMessages()->where('direction', 'outbound')->count(),
            'unread_messages' => $whatsappSession->whatsappMessages()
                ->where('direction', 'inbound')
                ->whereNull('read_at')
                ->count(),
            'unique_contacts' => $whatsappSession->whatsappMessages()->distinct('contact_id')->count(),
            'messages_today' => $whatsappSession->whatsappMessages()->whereDate('created_at', today())->count(),
            'messages_this_week' => $whatsappSession->whatsappMessages()
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'messages_this_month' => $whatsappSession->whatsappMessages()
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        // Get current session status
        $sessionStatus = 'unknown';
        $qrCode = null;
        
        if ($whatsappSession->is_active) {
            try {
                $this->whatsappService->setSession($whatsappSession);
                $statusResult = $this->whatsappService->getSessionStatus();
                $sessionStatus = $statusResult['status'] ?? 'unknown';
                
                // Get QR code if session is not authenticated
                if ($sessionStatus === 'disconnected' || $sessionStatus === 'connecting') {
                    $qrResult = $this->whatsappService->getQRCode();
                    if ($qrResult['success']) {
                        $qrCode = $qrResult['qr_code'];
                    }
                }
            } catch (\Exception $e) {
                $sessionStatus = 'error';
            }
        }

        // Recent messages
        $recentMessages = $whatsappSession->whatsappMessages()
            ->with(['contact'])
            ->latest()
            ->limit(10)
            ->get();

        return view('whatsapp.sessions.show', compact('whatsappSession', 'stats', 'sessionStatus', 'qrCode', 'recentMessages'));
    }

    /**
     * Show the form for editing the specified WhatsApp session
     */
    public function edit(WhatsAppSession $whatsappSession)
    {
        return view('whatsapp.sessions.edit', compact('whatsappSession'));
    }

    /**
     * Update the specified WhatsApp session
     */
    public function update(Request $request, WhatsAppSession $whatsappSession)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'server_url' => 'required|url',
            'webhook_url' => 'nullable|url',
            'api_key' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $whatsappSession->update([
                'name' => $request->name,
                'server_url' => rtrim($request->server_url, '/'),
                'webhook_url' => $request->webhook_url,
                'api_key' => $request->api_key,
                'description' => $request->description,
            ]);

            return redirect()->route('whatsapp.sessions.show', $whatsappSession)
                ->with('success', 'WhatsApp session updated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update WhatsApp session: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified WhatsApp session
     */
    public function destroy(WhatsAppSession $whatsappSession)
    {
        try {
            // Check if session has messages
            if ($whatsappSession->whatsappMessages()->count() > 0) {
                return back()->withErrors(['error' => 'Cannot delete WhatsApp session that has messages. Deactivate it instead.']);
            }

            // Disconnect if active
            if ($whatsappSession->is_active) {
                $this->whatsappService->setSession($whatsappSession);
                $this->whatsappService->disconnect();
            }

            $whatsappSession->delete();
            
            return redirect()->route('whatsapp.sessions.index')
                ->with('success', 'WhatsApp session deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete WhatsApp session: ' . $e->getMessage()]);
        }
    }

    /**
     * Activate a WhatsApp session
     */
    public function activate(WhatsAppSession $whatsappSession)
    {
        try {
            // Deactivate all other sessions
            WhatsAppSession::where('is_active', true)->update(['is_active' => false]);
            
            // Activate this session
            $whatsappSession->update([
                'is_active' => true,
                'status' => 'connecting'
            ]);

            // Initialize service with new session
            $this->whatsappService->setSession($whatsappSession);
            
            return redirect()->route('whatsapp.sessions.show', $whatsappSession)
                ->with('success', 'WhatsApp session activated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to activate WhatsApp session: ' . $e->getMessage()]);
        }
    }

    /**
     * Deactivate a WhatsApp session
     */
    public function deactivate(WhatsAppSession $whatsappSession)
    {
        try {
            // Disconnect session
            if ($whatsappSession->is_active) {
                $this->whatsappService->setSession($whatsappSession);
                $this->whatsappService->disconnect();
            }
            
            $whatsappSession->update([
                'is_active' => false,
                'status' => 'disconnected',
                'disconnected_at' => now(),
            ]);
            
            return redirect()->route('whatsapp.sessions.index')
                ->with('success', 'WhatsApp session deactivated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to deactivate WhatsApp session: ' . $e->getMessage()]);
        }
    }

    /**
     * Test WhatsApp session connection
     */
    public function testConnection(WhatsAppSession $whatsappSession)
    {
        try {
            $this->whatsappService->setSession($whatsappSession);
            $result = $this->whatsappService->testConnection();
            
            if ($result['success']) {
                $whatsappSession->update(['status' => 'connected']);
                return back()->with('success', 'Connection test successful. Session is working properly.');
            } else {
                $whatsappSession->update(['status' => 'connection_failed']);
                return back()->withErrors(['test' => 'Connection test failed: ' . $result['error']]);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['test' => 'Connection test failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Get fresh QR code for session
     */
    public function refreshQR(WhatsAppSession $whatsappSession)
    {
        try {
            $this->whatsappService->setSession($whatsappSession);
            $result = $this->whatsappService->getQRCode();
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'qr_code' => $result['qr_code'],
                    'message' => 'QR code refreshed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get QR code: ' . $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get session status via AJAX
     */
    public function getStatus(WhatsAppSession $whatsappSession)
    {
        try {
            $this->whatsappService->setSession($whatsappSession);
            $result = $this->whatsappService->getSessionStatus();
            
            // Update session status in database
            if (isset($result['status'])) {
                $whatsappSession->update(['status' => $result['status']]);
            }
            
            return response()->json([
                'success' => true,
                'status' => $result['status'] ?? 'unknown',
                'info' => $result['info'] ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restart WhatsApp session
     */
    public function restart(WhatsAppSession $whatsappSession)
    {
        try {
            $this->whatsappService->setSession($whatsappSession);
            
            // Disconnect first
            $this->whatsappService->disconnect();
            
            // Wait a moment
            sleep(2);
            
            // Reconnect
            $result = $this->whatsappService->testConnection();
            
            if ($result['success']) {
                $whatsappSession->update(['status' => 'connected']);
                return back()->with('success', 'WhatsApp session restarted successfully.');
            } else {
                $whatsappSession->update(['status' => 'connection_failed']);
                return back()->withErrors(['restart' => 'Session restart failed: ' . $result['error']]);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['restart' => 'Failed to restart session: ' . $e->getMessage()]);
        }
    }

    /**
     * Duplicate an existing WhatsApp session
     */
    public function duplicate(WhatsAppSession $whatsappSession)
    {
        try {
            $newSession = $whatsappSession->replicate();
            $newSession->name = $whatsappSession->name . ' (Copy)';
            $newSession->is_active = false;
            $newSession->status = 'disconnected';
            $newSession->user_id = Auth::id();
            $newSession->save();

            return redirect()->route('whatsapp.sessions.edit', $newSession)
                ->with('success', 'WhatsApp session duplicated successfully. Please review and activate.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to duplicate session: ' . $e->getMessage()]);
        }
    }
}
