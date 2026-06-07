{{-- resources/views/admin/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - Pixora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        /* Background Formal - Abu-abu gradient */
        .admin-bg {
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -2;
        }
        
        /* Blobs lembut */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            z-index: -1;
        }
        .blob-1 { width: 500px; height: 500px; background: #94a3b8; top: -150px; right: -150px; }
        .blob-2 { width: 600px; height: 600px; background: #64748b; bottom: -200px; left: -200px; }
        
        /* Glassmorphism Sidebar */
        .glass-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-sidebar.collapsed { width: 80px; }
        .glass-sidebar.collapsed .sidebar-text,
        .glass-sidebar.collapsed .logo-text,
        .glass-sidebar.collapsed .copyright { display: none; }
        .glass-sidebar.collapsed .sidebar-item { justify-content: center; padding: 12px; }
        
        /* Main Content */
        .main-content { transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .main-content.expanded { margin-left: 80px; }
        .main-content.normal { margin-left: 280px; }
        
        /* Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        /* Sidebar Items */
        .sidebar-item {
            transition: all 0.3s ease;
            border-radius: 12px;
            margin: 4px 12px;
            font-size: 13px;
            font-weight: 500;
        }
        .sidebar-item:hover {
            background: rgba(225, 29, 72, 0.1);
            transform: translateX(4px);
        }
        .sidebar-item.active {
            background: linear-gradient(135deg, #e11d48, #be123c);
            color: white;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.2);
        }
        
        /* Table Styles */
        .data-table th {
            background: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table td { font-size: 13px; padding: 12px 16px; }
        .data-table tr:hover { background: #f1f5f9; }
        
        /* Button */
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #e11d48; border-radius: 10px; }
        
        /* Font size adjustments */
        body { font-size: 13px; }
        h1, .text-xl { font-size: 1.25rem !important; }
        h2, .text-lg { font-size: 1.125rem !important; }
        .text-sm { font-size: 0.75rem !important; }
        .text-xs { font-size: 0.7rem !important; }
    </style>
</head>
<body>
    <div class="admin-bg"></div>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    
    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="glass-sidebar w-72 fixed h-full z-20 overflow-y-auto">
            <div class="p-5 mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-rose-500 to-pink-600 rounded-lg flex items-center justify-center shadow-md logo-icon">
                        <i class="fas fa-camera text-white text-sm"></i>
                    </div>
                    <div class="logo-text">
                        <h1 class="text-lg font-bold bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent">Pixora</h1>
                        <p class="text-[10px] text-gray-500">Admin Panel</p>
                    </div>
                </div>
                <button id="toggleSidebar" class="toggle-btn w-7 h-7 rounded-lg flex items-center justify-center text-gray-500 hover:text-rose-500">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
            </div>
            
            <nav class="px-2 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-gray-700 transition-all {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-4 text-sm"></i>
                    <span class="sidebar-text text-sm">Dashboard</span>
                </a>
                <a href="{{ route('admin.bookings.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-gray-700 transition-all {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-4 text-sm"></i>
                    <span class="sidebar-text text-sm">Bookings</span>
                </a>
                <a href="{{ route('admin.packages.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-gray-700 transition-all {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <i class="fas fa-box w-4 text-sm"></i>
                    <span class="sidebar-text text-sm">Paket</span>
                </a>
                <a href="{{ route('admin.customers.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-gray-700 transition-all {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-4 text-sm"></i>
                    <span class="sidebar-text text-sm">Customers</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-gray-700 transition-all {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-sliders-h w-4 text-sm"></i>
                    <span class="sidebar-text text-sm">Pengaturan</span>
                </a>
                <a href="{{ route('admin.landing-page.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-gray-700 transition-all {{ request()->routeIs('admin.landing-page.*') ? 'active' : '' }}">
                    <i class="fas fa-palette w-4 text-sm"></i>
                    <span class="sidebar-text text-sm">Landing Page</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-gray-700 transition-all {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-4 text-sm"></i>
                    <span class="sidebar-text text-sm">Laporan</span>
                </a>
            </nav>
            
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/30">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-item flex items-center gap-3 w-full px-3 py-2.5 text-gray-700 hover:bg-red-50 transition-all rounded-xl text-sm">
                        <i class="fas fa-sign-out-alt w-4 text-sm"></i>
                        <span class="sidebar-text">Logout</span>
                    </button>
                </form>
                <div class="mt-3 text-center copyright">
                    <p class="text-[10px] text-gray-400">© {{ date('Y') }} Pixora Studio</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div id="mainContent" class="main-content normal flex-1 relative z-10">
            <div class="glass-header px-6 py-3 flex justify-between items-center sticky top-0 z-10">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">@yield('title')</h2>
                    <p class="text-xs text-gray-500 mt-0.5">@yield('subtitle', 'Kelola data studio Anda')</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-xs font-medium text-gray-700">{{ Auth::user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] text-gray-400">Administrator</p>
                    </div>
                    <div class="w-8 h-8 bg-gradient-to-r from-rose-500 to-pink-600 rounded-full flex items-center justify-center text-white font-bold shadow-md text-sm">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                </div>
            </div>
            
            <div class="px-6 pt-4">
                @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
                @endif
            </div>
            
            <div class="p-6">
                @yield('content')
            </div>
        </div>
    </div>
    
    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        function toggleSidebar() {
            isCollapsed = !isCollapsed;
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.remove('normal');
                mainContent.classList.add('expanded');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-right text-xs"></i>';
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                mainContent.classList.add('normal');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-left text-xs"></i>';
            }
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
        
        toggleBtn.addEventListener('click', toggleSidebar);
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.remove('normal');
            mainContent.classList.add('expanded');
            toggleBtn.innerHTML = '<i class="fas fa-chevron-right text-xs"></i>';
        }
    </script>
    @stack('scripts')
</body>
</html>