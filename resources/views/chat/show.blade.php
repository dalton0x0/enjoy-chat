@extends('layouts.app')

@section('title', 'Chat - ' . $conversationData['name'])

@section('content')
    <div class="container-fluid h-100">
        <div class="row h-100 chat-container">
            <!-- Sidebar - List of conversations -->
            <div class="col-md-4 col-lg-3 px-0 border-end bg-white d-none d-md-block">
                <div class="d-flex flex-column h-100">
                    <!-- Header -->
                    <div class="p-3 border-bottom">
                        <h5 class="mb-3"><i class="bi bi-chat-dots"></i> Messages</h5>

                        <!-- Search users button -->
                        <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#searchUsersModal">
                            <i class="bi bi-plus-circle"></i> Nouvelle conversation
                        </button>
                    </div>

                    <!-- Conversations list -->
                    <div class="overflow-auto flex-grow-1">
                        @foreach($conversations as $conv)
                            <a href="{{ route('chat.show', $conv['id']) }}"
                               class="text-decoration-none">
                                <div class="conversation-item p-3 border-bottom d-flex align-items-center {{ $conv['id'] == $conversationData['id'] ? 'active' : '' }}">
                                    <!-- Avatar -->
                                    <div class="position-relative me-3">
                                        <img src="{{ $conv['avatar'] }}"
                                             alt="{{ $conv['name'] }}"
                                             class="rounded-circle"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        @if($conv['is_online'])
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>
                                        @endif
                                    </div>

                                    <!-- Conversation info -->
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 text-truncate">{{ $conv['name'] }}</h6>
                                            <small class="text-muted">{{ $conv['last_message_time'] }}</small>
                                        </div>
                                        <p class="mb-0 text-muted text-truncate small">
                                            {{ $conv['last_message'] ?? 'Aucun message' }}
                                        </p>
                                    </div>

                                    <!-- Unread badge -->
                                    @if($conv['unread_count'] > 0 && $conv['id'] != $conversationData['id'])
                                        <span class="badge bg-primary rounded-pill ms-2">
                                        {{ $conv['unread_count'] }}
                                    </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="col-md-8 col-lg-9 d-flex flex-column px-0 bg-light">
                <!-- Chat Header -->
                <div class="bg-white border-bottom p-3 d-flex align-items-center">
                    <!-- Back button for mobile -->
                    <a href="{{ route('chat.index') }}" class="btn btn-link text-decoration-none d-md-none me-2">
                        <i class="bi bi-arrow-left"></i>
                    </a>

                    <div class="position-relative me-3">
                        <img src="{{ $conversationData['avatar'] }}"
                             alt="{{ $conversationData['name'] }}"
                             class="rounded-circle"
                             style="width: 45px; height: 45px; object-fit: cover;">
                        @if($conversationData['is_online'])
                            <span class="position-absolute bottom-0 end-0 online-indicator"></span>
                        @endif
                    </div>

                    <div>
                        <h6 class="mb-0">{{ $conversationData['name'] }}</h6>
                        <small class="text-muted">
                            @if($conversationData['is_online'])
                                <i class="bi bi-circle-fill text-success" style="font-size: 0.5rem;"></i> En ligne
                            @else
                                {{ $conversationData['status'] }}
                            @endif
                        </small>
                    </div>
                </div>

                <!-- Messages Container -->
                <div class="messages-container flex-grow-1 p-3 overflow-auto" id="messagesContainer">
                    <div id="messagesList">
                        @php
                            $currentDate = null;
                        @endphp

                        @foreach($messages as $message)
                            @if($currentDate !== $message['date'])
                                @php $currentDate = $message['date']; @endphp
                                <div class="text-center my-3">
                                    <span class="badge bg-secondary">{{ $message['date'] }}</span>
                                </div>
                            @endif

                            <div class="mb-3 d-flex {{ $message['is_mine'] ? 'justify-content-end' : 'justify-content-start' }}"
                                 data-message-id="{{ $message['id'] }}">
                                @if(!$message['is_mine'])
                                    <img src="{{ $message['user_avatar'] }}"
                                         alt="{{ $message['user_name'] }}"
                                         class="rounded-circle me-2"
                                         style="width: 35px; height: 35px; object-fit: cover;">
                                @endif

                                <div class="message-bubble {{ $message['is_mine'] ? 'bg-primary text-white' : 'bg-white' }} p-3 rounded shadow-sm"
                                     style="max-width: 70%;">
                                    @if(!$message['is_mine'])
                                        <small class="d-block mb-1 {{ $message['is_mine'] ? 'text-white-50' : 'text-muted' }}">
                                            {{ $message['user_name'] }}
                                        </small>
                                    @endif

                                    <p class="mb-1" style="white-space: pre-wrap; word-wrap: break-word;">{{ $message['body'] }}</p>

                                    <small class="d-block text-end {{ $message['is_mine'] ? 'text-white-50' : 'text-muted' }}">
                                        {{ $message['time'] }}
                                    </small>
                                </div>

                                @if($message['is_mine'])
                                    <img src="{{ $message['user_avatar'] }}"
                                         alt="{{ $message['user_name'] }}"
                                         class="rounded-circle ms-2"
                                         style="width: 35px; height: 35px; object-fit: cover;">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Message Input -->
                <div class="message-input-container bg-white p-3">
                    <form id="messageForm" class="d-flex align-items-end">
                        @csrf
                        <textarea class="form-control me-2"
                                  id="messageInput"
                                  name="body"
                                  rows="1"
                                  placeholder="Tapez votre message..."
                                  style="resize: none; max-height: 150px;"></textarea>

                        <button type="submit" class="btn btn-primary" id="sendButton">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>

                    <div id="errorMessage" class="alert alert-danger mt-2 d-none"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Users Modal -->
    <div class="modal fade" id="searchUsersModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-search"></i> Rechercher un utilisateur
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Search input -->
                    <div class="mb-3">
                        <input type="text"
                               class="form-control"
                               id="userSearch"
                               placeholder="Rechercher par nom ou email...">
                    </div>

                    <!-- Users list -->
                    <div id="usersList">
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-search"></i>
                            <p class="mb-0">Recherchez un utilisateur pour démarrer une conversation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .message-bubble {
            border-radius: 18px !important;
        }

        .messages-container::-webkit-scrollbar {
            width: 8px;
        }

        .messages-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .messages-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .messages-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageForm = document.getElementById('messageForm');
            const messageInput = document.getElementById('messageInput');
            const messagesList = document.getElementById('messagesList');
            const messagesContainer = document.getElementById('messagesContainer');
            const sendButton = document.getElementById('sendButton');
            const errorMessage = document.getElementById('errorMessage');
            const conversationId = {{ $conversationData['id'] }};

            let lastMessageId = {{ $messages->last()['id'] ?? 0 }};
            let isSubmitting = false;
            let currentDate = '{{ $messages->last()["date"] ?? "" }}';

            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 150) + 'px';
            });

            // Submit on Enter (Shift+Enter for new line)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    messageForm.dispatchEvent(new Event('submit'));
                }
            });

            // Scroll to bottom
            function scrollToBottom() {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Initial scroll
            scrollToBottom();

            // Add message to UI
            function addMessageToUI(message) {
                // Check if we need a date separator
                if (currentDate !== message.date) {
                    currentDate = message.date;
                    const dateSeparator = `
                <div class="text-center my-3">
                    <span class="badge bg-secondary">${message.date}</span>
                </div>
            `;
                    messagesList.insertAdjacentHTML('beforeend', dateSeparator);
                }

                const messageHTML = `
            <div class="mb-3 d-flex ${message.is_mine ? 'justify-content-end' : 'justify-content-start'}"
                 data-message-id="${message.id}">
                ${!message.is_mine ? `
                    <img src="${message.user_avatar}"
                         alt="${message.user_name}"
                         class="rounded-circle me-2"
                         style="width: 35px; height: 35px; object-fit: cover;">
                ` : ''}

                <div class="message-bubble ${message.is_mine ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm"
                     style="max-width: 70%;">
                    ${!message.is_mine ? `
                        <small class="d-block mb-1 ${message.is_mine ? 'text-white-50' : 'text-muted'}">
                            ${message.user_name}
                        </small>
                    ` : ''}

                    <p class="mb-1" style="white-space: pre-wrap; word-wrap: break-word;">${escapeHtml(message.body)}</p>

                    <small class="d-block text-end ${message.is_mine ? 'text-white-50' : 'text-muted'}">
                        ${message.time}
                    </small>
                </div>

                ${message.is_mine ? `
                    <img src="${message.user_avatar}"
                         alt="${message.user_name}"
                         class="rounded-circle ms-2"
                         style="width: 35px; height: 35px; object-fit: cover;">
                ` : ''}
            </div>
        `;

                messagesList.insertAdjacentHTML('beforeend', messageHTML);
                scrollToBottom();

                lastMessageId = message.id;
            }

            // Escape HTML to prevent XSS
            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, m => map[m]);
            }

            // Send message
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (isSubmitting) return;

                const body = messageInput.value.trim();

                if (!body) {
                    return;
                }

                isSubmitting = true;
                sendButton.disabled = true;
                errorMessage.classList.add('d-none');

                fetch(`/chat/${conversationId}/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ body }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            addMessageToUI(data.message);
                            messageInput.value = '';
                            messageInput.style.height = 'auto';
                        } else {
                            throw new Error(data.message || 'Erreur lors de l\'envoi du message');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        errorMessage.textContent = error.message || 'Erreur lors de l\'envoi du message';
                        errorMessage.classList.remove('d-none');
                    })
                    .finally(() => {
                        isSubmitting = false;
                        sendButton.disabled = false;
                        messageInput.focus();
                    });
            });

            // Poll for new messages every 3 seconds
            setInterval(() => {
                fetch(`/chat/${conversationId}/messages?last_message_id=${lastMessageId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.messages && data.messages.length > 0) {
                            data.messages.forEach(message => {
                                addMessageToUI(message);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error polling messages:', error);
                    });
            }, 3000);

            // Search users
            const searchInput = document.getElementById('userSearch');
            const usersList = document.getElementById('usersList');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);

                const search = this.value.trim();

                if (search.length === 0) {
                    usersList.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="bi bi-search"></i>
                    <p class="mb-0">Recherchez un utilisateur pour démarrer une conversation</p>
                </div>
            `;
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`{{ route('chat.users.search') }}?search=${encodeURIComponent(search)}`)
                        .then(response => response.json())
                        .then(users => {
                            if (users.length === 0) {
                                usersList.innerHTML = `
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-person-x"></i>
                                <p class="mb-0">Aucun utilisateur trouvé</p>
                            </div>
                        `;
                                return;
                            }

                            usersList.innerHTML = users.map(user => `
                        <form method="POST" action="{{ route('chat.start') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="${user.id}">
                            <button type="submit" class="w-100 border-0 bg-white text-start user-search-item p-3 border-bottom d-flex align-items-center">
                                <div class="position-relative me-3">
                                    <img src="${user.avatar}"
                                         alt="${user.name}"
                                         class="rounded-circle"
                                         style="width: 45px; height: 45px; object-fit: cover;">
                                    ${user.is_online ? '<span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>' : ''}
                                </div>
                                <div>
                                    <h6 class="mb-0">${user.name}</h6>
                                    <small class="text-muted">${user.email}</small>
                                </div>
                            </button>
                        </form>
                    `).join('');
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }, 300);
            });
        });
    </script>
@endpush
