<?php

namespace App\Http\Controllers;

use App\Models\HrMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HrMessageController extends Controller
{
    public function employeeChat()
    {
        $userId = Session::get('user_id');

        // When the employee opens the chat, mark any unread messages from HR as read
        HrMessage::where('employee_id', $userId)
            ->where('sender_id', '!=', $userId)
            ->update(['is_read' => 1]);

        // Fetch the entire conversation history
        $messages = HrMessage::with('sender')
            ->where('employee_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('employee.chat.index', compact('messages'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:5000', 'required_without_all:attachment,voice_message'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx'],
            'voice_message' => ['nullable', 'file', 'max:10240', 'mimetypes:audio/webm,video/webm,audio/ogg,audio/mp4,audio/mpeg,audio/wav'],
        ]);

        if ($request->hasFile('attachment') && $request->hasFile('voice_message')) {
            return back()
                ->withErrors(['attachment' => 'Send a file or a voice message, not both at once.'])
                ->withInput();
        }

        $userId = Session::get('user_id');
        $media = $this->storedHelpdeskMedia($request);

        HrMessage::create([
            'employee_id' => $userId,
            'sender_id' => $userId,
            'message' => $request->filled('message') ? $request->string('message')->toString() : null,
            'is_read' => 0,
            'attachment_path' => $media['path'],
            'attachment_type' => $media['type'],
        ]);

        return back();
    }

    /**
     * @return array{path: ?string, type: ?string}
     */
    private function storedHelpdeskMedia(Request $request): array
    {
        if ($request->hasFile('voice_message')) {
            return [
                'path' => $request->file('voice_message')->store('helpdesk/audio', 'public'),
                'type' => 'audio',
            ];
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            return [
                'path' => $file->store('helpdesk', 'public'),
                'type' => str_starts_with((string) $file->getMimeType(), 'image/') ? 'image' : 'document',
            ];
        }

        return ['path' => null, 'type' => null];
    }
    // ==========================================
    // HR OFFICER ROUTES (Role 2)
    // ==========================================

    public function hrInbox()
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // Get all unique employees who have sent or received a message
        $employeeIds = HrMessage::select('employee_id')->distinct()->pluck('employee_id');

        $conversations = User::whereIn('id', $employeeIds)->get();

        // Attach the latest message and unread count to each employee
        foreach ($conversations as $emp) {
            $emp->unread_count = HrMessage::where('employee_id', $emp->id)
                ->where('sender_id', $emp->id) // Only count messages sent BY the employee
                ->where('is_read', 0)
                ->count();

            $emp->latest_message = HrMessage::where('employee_id', $emp->id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // Sort so the newest conversations (or unread ones) bubble to the top
        $conversations = $conversations->sortByDesc(function ($emp) {
            return $emp->latest_message->created_at ?? now();
        });

        return view('hr.chat.index', compact('conversations'));
    }

    public function hrChat($employee_id)
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard');
        }

        $employee = User::findOrFail($employee_id);

        // Mark any messages sent by this employee as read since HR is viewing them now
        HrMessage::where('employee_id', $employee_id)
            ->where('sender_id', $employee_id)
            ->update(['is_read' => 1]);

        $messages = HrMessage::with('sender')
            ->where('employee_id', $employee_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('hr.chat.show', compact('employee', 'messages'));
    }

    public function hrSendMessage(Request $request, $employee_id)
    {
        if (Session::get('role_id') != 2) {
            return back();
        }

        $request->validate(['message' => 'required|string']);

        HrMessage::create([
            'employee_id' => $employee_id,          // The room still belongs to the employee
            'sender_id' => Session::get('user_id'), // The sender is the HR Officer
            'message' => $request->message,
            'is_read' => 0,                          // Unread for the employee to see later
        ]);

        return back();
    }
}
