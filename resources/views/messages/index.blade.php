@extends('layouts.app')

@section('content')
@php
  $totalUnread = collect($users)->where('unread_count', '>', 0)->count();
@endphp
<div class="container">
  <h2 style="margin-bottom:4px;">Messages <span class="badge badge-new" style="font-size:0.7rem; margin-left:6px;">{{ $totalUnread }}</span></h2>
  <p style="color:var(--muted);margin-bottom:24px;">Chat with peers and requesters.</p>

  <div class="message-layout">
    {{-- Conversation List --}}
    <div class="conversation-sidebar">
      <h4 style="margin:0 0 8px; padding:0 14px; font-size:0.9rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Conversations</h4>
      <div style="margin:0 14px 12px 14px;">
        <input type="text" id="conv-search" placeholder="Search conversations…" style="width:100%;padding:8px;border:2px solid var(--border);border-radius:6px;font-size:0.9rem;">
      </div>
      @if (empty($users))
        <div class="empty-state" style="padding:32px 16px;">
          <div style="font-size:2.5rem; margin-bottom:12px;">💬</div>
          <h4 style="margin:0 0 6px; color:var(--text);">No conversations yet</h4>
          <p style="margin:0; color:var(--muted); font-size:0.9rem;">Start a conversation by messaging a matched peer.</p>
        </div>
      @else
        <div class="conversation-list">
          @foreach ($users as $c)
            @php
              $p = $c['user'];
              $isActive = $p->User_ID == $withId;
              $pic = $p->Profile_Picture ? asset('storage/'.$p->Profile_Picture) : asset('images/default-avatar.svg');
              $initial = strtoupper(substr($p->Full_Name ?? 'U', 0, 1));
              $unreadCount = $c['unread_count'];
              $isBlocked = \App\Models\Block::isBlocked(auth()->id(), $p->User_ID);
            @endphp
            <div class="conversation-item {{ $isActive ? 'active' : '' }}" style="display:flex; flex-wrap:nowrap; align-items:center; gap:12px;" onclick="window.location.href='{{ route('messages.index', ['with' => $p->User_ID]) }}';">
              <div class="convo-avatar-wrap" style="flex-shrink:0; width:44px;">
                <img src="{{ $pic }}" alt="{{ $p->Full_Name }}" class="convo-avatar-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="convo-avatar" style="display:none;">{{ $initial }}</div>
              </div>
              <div class="conversation-info" style="flex:1; min-width:0;">
                <div style="display:flex; flex-wrap:nowrap; align-items:center; justify-content:space-between; gap:8px;">
                  <div class="conversation-name" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; font-weight:600; font-size:0.95rem;">{{ $p->Full_Name }}</div>
                  @if ($unreadCount > 0)
                    <span class="badge badge-new" style="font-size:0.7rem;">{{ $unreadCount }}</span>
                  @endif
                  @if ($p->User_ID !== auth()->id())
                    <div class="conversation-menu" style="flex-shrink:0;">
                      <button type="button" class="conversation-menu-btn" onclick="event.stopPropagation(); toggleConvoMenu(this);" title="Options">
                        <span></span><span></span><span></span>
                      </button>
                      <div class="conversation-menu-dropdown">
                        <a href="{{ route('profile.show', ['id' => $p->User_ID]) }}" class="convo-menu-item">View profile</a>
                        @if ($isBlocked)
                          <form action="{{ route('messages.unblock', ['id' => $p->User_ID]) }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="convo-menu-item convo-menu-item-form" onclick="event.stopPropagation();" title="Unblock">Unblock</button>
                          </form>
                        @else
                          <form action="{{ route('messages.block', ['id' => $p->User_ID]) }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="convo-menu-item convo-menu-item-form" onclick="event.stopPropagation();" title="Block">Block</button>
                          </form>
                        @endif
                        <form action="{{ route('messages.mark-unread', ['id' => $p->User_ID]) }}" method="POST" style="margin:0;">
                          @csrf
                          <button type="submit" class="convo-menu-item convo-menu-item-form" onclick="event.stopPropagation();" title="Mark as Unread">Mark as Unread</button>
                        </form>
                        <button type="button" class="convo-menu-item convo-menu-item-form" onclick="event.stopPropagation(); reportUser({{ $p->User_ID }}, '{{ addslashes($p->Full_Name) }}');" title="Report">Report</button>
                      </div>
                    </div>
                  @endif
                </div>
                <div class="conversation-preview">Click to view conversation</div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Chat Panel --}}
    <div class="chat-container">
      @if (!$withId)
        <div class="empty-state" style="padding:60px 20px;">
          <div style="font-size:3rem; margin-bottom:16px;">💬</div>
          <h3 style="margin:0 0 8px;">Select a conversation</h3>
          <p style="margin:0;">Choose a conversation from the left to start chatting.</p>
        </div>
      @else
        @php
          $conv = collect($users)->first(function ($c) use ($withId) {
              return (int) $c['user']->User_ID === (int) $withId;
          });
          $partner = $conv ? $conv['user'] : null;
          $partnerName = $partner?->Full_Name ?? 'User';
          $partnerInitial = $partnerName ? strtoupper(substr($partnerName, 0, 1)) : 'U';
          $partnerPic = $partner && $partner->Profile_Picture ? asset('storage/'.$partner->Profile_Picture) : null;
        @endphp
        <div class="chat-header">
          <button class="chat-back-btn" onclick="closeConversation()" title="Back to conversations">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          </button>
          @if ($partnerPic)
            <img src="{{ $partnerPic }}" alt="{{ $partnerName }}" class="chat-avatar-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div class="chat-avatar" style="display:none;">{{ $partnerInitial }}</div>
          @else
            <div class="chat-avatar">{{ $partnerInitial }}</div>
          @endif
            <div style="flex:1;">
            <div class="chat-header-name">{{ $partnerName }}</div>
            <div class="chat-header-sub">Chat</div>
          </div>
        </div>

        <div id="chat-messages" class="chat-messages">
          @if ($messages->isEmpty())
            <div style="text-align:center;padding:40px 20px;color:var(--muted);">
              <div style="font-size:2rem;margin-bottom:12px;">⚡</div>
              <p>No messages yet. Start the conversation!</p>
            </div>
          @else
            @foreach ($messages as $m)
              @php
                $isSent = $m->Sender_ID == auth()->id();
                $sender = $isSent ? auth()->user() : $partner;
                $senderPic = $sender && $sender->Profile_Picture ? asset('storage/'.$sender->Profile_Picture) : null;
                $senderInitial = $sender ? strtoupper(substr($sender->Full_Name ?? 'U', 0, 1)) : 'U';
                $isRead = !$isSent && $m->read_at ? true : false;
              @endphp
              <div class="message-row {{ $isSent ? 'sent' : '' }}" data-message-id="{{ $m->Message_ID }}">
                @if (!$isSent)
                  @if ($senderPic)
                    <img src="{{ $senderPic }}" alt="{{ $sender->Full_Name ?? 'User' }}" class="message-avatar-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="message-avatar" style="display:none;">{{ $senderInitial }}</div>
                  @else
                    <div class="message-avatar">{{ $senderInitial }}</div>
                  @endif
                @endif
                <div class="message-bubble">
                  <div class="message-meta" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <span style="font-weight:600; font-size:0.85rem;">{{ $isSent ? 'You' : $partnerName }}</span>
                    <span class="message-timestamp">{{ \Carbon\Carbon::parse($m->Sent_At)->format('g:i A') }}</span>
                  </div>
                  <div class="message-text">{{ $m->Message_Text }}</div>
                  @if ($isSent)
                    <div class="message-status" style="font-size:0.7rem; text-align:right; margin-top:4px;">
                      <span class="read-receipt {{ $m->read_at ? 'read' : '' }}" title="{{ $m->read_at ? 'Seen' : 'Sent' }}">
                        {{ $m->read_at ? 'Read' : 'Sent' }}
                      </span>
                    </div>
                  @endif
                </div>
              </div>
            @endforeach
          @endif
        </div>

        <form id="message-form" method="POST" action="{{ route('messages.store') }}" class="chat-input">
          @csrf
          <div class="chat-input-form">
            <input type="text" name="message" id="message-input" required autofocus placeholder="Type a message…">
            <input type="hidden" name="to_id" value="{{ $withId }}">
            <button type="submit" id="send-message-btn" class="btn btn-primary">Send</button>
          </div>
        </form>
      @endif
    </div>
  </div>
</div>

<script>
function openConversation(userId, element) {
  window.location.href = '/messages?with=' + userId;
}

function closeConversation() {
  window.location.href = '/messages';
}

@if ($withId)
const lastMessageId = {{ $messages->last()?->Message_ID ?? 0 }};
const seenMessageIds = new Set();
const chatContainer = document.getElementById('chat-messages');
const withId = {{ $withId }};
const authId = {{ auth()->id() }};
const partnerName = @json($partnerName ?? 'User');

if (window.innerWidth <= 640 && withId) {
  document.querySelector('.message-layout').classList.add('has-conversation');
}

document.querySelectorAll('[data-message-id]').forEach(el => {
  seenMessageIds.add(parseInt(el.getAttribute('data-message-id')));
});

function scrollToBottom() {
  if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
}

function appendMessage(message) {
  if (!chatContainer) return;
  const isSent = message.Sender_ID == authId;
  const row = document.createElement('div');
  row.className = 'message-row ' + (isSent ? 'sent' : '');
  row.setAttribute('data-message-id', message.Message_ID);

  const time = new Date(message.Sent_At).toLocaleTimeString('en-US', {
    hour: 'numeric', minute: '2-digit', hour12: true
  });

  row.innerHTML =
    '<div class="message-bubble">' +
      '<div class="message-meta" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">' +
        '<span style="font-weight:600; font-size:0.85rem;">' + (isSent ? 'You' : partnerName) + '</span>' +
        '<span class="message-timestamp">' + time + '</span>' +
      '</div>' +
      '<div class="message-text">' + message.Message_Text + '</div>' +
      (isSent ? '<div class="message-status" style="font-size:0.7rem; text-align:right; margin-top:4px;"><span class="read-receipt ' + (message.read_at ? 'read' : '') + '" title="' + (message.read_at ? 'Read' : 'Sent') + '">' + (message.read_at ? 'Read' : 'Sent') + '</span></div>' : '') +
    '</div>';
  chatContainer.appendChild(row);
  seenMessageIds.add(parseInt(message.Message_ID));
  scrollToBottom();
}

function updateReadReceipt(messageId, isRead) {
  const row = document.querySelector('.message-row[data-message-id="' + messageId + '"]');
  if (!row) return;
  const existing = row.querySelector('.read-receipt');
  if (existing) {
    existing.className = 'read-receipt ' + (isRead ? 'read' : '');
    existing.title = isRead ? 'Read' : 'Sent';
    existing.textContent = isRead ? 'Read' : 'Sent';
  }
}

function pollMessages() {
  fetch('/messages?with=' + withId + '&since=' + (lastMessageId || 0) + '&ajax=1')
    .then(r => r.ok ? r.json() : Promise.reject('Network error'))
    .then(data => {
      if (data.messages && data.messages.length > 0) {
        data.messages.forEach(msg => {
          const id = parseInt(msg.Message_ID);
          if (!seenMessageIds.has(id)) {
            appendMessage(msg);
          } else if (msg.Sender_ID == authId && msg.read_at) {
            updateReadReceipt(id, true);
          }
        });
        lastMessageId = data.last_id;
      }
    })
    .catch(err => console.error('Poll error:', err));
}

function sendMessage(event) {
  event.preventDefault();
  const form = event.target;
  const input = document.getElementById('message-input');
  const btn = document.getElementById('send-message-btn');
  const message = input.value.trim();
  if (!message) return false;

  btn.disabled = true;
  btn.innerHTML = 'Sending…';

  const formData = new FormData(form);
  fetch(form.action, {
    method: 'POST',
    body: formData,
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
  .then(r => r.ok ? r.json() : Promise.reject('Send failed'))
  .then(data => {
    if (data.message) {
      const id = parseInt(data.message.Message_ID);
      if (!seenMessageIds.has(id)) {
        appendMessage(data.message);
      }
      input.value = '';
      input.focus();
    }
  })
  .catch(err => console.error('Send error:', err))
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = 'Send';
  });
  return false;
}

scrollToBottom();
setInterval(pollMessages, 3000);
document.getElementById('message-form').addEventListener('submit', sendMessage);

// Search conversations
const convSearch = document.getElementById('conv-search');
const convItems = document.querySelectorAll('.conversation-item');
if (convSearch && convItems.length) {
  convSearch.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    convItems.forEach(item => {
      const name = item.querySelector('.conversation-name')?.textContent.toLowerCase() || '';
      item.style.display = name.includes(term) ? 'flex' : 'none';
    });
  });
}
</script>
@endif

<script>
const convSearch = document.getElementById('conv-search');
const convItems = document.querySelectorAll('.conversation-item');
if (convSearch && convItems.length) {
  convSearch.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    convItems.forEach(item => {
      const name = item.querySelector('.conversation-name')?.textContent.toLowerCase() || '';
      item.style.display = name.includes(term) ? 'flex' : 'none';
    });
  });
}
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('message-form');
    var btn = document.getElementById('send-message-btn');
    if (!form || !btn) return;

    form.addEventListener('submit', function () {
      btn.disabled = true;
      btn.innerHTML = 'Sending…';
    });

    window.addEventListener('pageshow', function () {
      btn.disabled = false;
      btn.innerHTML = 'Send';
    });
  });
</script>

<script>
  function toggleConvoMenu(btn) {
    const menu = btn.nextElementSibling;
    const isOpen = menu.classList.contains('open');
    document.querySelectorAll('.conversation-menu-dropdown.open').forEach(m => m.classList.remove('open'));
    if (!isOpen) menu.classList.add('open');
  }

  function reportUser(userId, name) {
    const reason = prompt('Report "' + name + '"? Please describe the issue:');
    if (!reason || !reason.trim()) return;
    fetch('{{ route('reports.store') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: 'reported_user_id=' + encodeURIComponent(userId) + '&reason=' + encodeURIComponent(reason.trim()),
    })
    .then(r => r.ok ? r.json() : Promise.reject('Report failed'))
    .then(() => {
      alert('Report submitted.');
      location.reload();
    })
    .catch(err => {
      console.error('Report error:', err);
      alert('Failed to submit report.');
    });
  }

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.conversation-menu')) {
      document.querySelectorAll('.conversation-menu-dropdown.open').forEach(m => m.classList.remove('open'));
    }
  });
</script>
@endsection
