<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogIT - IT Activity Logging System</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    
    <!-- Boxicons for modern icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Particles.js for Login Animation -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

    <!-- Theme Initialization to prevent FOUC -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>
<body>

@if(!request()->routeIs('home') && !request()->routeIs('login'))
<div class="app-container">
    
    <!-- Mobile Header -->
    <header class="mobile-header">
        <div style="font-weight: 800; color: var(--primary); font-size: 1.25rem;">
            LogIT
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="{{ route('dashboard') }}?status=unresolved" style="color: var(--text-main); font-size: 1.25rem; position: relative;">
                <i class='bx bx-bell'></i>
                <span id="notification-badge-mobile" class="badge red" style="position: absolute; top: -5px; right: -10px; font-size: 0.6rem; padding: 0.1rem 0.3rem; display: none;">0</span>
            </a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: var(--status-red); font-size: 1.25rem;">
                <i class='bx bx-log-out'></i>
            </a>
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class='bx bx-hive' style="color: var(--primary);"></i> LogIT
        </div>
        
        <div class="nav-menu">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" style="display: flex; justify-content: space-between; align-items: center;">
                <span><i class='bx bx-grid-alt'></i> Dashboard</span>
                <span id="notification-badge-sidebar" class="badge red" style="font-size: 0.6rem; padding: 0.1rem 0.3rem; display: none;">0</span>
            </a>
            @if (Auth::user() && Auth::user()->role === 'admin')
            <a href="{{ route('admin') }}" class="nav-item {{ request()->routeIs('admin') ? 'active' : '' }}">
                <i class='bx bx-slider-alt'></i> Admin Panel
            </a>
            @endif
        </div>
        
        <div class="user-profile">
            <div class="user-info">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->full_name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">
                        {{ Auth::user()->full_name ?? 'Admin User' }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">
                        {{ Auth::user()->role ?? 'Admin' }}
                    </div>
                </div>
            </div>
            
            <button id="theme-toggle" class="nav-item" style="background: transparent; border: none; width: 100%; text-align: left; cursor: pointer; color: var(--text-muted); font-size: 1rem;">
                <i class='bx bx-moon' id="theme-icon"></i> <span id="theme-text">Dark Mode</span>
            </button>

            {{-- We'll use a proper form for logout later, just a link for now to match UI --}}
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-item" style="color: var(--status-red); padding: 0.75rem 0;">
                <i class='bx bx-log-out'></i> Log out
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </nav>
    
    <main class="main-content">
        @yield('content')
    </main>
</div>
@else
    @yield('content')
@endif

    <!-- Application JS -->
    <script src="/assets/js/app.js"></script>
    
    <!-- Theme Toggle Logic -->
    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const themeText = document.getElementById('theme-text');
        const htmlElement = document.documentElement;

        if (themeToggle) {
            if (htmlElement.getAttribute('data-theme') === 'dark') {
                themeIcon.className = 'bx bx-sun';
                themeText.textContent = 'Light Mode';
            }

            themeToggle.addEventListener('click', () => {
                if (htmlElement.getAttribute('data-theme') === 'dark') {
                    htmlElement.removeAttribute('data-theme');
                    localStorage.setItem('theme', 'light');
                    themeIcon.className = 'bx bx-moon';
                    themeText.textContent = 'Dark Mode';
                } else {
                    htmlElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                    themeIcon.className = 'bx bx-sun';
                    themeText.textContent = 'Light Mode';
                }
            });
        }

        // Notification Polling
        function checkNotifications() {
            fetch('{{ route('api.notifications.unresolved') }}')
                .then(response => response.json())
                .then(data => {
                    const badgeSidebar = document.getElementById('notification-badge-sidebar');
                    const badgeMobile = document.getElementById('notification-badge-mobile');
                    if (data.count > 0) {
                        if (badgeSidebar) { badgeSidebar.textContent = data.count; badgeSidebar.style.display = 'inline-block'; }
                        if (badgeMobile) { badgeMobile.textContent = data.count; badgeMobile.style.display = 'inline-block'; }
                    } else {
                        if (badgeSidebar) badgeSidebar.style.display = 'none';
                        if (badgeMobile) badgeMobile.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        // Check immediately, then every 30 seconds
        @if(Auth::check())
            checkNotifications();
            setInterval(checkNotifications, 30000);
        @endif
    </script>
    @stack('scripts')
</body>
</html>
