<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - NT Logistics ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <style>
        :root {
            --navy-dark: {{ Auth::user()->theme_color ?? '#1a237e' }};
            --navy-light: {{ Auth::user()->theme_color ? Auth::user()->theme_color . 'cc' : '#283593' }}; /* Add some opacity for light variant */
        }
        @if(Auth::user()->is_dark_mode)
        body {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
        }
        .sidebar { 
            background-color: #1e293b !important; 
            border-right: 1px solid #334155 !important;
        }
        .top-navbar {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border-bottom: 1px solid #334155 !important;
            box-shadow: none !important;
        }
        .card, .kpi-card, .modal-content, .dropdown-menu, .list-group-item, .accordion-item {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
        }
        .modal-header, .modal-footer, .card-header, .card-footer {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        .icon-box { background-color: #334155 !important; color: #fff !important; }
        .dropdown-item { color: #cbd5e1 !important; }
        .dropdown-item:hover { background-color: #334155 !important; color: #fff !important; }
        
        .table { color: #f1f5f9 !important; border-color: #334155 !important; }
        .table thead th, .table-light, .table-light th, thead.bg-light th { background-color: #0f172a !important; color: #cbd5e1 !important; border-bottom: 2px solid #334155 !important; }
        .table td, .table th { color: #f1f5f9 !important; border-color: #334155 !important; }
        .table-hover tbody tr:hover { background-color: rgba(255,255,255,0.05) !important; color: #fff !important; }
        
        .form-control, .form-select, .input-group-text {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        .form-control::placeholder { color: #94a3b8 !important; }
        .form-control:focus, .form-select:focus {
            background-color: #0f172a !important;
            border-color: var(--navy-dark) !important;
            color: #fff !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        }

        label, .form-label, .form-text, .text-dark, .text-body, .text-black { color: #cbd5e1 !important; }
        code { color: #f9a8d4 !important; }
        .text-navy, .text-muted { color: #94a3b8 !important; }
        .text-navy.fw-bold, .fw-bold.text-navy { color: #f1f5f9 !important; }
        .bg-light, .bg-white, .btn-light {
            background-color: #334155 !important;
            color: #f1f5f9 !important;
            border-color: #475569 !important;
        }
        .badge.bg-light { color: #f1f5f9 !important; }
        .btn-outline-navy {
            color: #bfdbfe !important;
            border-color: #60a5fa !important;
        }
        
        .alert-success { background-color: #064e3b !important; color: #6ee7b7 !important; border: none !important; }
        .alert-danger { background-color: #7f1d1d !important; color: #fca5a5 !important; border: none !important; }
        
        .nav-link-custom { color: #94a3b8 !important; }
        .nav-link-custom:hover, .nav-link-custom.active { background: rgba(255,255,255,0.05) !important; color: #fff !important; }
        @endif
    </style>
</head>
<body class="{{ Auth::user()?->is_dark_mode ? 'theme-dark' : '' }}">
    <div id="main-app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fa fa-truck-fast me-2"></i> NT LOGISTICS
            </div>
            <div class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa fa-chart-line"></i> Tổng quan
                </a>
                
                @if(Auth::user()->hasRole(['ADMIN', 'SALES']))
                <a href="{{ route('customers.index') }}" class="nav-link-custom {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="fa fa-users"></i> Khách hàng
                </a>
                @endif

                @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH']))
                <a href="{{ route('locations.index') }}" class="nav-link-custom {{ request()->routeIs('locations.*') ? 'active' : '' }}">
                    <i class="fa fa-map-location-dot"></i> Địa điểm
                </a>
                <a href="{{ route('vehicles.index') }}" class="nav-link-custom {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                    <i class="fa fa-truck-moving"></i> Đội xe
                </a>
                <a href="{{ route('drivers.index') }}" class="nav-link-custom {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                    <i class="fa fa-id-card"></i> Tài xế
                </a>
                <a href="{{ route('field-staff.index') }}" class="nav-link-custom {{ request()->routeIs('field-staff.*') ? 'active' : '' }}">
                    <i class="fa fa-user-shield"></i> Nhân viên hiện trường
                </a>
                @endif

                @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT', 'SALES']))
                <a href="{{ route('service-prices.index') }}" class="nav-link-custom {{ request()->routeIs('service-prices.*') ? 'active' : '' }}">
                    <i class="fa fa-tags"></i> Biểu giá
                </a>
                @endif

                <a href="{{ route('shipping-jobs.index') }}" class="nav-link-custom {{ request()->routeIs('shipping-jobs.*') ? 'active' : '' }}">
                    <i class="fa fa-box"></i> Đơn hàng
                </a>

                @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT', 'SALES', 'DISPATCH', 'FIELD', 'DOCUMENT']))
                <a href="{{ route('documents.index') }}" class="nav-link-custom {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                    <i class="fa fa-folder-open"></i> Chứng từ
                </a>
                @endif

                @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH', 'ACCOUNTANT']))
                <a href="{{ route('dispatch-orders.index') }}" class="nav-link-custom {{ request()->routeIs('dispatch-orders.*') ? 'active' : '' }}">
                    <i class="fa fa-route"></i> Điều vận
                </a>
                @endif

                @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH', 'FIELD']))
                <a href="{{ route('field-assignments.index') }}" class="nav-link-custom {{ request()->routeIs('field-assignments.*') ? 'active' : '' }}">
                    <i class="fa fa-clipboard-check"></i> Phiếu hiện trường
                </a>
                @endif

                @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT', 'DISPATCH']))
                <a href="{{ route('trip-settlements.index') }}" class="nav-link-custom {{ request()->routeIs('trip-settlements.*') ? 'active' : '' }}">
                    <i class="fa fa-calculator"></i> Quyết toán chuyến
                </a>
                @endif

                @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']))
                <div class="small text-white text-uppercase fw-bold mt-4 mb-2 px-3" style="font-size: 0.65rem; letter-spacing: 1px;">Báo cáo & Thống kê</div>
                <a href="{{ route('reports.operational') }}" class="nav-link-custom {{ request()->routeIs('reports.operational') ? 'active' : '' }}">
                    <i class="fa fa-chart-line"></i> Vận hành
                </a>
                <a href="{{ route('reports.financial') }}" class="nav-link-custom {{ request()->routeIs('reports.financial') ? 'active' : '' }}">
                    <i class="fa fa-chart-pie"></i> Tài chính
                </a>
                @elseif(Auth::user()->hasRole(['DISPATCH']))
                <div class="small text-white text-uppercase fw-bold mt-4 mb-2 px-3" style="font-size: 0.65rem; letter-spacing: 1px;">Báo cáo & Thống kê</div>
                <a href="{{ route('reports.operational') }}" class="nav-link-custom {{ request()->routeIs('reports.operational') ? 'active' : '' }}">
                    <i class="fa fa-chart-line"></i> Vận hành
                </a>
                @endif

                @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH']))
                <div class="small text-white text-uppercase fw-bold mt-4 mb-2 px-3" style="font-size: 0.65rem; letter-spacing: 1px;">Hệ thống</div>

                <a href="{{ route('settings.company') }}" class="nav-link-custom {{ request()->routeIs('settings.company') ? 'active' : '' }}">
                    <i class="fa fa-building"></i> Công ty
                </a>
                <a href="{{ route('users.index') }}" class="nav-link-custom {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fa fa-users-cog"></i> Nhân sự
                </a>
                @endif
            </div>

            <div class="sidebar-footer">
                <a href="{{ route('profile.edit') }}" class="nav-link-custom {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fa fa-user-gear"></i> Cài đặt
                </a>
                <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-none">
                    @csrf
                </form>
                <a href="javascript:void(0)" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link-custom text-danger">
                    <i class="fa fa-right-from-bracket"></i> Đăng xuất
                </a>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <header class="top-navbar">
                <button class="btn d-lg-none" onclick="toggleSidebar()"><i class="fa fa-bars"></i></button>
                <div></div>
                <div class="d-flex align-items-center gap-3">
                    <!-- Notifications Dropdown -->
                    <div class="dropdown">
                        @php $unreadCount = Auth::user()->unreadNotifications->count(); @endphp
                        <a href="#" class="text-navy position-relative p-2" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell fs-5"></i>
                            @if($unreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem; padding: 0.35em 0.65em;">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 p-0 overflow-hidden" aria-labelledby="notifDropdown" style="width: 320px;">
                            <li class="bg-navy p-3 text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold">Thông báo</h6>
                                    @if($unreadCount > 0)
                                        <span class="small opacity-75">{{ $unreadCount }} mới</span>
                                    @endif
                                </div>
                            </li>
                            <li class="p-0 overflow-auto" style="max-height: 400px;">
                                @forelse(Auth::user()->notifications()->latest()->take(5)->get() as $notification)
                                    <a class="dropdown-item p-3 border-bottom d-flex gap-3 {{ $notification->read_at ? 'opacity-75' : 'bg-light' }}" href="{{ $notification->data['link'] ?? '#' }}">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                                            <i class="fa {{ $notification->data['icon'] ?? 'fa-bell' }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small">{{ $notification->data['title'] }}</div>
                                            <div class="text-muted small text-wrap" style="line-height: 1.2;">{{ $notification->data['message'] }}</div>
                                            <div class="text-muted mt-1" style="font-size: 0.6rem;">{{ $notification->created_at->diffForHumans() }}</div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-4 text-center text-muted small">
                                        <i class="fa fa-bell-slash d-block fs-2 mb-2 opacity-25"></i>
                                        Không có thông báo nào.
                                    </div>
                                @endforelse
                            </li>
                            <li class="p-2 text-center bg-light">
                                <a href="#" class="text-navy small fw-bold text-decoration-none">Đánh dấu đã đọc tất cả</a>
                            </li>
                        </ul>
                    </div>

                    <div class="user-profile d-flex align-items-center gap-3">
                        <div class="text-end d-none d-sm-block">
                            <div class="fw-bold small">{{ Auth::user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ Auth::user()->role->role_name ?? 'Nhân viên' }}</div>
                        </div>
                        <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fa fa-user"></i>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                        <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                        <i class="fa fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
    <script>
        // Global delete confirmation
        function handleDelete(id, message = 'Bạn có chắc chắn muốn xóa?') {
            Swal.fire({
                title: 'Xác nhận xóa',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1a237e',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById(id) || document.getElementById('delete-form-' + id);
                    if (form) {
                        form.submit();
                    } else {
                        console.error('Delete form not found for ID: ' + id);
                    }
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sửa lỗi không chọn được năm (focus trap của Bootstrap Modal)
            document.addEventListener('focusin', (e) => {
                if (e.target.closest('.flatpickr-calendar')) {
                    e.stopImmediatePropagation();
                }
            });

            // Khởi tạo Flatpickr cho tất cả các ô chọn ngày để đồng bộ định dạng dd/mm/yyyy
            flatpickr('input[type="date"]', {
                locale: "vn",
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                allowInput: true,
                disableMobile: true,
                placeholder: "dd/mm/yyyy"
            });
        });

        // Hàm hỗ trợ gán giá trị ngày an toàn khi có dùng Flatpickr
        window.setDateValue = function(id, value) {
            let el = document.getElementById(id);
            if (el) {
                if (el._flatpickr) {
                    el._flatpickr.setDate(value);
                } else {
                    el.value = value;
                }
            }
        };
    </script>
    @stack('scripts')
</body>
</html>
