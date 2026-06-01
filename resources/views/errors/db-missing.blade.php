<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống đang bảo trì — NT Logistics</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1a237e 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1.5rem;
            padding: 3rem;
            max-width: 560px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        .icon-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(251, 191, 36, 0.15);
            border: 2px solid rgba(251, 191, 36, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.3); }
            50% { box-shadow: 0 0 0 12px rgba(251, 191, 36, 0); }
        }
        .error-code {
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            color: rgba(251, 191, 36, 0.8);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        h1 { color: #f1f5f9; font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem; }
        .desc { color: #94a3b8; font-size: 0.95rem; line-height: 1.7; margin-bottom: 2rem; }
        .info-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        .info-box .step {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
            color: #cbd5e1;
        }
        .info-box .step:last-child { margin-bottom: 0; }
        .info-box .step .num {
            min-width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #1a237e;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }
        .btn-restore {
            background: #1a237e;
            color: #fff;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        .btn-restore:hover {
            background: #283593;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(26,35,126,0.4);
        }
        .btn-retry {
            background: transparent;
            color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-retry:hover { color: #f1f5f9; border-color: rgba(255,255,255,0.3); }
        .brand { color: rgba(255,255,255,0.3); font-size: 0.75rem; margin-top: 2rem; }
        .brand i { color: #1a237e; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-wrap">
            <i class="fa fa-database fa-2x" style="color: #fbbf24;"></i>
        </div>

        <div class="error-code">
            <i class="fa fa-triangle-exclamation me-1"></i>
            Hệ thống đang bảo trì dữ liệu
        </div>

        <h1>Cơ sở dữ liệu chưa sẵn sàng</h1>

        <p class="desc">
            Dữ liệu hệ thống đang được khôi phục hoặc chưa được khởi tạo đầy đủ.
            Vui lòng thực hiện khôi phục dữ liệu từ trang Cài đặt.
        </p>

        <div class="info-box">
            <div class="step">
                <div class="num">1</div>
                <div>Vào <strong style="color:#f1f5f9">Cài đặt → Lưu trữ dữ liệu</strong></div>
            </div>
            <div class="step">
                <div class="num">2</div>
                <div>Chọn file sao lưu <strong style="color:#f1f5f9">.sql</strong> và nhập mật khẩu xác nhận</div>
            </div>
            <div class="step">
                <div class="num">3</div>
                <div>Nhấn <strong style="color:#f1f5f9">Khôi phục hệ thống</strong> và chờ hoàn tất</div>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="/settings#tab-luutru" class="btn-restore">
                <i class="fa fa-upload"></i>
                Đến trang Khôi phục
            </a>
            <a href="javascript:location.reload()" class="btn-retry">
                <i class="fa fa-rotate-right"></i>
                Thử lại
            </a>
        </div>

        <div class="brand">
            <i class="fa fa-truck-fast me-1"></i>NT LOGISTICS ERP
        </div>
    </div>
</body>
</html>
