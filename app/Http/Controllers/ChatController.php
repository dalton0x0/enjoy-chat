<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartConversationRequest;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Display the list of conversations.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $conversations = Auth::user()
            ->conversations()
            ->with(['users', 'latestMessage.user'])
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'name' => $conversation->getDisplayName(Auth::id()),
                    'avatar' => $conversation->isPrivate()
                        ? $conversation->getOtherUser(Auth::id())?->avatar_url
                        : null,
                    'last_message' => $conversation->latestMessage?->body,
                    'last_message_time' => $conversation->latestMessage?->created_at?->diffForHumans(),
                    'unread_count' => $conversation->getUnreadCount(Auth::id()),
                    'is_online' => $conversation->isPrivate()
                        ? $conversation->getOtherUser(Auth::id())?->isOnline()
                        : false,
                ];
            });

        return view('chat.index', compact('conversations'));
    }

    /**
     * Display a specific conversation.
     *
     * @param \App\Models\Conversation $conversation
     * @return \Illuminate\View\View
     */
    public function show(Conversation $conversation)
    {
        // Check if user is part of the conversation
        if (!$conversation->users->contains(Auth::id())) {
            abort(403, 'Vous n\'avez pas accès à cette conversation.');
        }

        // Mark conversation as read
        $conversation->markAsRead(Auth::id());

        // Load messages with user
        $messages = $conversation->messages()
            ->with('user')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'user_id' => $message->user_id,
                    'user_name' => $message->user->name,
                    'user_avatar' => $message->user->avatar_url,
                    'is_mine' => $message->user_id === Auth::id(),
                    'time' => $message->formatted_time,
                    'date' => $message->formatted_date,
                    'created_at' => $message->created_at->format('c'), // Format ISO 8601
                ];
            });

        // Get all conversations for sidebar
        $conversations = Auth::user()
            ->conversations()
            ->with(['users', 'latestMessage.user'])
            ->get()
            ->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'type' => $conv->type,
                    'name' => $conv->getDisplayName(Auth::id()),
                    'avatar' => $conv->isPrivate()
                        ? $conv->getOtherUser(Auth::id())?->avatar_url
                        : null,
                    'last_message' => $conv->latestMessage?->body,
                    'last_message_time' => $conv->latestMessage?->created_at?->diffForHumans(),
                    'unread_count' => $conv->getUnreadCount(Auth::id()),
                    'is_online' => $conv->isPrivate()
                        ? $conv->getOtherUser(Auth::id())?->isOnline()
                        : false,
                ];
            });

        // Get fresh user data for conversation
        $otherUser = $conversation->getOtherUser(Auth::id());
        if ($otherUser) {
            $otherUser->refresh(); // Refresh to get latest is_online status
        }

        // Conversation details
        $conversationData = [
            'id' => $conversation->id,
            'name' => $conversation->getDisplayName(Auth::id()),
            'avatar' => $conversation->isPrivate()
                ? $otherUser?->avatar_url
                : null,
            'is_online' => $conversation->isPrivate()
                ? $otherUser?->isOnline()
                : false,
            'status' => $conversation->isPrivate()
                ? $otherUser?->status
                : null,
        ];

        return view('chat.show', compact('conversations', 'conversationData', 'messages'));
    }

    /**
     * Search for users to start a conversation.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        $search = $request->input('search', '');

        $users = User::where('id', '!=', Auth::id())
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar_url,
                    'is_online' => $user->isOnline(),
                    'status' => $user->status,
                ];
            });

        return response()->json($users);
    }

    /**
     * Start a new conversation with a user.
     *
     * @param \App\Http\Requests\Chat\StartConversationRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startConversation(StartConversationRequest $request)
    {
        $otherUserId = $request->user_id;

        $existingConversation = Auth::user()
            ->conversations()
            ->where('type', 'private')
            ->whereHas('users', function ($query) use ($otherUserId) {
                $query->where('users.id', $otherUserId);
            })
            ->first();

        if ($existingConversation) {
            return redirect()
                ->route('chat.show', $existingConversation)
                ->with('info', 'Conversation existante.');
        }

        $conversation = DB::transaction(function () use ($otherUserId) {
            $conversation = Conversation::create([
                'type' => 'private',
                'created_by' => Auth::id(),
            ]);

            $conversation->users()->attach([Auth::id(), $otherUserId]);

            return $conversation;
        });

        return redirect()
            ->route('chat.show', $conversation)
            ->with('success', 'Nouvelle conversation démarrée !');
    }

    /**
     * Send a message in a conversation.
     *
     * @param \App\Http\Requests\Chat\SendMessageRequest $request
     * @param \App\Models\Conversation $conversation
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(SendMessageRequest $request, Conversation $conversation)
    {
        // Create the message
        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
            'type' => 'text',
        ]);

        // Update conversation's updated_at to move it to top of list
        $conversation->touch();

        // Load user relationship
        $message->load('user');

        // Return formatted message
        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'user_id' => $message->user_id,
                'user_name' => $message->user->name,
                'user_avatar' => $message->user->avatar_url,
                'is_mine' => true,
                'time' => $message->formatted_time,
                'date' => $message->formatted_date,
                'created_at' => $message->created_at->format('c'), // Format ISO 8601
            ],
        ]);
    }

    /**
     * Get new messages for a conversation (polling).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Conversation $conversation
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages(Request $request, Conversation $conversation)
    {
        // Check if user is part of the conversation
        if (!$conversation->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lastMessageId = $request->input('last_message_id', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $lastMessageId)
            ->with('user')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'user_id' => $message->user_id,
                    'user_name' => $message->user->name,
                    'user_avatar' => $message->user->avatar_url,
                    'is_mine' => $message->user_id === Auth::id(),
                    'time' => $message->formatted_time,
                    'date' => $message->formatted_date,
                    'created_at' => $message->created_at->format('c'),
                ];
            });

        return response()->json([
            'messages' => $messages,
        ]);
    }
}
