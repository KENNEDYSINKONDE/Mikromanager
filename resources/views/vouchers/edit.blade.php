@extends('layouts.app')

@section('content')

<div class="pagetitle">
    <h1>Edit Voucher</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Vouchers</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vouchers.show', $voucher) }}">{{ $voucher->username }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">

        {{-- ── Form ── --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center gap-2 py-3">
                    <i class="bi bi-pencil-square text-warning fs-5"></i>
                    <h5 class="mb-0">Edit — <span class="text-primary font-monospace">{{ $voucher->username }}</span></h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('vouchers.update', $voucher) }}" method="POST">
                        @csrf @method('PUT')

                        {{-- Username (read-only) --}}
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" class="form-control bg-light" value="{{ $voucher->username }}" disabled>
                                <div class="form-text"><i class="bi bi-lock me-1"></i>Cannot be changed.</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="text" class="form-control bg-light" value="{{ $voucher->password }}" disabled>
                                <div class="form-text"><i class="bi bi-lock me-1"></i>Cannot be changed.</div>
                            </div>
                        </div>

                        {{-- Profile & Status --}}
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Profile <span class="text-danger">*</span></label>
                                <select name="profile" class="form-select @error('profile') is-invalid @enderror" required>
                                    @foreach($profiles as $p)
                                        <option value="{{ $p }}" @selected(old('profile', $voucher->profile) == $p)>{{ $p }}</option>
                                    @endforeach
                                </select>
                                @error('profile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active"   @selected(old('status', $voucher->status) == 'active')>Active</option>
                                    <option value="used"     @selected(old('status', $voucher->status) == 'used')>Used</option>
                                    <option value="expired"  @selected(old('status', $voucher->status) == 'expired')>Expired</option>
                                    <option value="disabled" @selected(old('status', $voucher->status) == 'disabled')>Disabled</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Limits --}}
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Time Limit (seconds)</label>
                                <input type="number" name="time_limit"
                                       class="form-control @error('time_limit') is-invalid @enderror"
                                       value="{{ old('time_limit', $voucher->time_limit) }}"
                                       placeholder="blank = unlimited">
                                <div class="form-text">Current: {{ $voucher->time_limit_formatted }}</div>
                                @error('time_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Data Limit (bytes)</label>
                                <input type="number" name="data_limit"
                                       class="form-control @error('data_limit') is-invalid @enderror"
                                       value="{{ old('data_limit', $voucher->data_limit) }}"
                                       placeholder="blank = unlimited">
                                <div class="form-text">Current: {{ $voucher->data_limit_formatted }}</div>
                                @error('data_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Price, Batch, Expires --}}
                        <div class="row g-3 mb-3">
                            <div class="col-sm-4">
                                <label class="form-label fw-semibold">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="price" step="0.01"
                                           class="form-control @error('price') is-invalid @enderror"
                                           value="{{ old('price', $voucher->price) }}" placeholder="0.00">
                                </div>
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label fw-semibold">Batch</label>
                                <input type="text" name="batch"
                                       class="form-control @error('batch') is-invalid @enderror"
                                       value="{{ old('batch', $voucher->batch) }}">
                                @error('batch') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label fw-semibold">Expires At</label>
                                <input type="datetime-local" name="expires_at"
                                       class="form-control @error('expires_at') is-invalid @enderror"
                                       value="{{ old('expires_at', $voucher->expires_at?->format('Y-m-d\TH:i')) }}">
                                @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Note --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Note</label>
                            <textarea name="note" class="form-control" rows="2">{{ old('note', $voucher->note) }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-floppy me-1"></i> Save Changes
                            </button>
                            <a href="{{ route('vouchers.show', $voucher) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Current Values Preview ── --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3"
                 style="background:linear-gradient(135deg,#198754,#146c43);border-radius:16px">
                <div class="card-body p-4 text-white">
                    <div class="small opacity-75 text-uppercase fw-semibold mb-1">Current Values</div>
                    <div class="fs-5 fw-bold font-monospace mb-3">{{ $voucher->username }}</div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="small opacity-75">Status</div>
                            <div class="fw-bold">{{ ucfirst($voucher->status) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small opacity-75">Profile</div>
                            <div class="fw-bold">{{ $voucher->profile }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small opacity-75">Time Limit</div>
                            <div class="fw-bold">{{ $voucher->time_limit_formatted }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small opacity-75">Data Limit</div>
                            <div class="fw-bold">{{ $voucher->data_limit_formatted }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small opacity-75">Price</div>
                            <div class="fw-bold">{{ $voucher->price ? '$'.number_format($voucher->price,2) : '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small opacity-75">Batch</div>
                            <div class="fw-bold">{{ $voucher->batch ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold text-muted mb-3">
                        <i class="bi bi-exclamation-triangle me-1 text-warning"></i> Notes
                    </h6>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-2"><i class="bi bi-lock me-2"></i>Username and password cannot be changed.</li>
                        <li class="mb-2"><i class="bi bi-cloud me-2"></i>Changes here are saved to the database only.</li>
                        <li><i class="bi bi-arrow-clockwise me-2"></i>To update MikroTik, use the Sync button on the detail page.</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection
