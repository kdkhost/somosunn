{{-- Emoji Picker Component --}}
<div id="emoji-picker-{{ $pickerId ?? 'default' }}" class="emoji-picker-container hidden absolute bottom-full mb-2 left-0 bg-white rounded-lg shadow-xl border border-gray-200 p-3 w-72 z-50">
    <div class="flex items-center gap-2 text-xs mb-2">
        <button type="button" class="emoji-tab is-active px-2 py-1 rounded hover:bg-gray-100" data-category="faces">🙂</button>
        <button type="button" class="emoji-tab px-2 py-1 rounded hover:bg-gray-100" data-category="gestures">👍</button>
        <button type="button" class="emoji-tab px-2 py-1 rounded hover:bg-gray-100" data-category="objects">🎉</button>
        <button type="button" class="emoji-tab px-2 py-1 rounded hover:bg-gray-100" data-category="nature">🌿</button>
        <button type="button" class="emoji-tab px-2 py-1 rounded hover:bg-gray-100" data-category="symbols">❤️</button>
        <button type="button" class="emoji-tab px-2 py-1 rounded hover:bg-gray-100" data-category="all">⭐</button>
    </div>
    <div class="emoji-grid grid grid-cols-8 gap-1 text-lg text-center max-h-48 overflow-y-auto">
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😀">😀</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😁">😁</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😂">😂</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😅">😅</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="🤣">🤣</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😍">😍</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😘">😘</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😎">😎</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="🤩">🤩</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😇">😇</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="🙂">🙂</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😉">😉</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😮">😮</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😲">😲</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😢">😢</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😭">😭</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😡">😡</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="🤯">🤯</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="😴">😴</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="faces" data-emoji="🤗">🤗</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="👍">👍</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="👎">👎</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="👏">👏</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="🙌">🙌</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="🙏">🙏</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="💪">💪</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="🤝">🤝</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="👌">👌</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="✌️">✌️</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="gestures" data-emoji="🤟">🤟</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="🎉">🎉</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="🎯">🎯</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="🎁">🎁</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="🏆">🏆</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="📌">📌</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="🧠">🧠</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="💡">💡</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="📣">📣</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="🚀">🚀</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="objects" data-emoji="💼">💼</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="nature" data-emoji="🌿">🌿</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="nature" data-emoji="🌞">🌞</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="nature" data-emoji="🌈">🌈</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="nature" data-emoji="🌊">🌊</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="nature" data-emoji="🍀">🍀</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="nature" data-emoji="🌻">🌻</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="nature" data-emoji="🔥">🔥</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="nature" data-emoji="✨">✨</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="symbols" data-emoji="❤️">❤️</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="symbols" data-emoji="💙">💙</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="symbols" data-emoji="💚">💚</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="symbols" data-emoji="💛">💛</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="symbols" data-emoji="🧡">🧡</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="symbols" data-emoji="💜">💜</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="symbols" data-emoji="⭐">⭐</button>
        <button type="button" class="emoji-item hover:bg-gray-100 rounded p-1" data-category="symbols" data-emoji="✅">✅</button>
    </div>
</div>
