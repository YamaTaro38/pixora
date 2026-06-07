{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pixora Studio - Foto Terbaik untuk Momen Terbaik Anda')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Glassmorphism Effects */
        .glass {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .glass-dark {
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Stats Card - Improved Contrast */
        .stats-card {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            background: rgba(0, 0, 0, 0.75);
            transform: translateY(-5px);
        }

        /* Hero Gradient */
        .hero-gradient {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 50%, rgba(0, 0, 0, 0.2) 100%);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #e11d48;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Card Hover */
        .package-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        /* Floating Animation */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        /* Chatbot Styles */
        .chatbot-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
        }

        .chatbot-window {
            position: fixed;
            bottom: 100px;
            right: 24px;
            width: 380px;
            height: 560px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            display: none;
            flex-direction: column;
            z-index: 1001;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        @media (max-width: 640px) {
            .chatbot-window {
                width: calc(100% - 48px);
                right: 24px;
                left: 24px;
                height: 70vh;
                bottom: 80px;
            }
        }

        .chatbot-window.active {
            display: flex;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .message-bot {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 12px 16px;
            border-radius: 20px;
            border-top-left-radius: 4px;
            margin-bottom: 12px;
            max-width: 85%;
            font-size: 14px;
            line-height: 1.5;
            color: #1f2937;
        }

        .message-user {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 20px;
            border-top-right-radius: 4px;
            margin-bottom: 12px;
            margin-left: auto;
            max-width: 85%;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #e11d48;
            border-radius: 10px;
        }

        /* Message formatting */
        .message-bot p,
        .message-user p {
            margin: 0 0 8px 0;
        }

        .message-bot p:last-child,
        .message-user p:last-child {
            margin-bottom: 0;
        }

        .message-bot ul,
        .message-user ul {
            margin: 8px 0;
            padding-left: 20px;
        }

        .message-bot li,
        .message-user li {
            margin: 4px 0;
        }

        .message-bot strong,
        .message-bot b {
            color: #e11d48;
        }

        /* Animated Background */
        @keyframes floatBg {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -30px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        .bg-blob {
            animation: floatBg 20s ease-in-out infinite;
        }

        .bg-blob-delay {
            animation: floatBg 25s ease-in-out infinite reverse;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .animate-bounce {
            animation: bounce 0.5s ease, slideInRight 0.3s ease;
        }

        /* Time slot card hover effect */
        .time-slot-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .time-slot-card:hover:not(.cursor-not-allowed) {
            transform: translateY(-2px);
        }

        /* Radio button custom style */
        input[type="radio"] {
            accent-color: #e11d48;
        }

        /* Custom Lightbox Style */
        .lb-outerContainer {
            border-radius: 12px !important;
            background: rgba(0, 0, 0, 0.9) !important;
        }

        .lb-dataContainer {
            background: rgba(0, 0, 0, 0.8) !important;
            border-radius: 0 0 12px 12px !important;
        }

        .lb-caption {
            font-family: 'Inter', sans-serif !important;
            font-size: 14px !important;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 via-white to-gray-100">

    <!-- Animated Background Blobs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div
            class="absolute top-20 -left-20 w-96 h-96 bg-rose-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 bg-blob">
        </div>
        <div
            class="absolute bottom-20 -right-20 w-96 h-96 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 bg-blob-delay">
        </div>
        <div
            class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20">
        </div>
    </div>

    <!-- Navbar Glass -->
    <nav class="glass sticky top-0 z-50 border-b border-white/20">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="{{ route('home') }}"
                    class="text-2xl md:text-3xl font-display font-bold bg-gradient-to-r from-rose-500 to-pink-600 bg-clip-text text-transparent">
                    Pixora
                </a>

                <div class="hidden md:flex space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-rose-600 transition font-medium">
                        <i class="fas fa-home mr-1"></i> Beranda
                    </a>
                    <a href="{{ route('packages') }}" class="text-gray-700 hover:text-rose-600 transition font-medium">
                        <i class="fas fa-box mr-1"></i> Paket
                    </a>
                    <a href="{{ route('calendar') }}" class="text-gray-700 hover:text-rose-600 transition font-medium">
                        <i class="fas fa-calendar-alt mr-1"></i> Cek Jadwal
                    </a>
                </div>

                <div class="flex items-center gap-4">
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-rose-600 transition">
                                <i class="fas fa-tachometer-alt text-xl"></i>
                            </a>
                        @else
                            <a href="{{ route('profile') }}" class="text-gray-700 hover:text-rose-600 transition">
                                <i class="fas fa-user-circle text-2xl"></i>
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline" id="logout-form">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-rose-600 transition">
                                <i class="fas fa-sign-out-alt text-xl"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-rose-600 transition">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="{{ route('register') }}"
                            class="bg-rose-500 text-white px-4 py-2 rounded-full text-sm hover:bg-rose-600 transition">
                            <i class="fas fa-user-plus mr-1"></i> Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10">
        @yield('content')
    </main>

    <!-- Footer Improved Contrast -->
    <footer class="bg-gray-900 text-gray-300 py-12 mt-20 relative z-10">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-display font-bold text-white mb-4 editable-text" data-field="studio_name"
                        data-editable>{{ $studio_name ?? 'Pixora' }}</h3>
                    <p class="text-gray-400">Abadikan momen terbaikmu dengan hasil foto berkualitas tinggi.</p>
                    <div class="flex gap-4 mt-4">
                        <a href="https://instagram.com/{{ str_replace('@', '', $instagram ?? '@pixora.studio') }}"
                            class="text-gray-400 hover:text-rose-400 transition">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="https://facebook.com/{{ $facebook ?? 'pixora.studio' }}"
                            class="text-gray-400 hover:text-rose-400 transition">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="https://tiktok.com/@{{ str_replace('@', '', $tiktok ?? '@pixora') }}"
                            class="text-gray-400 hover:text-rose-400 transition">
                            <i class="fab fa-tiktok text-xl"></i>
                        </a>
                        <a href="https://youtube.com/{{ $youtube ?? 'pixora' }}"
                            class="text-gray-400 hover:text-rose-400 transition">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-4">Menu</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition">Beranda</a>
                        </li>
                        <li><a href="{{ route('packages') }}" class="text-gray-400 hover:text-white transition">Paket
                                Fotografi</a></li>
                        <li><a href="{{ route('calendar') }}" class="text-gray-400 hover:text-white transition">Cek
                                Jadwal</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-4">Kontak</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fab fa-whatsapp w-5 mr-2 text-green-400"></i>
                            <span class="editable-text" data-field="studio_phone"
                                data-editable>{{ $studio_phone ?? '0812-3456-7890' }}</span>
                        </li>
                        <li><i class="fas fa-envelope w-5 mr-2"></i>
                            <span class="editable-text" data-field="studio_email" data-editable>{{ $studio_email ??
                                'hello@pixora.com' }}</span>
                        </li>
                        <li><i class="fas fa-map-marker-alt w-5 mr-2 text-rose-400"></i>
                            <span class="editable-text" data-field="studio_address"
                                data-editable>{{ $studio_address ?? 'Jakarta, Indonesia' }}</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-4">Jam Operasional</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-clock w-5 mr-2"></i>
                            <span class="editable-text" data-field="open_time"
                                data-editable>{{ $open_time ?? '08:00' }}</span> -
                            <span class="editable-text" data-field="close_time"
                                data-editable>{{ $close_time ?? '20:00' }}</span> WIB
                        </li>
                        <li><i class="fas fa-calendar-day w-5 mr-2"></i> Senin - Sabtu</li>
                        <li><i class="fas fa-calendar-week w-5 mr-2"></i> Minggu: 09:00 - 17:00</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500">
                <span class="editable-text" data-field="footer_copyright"
                    data-editable>{{ $footer_copyright ?? '© 2024 Pixora Studio. All rights reserved.' }}</span>
            </div>
        </div>
    </footer>


    <!-- Chatbot Floating Button -->
    <div class="chatbot-btn">
        <button id="chatbotToggle"
            class="group bg-gradient-to-r from-rose-500 to-pink-600 text-white rounded-full w-14 h-14 shadow-2xl flex items-center justify-center transition-all hover:scale-110">
            <i class="fas fa-comment-dots text-2xl group-hover:rotate-12 transition"></i>
        </button>
    </div>

    <div id="chatbotWindow" class="chatbot-window">
        <div
            class="bg-gradient-to-r from-rose-500 to-pink-600 text-white p-4 rounded-t-2xl flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="fas fa-robot text-xl"></i>
                <span class="font-semibold">Asisten Pixora AI</span>
            </div>
            <button id="chatbotClose" class="text-white hover:opacity-80">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="chatMessages" class="chat-messages bg-gray-50/80">
            <div class="message-bot">
                <i class="fas fa-smile-wink mr-2 text-rose-500"></i>
                Halo! Saya asisten Pixora.<br>
                Ada yang bisa saya bantu?<br>
                <span class="text-xs text-gray-500 mt-1 block">Cek jadwal, rekomendasi paket, atau info lainnya</span>
            </div>
        </div>

        <div class="p-4 border-t bg-white/90 backdrop-blur rounded-b-2xl">
            <div class="flex gap-2">
                <input type="text" id="chatInput" placeholder="Ketik pertanyaan..."
                    class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                <button id="chatSend"
                    class="bg-gradient-to-r from-rose-500 to-pink-600 text-white px-5 rounded-xl hover:shadow-lg transition">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="flex flex-wrap gap-2 mt-3">
                <button
                    class="chat-suggestion text-xs bg-gray-100 hover:bg-rose-100 px-3 py-1.5 rounded-full transition">
                    <i class="fas fa-calendar-day mr-1"></i> Cek jadwal hari ini
                </button>
                <button
                    class="chat-suggestion text-xs bg-gray-100 hover:bg-rose-100 px-3 py-1.5 rounded-full transition">
                    <i class="fas fa-heart mr-1"></i> Rekomendasi prewedding
                </button>
                <button
                    class="chat-suggestion text-xs bg-gray-100 hover:bg-rose-100 px-3 py-1.5 rounded-full transition">
                    <i class="fas fa-tag mr-1"></i> Harga termurah
                </button>
                <button
                    class="chat-suggestion text-xs bg-gray-100 hover:bg-rose-100 px-3 py-1.5 rounded-full transition">
                    <i class="fas fa-map-pin mr-1"></i> Lokasi studio
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- SortableJS untuk drag & drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>

        document.getElementById('logout-form')?.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Yakin ingin logout?',
                text: 'Anda akan keluar dari akun Anda.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        AOS.init({
            duration: 800,
            once: true
        });

        // Mobile menu toggle
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function () {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });

        // Chatbot functionality
        const chatbotToggle = document.getElementById('chatbotToggle');
        const chatbotWindow = document.getElementById('chatbotWindow');
        const chatbotClose = document.getElementById('chatbotClose');
        const chatInput = document.getElementById('chatInput');
        const chatSend = document.getElementById('chatSend');
        const chatMessages = document.getElementById('chatMessages');

        chatbotToggle.addEventListener('click', () => {
            chatbotWindow.classList.toggle('active');
        });

        chatbotClose.addEventListener('click', () => {
            chatbotWindow.classList.remove('active');
        });

        function formatMessage(text) {
            // Convert markdown-like syntax to HTML
            let formatted = text
                // Bold
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/__(.*?)__/g, '<strong>$1</strong>')
                // Italic
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/_(.*?)_/g, '<em>$1</em>')
                // Line breaks
                .replace(/\n/g, '<br>')
                // Lists (simple)
                .replace(/^\- (.*?)$/gm, '<li>$1</li>')
                .replace(/^\• (.*?)$/gm, '<li>$1</li>')
                .replace(/^\d+\. (.*?)$/gm, '<li>$1</li>');

            // Wrap consecutive list items
            if (formatted.includes('<li>')) {
                formatted = formatted.replace(/(<li>.*?<\/li>)/gs, '<ul class="list-disc pl-4 my-2">$1</ul>');
            }

            return formatted;
        }

        function addMessage(message, isUser = false) {
            const div = document.createElement('div');
            div.className = isUser ? 'message-user' : 'message-bot';

            if (!isUser) {
                div.innerHTML = formatMessage(message);
            } else {
                div.innerHTML = message.replace(/\n/g, '<br>');
            }

            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message-bot';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = '<span class="loading-spinner"></span> Sedang mengetik...';
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTyping() {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();
        }

        async function sendMessage(message) {
            if (!message.trim()) return;

            addMessage(message, true);
            chatInput.value = '';
            showTyping();

            try {
                const response = await fetch('{{ route("ai.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();
                hideTyping();

                // Debug: lihat response di console
                console.log('AI Response:', data);

                let replyText = data.reply || 'Maaf, terjadi kesalahan. Silakan coba lagi.';

                // Bersihkan response dari karakter aneh
                replyText = replyText
                    .replace(/\\n/g, '\n')  // Handle escaped newlines
                    .replace(/\\"/g, '"')    // Handle escaped quotes
                    .replace(/\\'/g, "'")    // Handle escaped quotes
                    .replace(/[{}\[\]]/g, ''); // Hapus kurung kurawal

                addMessage(replyText, false);

            } catch (error) {
                console.error('Chat error:', error);
                hideTyping();
                addMessage('Maaf, terjadi kesalahan. Silakan coba lagi atau hubungi admin kami langsung melalui WhatsApp.', false);
            }
        }

        chatSend.addEventListener('click', () => sendMessage(chatInput.value));
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage(chatInput.value);
        });

        document.querySelectorAll('.chat-suggestion').forEach(btn => {
            btn.addEventListener('click', () => sendMessage(btn.textContent.trim()));
        });
    </script>

    @stack('scripts')
</body>

</html>