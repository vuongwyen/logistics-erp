@extends('layouts.app')

@section('title', 'Danh sách Lô hàng')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Quản Lý Lô Hàng (Jobs)</h4>
    <button class="btn btn-success px-4 fw-bold"><i class="fa fa-plus me-2"></i> TẠO LÔ HÀNG MỚI</button>
</div>

<!-- Filters -->
<div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
    <form action="" method="GET">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <input type="text" class="form-control border-light" placeholder="Mã lô hàng, Bill no...">
            </div>
            <div class="col-md-3">
                <select class="form-select border-light">
                    <option>Trạng thái: Tất cả</option>
                    <option>Đang xử lý</option>
                    <option>Hoàn thành</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control border-light">
            </div>
            
            <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                <a href="#" class="btn btn-light px-4">Xóa lọc</a>
                <button type="submit" class="btn btn-navy px-4">Tìm kiếm</button>
            </div>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Mã Lô Hàng</th>
                    <th>Khách Hàng</th>
                    <th>Tuyến Đường</th>
                    <th>Trạng Thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ps-4 fw-bold">JOB-001</td>
                    <td>Samsung Vietnam</td>
                    <td>Cát Lái -> Bắc Ninh</td>
                    <td><span class="badge rounded-pill bg-success">Hoàn thành</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm text-primary me-2"><i class="fa fa-eye"></i></button>
                        <button class="btn btn-sm text-warning"><i class="fa fa-edit"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="ps-4 fw-bold">JOB-002</td>
                    <td>VinFast</td>
                    <td>Hải Phòng -> Hà Nội</td>
                    <td><span class="badge rounded-pill bg-warning text-dark">Đang xử lý</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm text-primary me-2"><i class="fa fa-eye"></i></button>
                        <button class="btn btn-sm text-warning"><i class="fa fa-edit"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="ps-4 fw-bold">JOB-003</td>
                    <td>LG Electronics</td>
                    <td>Cái Mép -> Bình Dương</td>
                    <td><span class="badge rounded-pill bg-success">Hoàn thành</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm text-primary me-2"><i class="fa fa-eye"></i></button>
                        <button class="btn btn-sm text-warning"><i class="fa fa-edit"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="p-4 border-top">
        <nav class="d-flex justify-content-between align-items-center">
            <span class="small text-muted">Hiển thị 3/120 đơn hàng</span>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Tiếp</a></li>
            </ul>
        </nav>
    </div>
</div>
@endsection
