@extends('layouts.app')

@section('title', 'Categories – SubShield')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Categories</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-lg me-1"></i>Add Category
    </button>
</div>

<div class="row g-3">
    @forelse($categories as $category)
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:{{ $category->color }}22;">
                        <span class="fw-bold" style="color:{{ $category->color }};">
                            {{ strtoupper(substr($category->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold">{{ $category->name }}</h6>
                        <small class="text-muted">{{ $category->subscriptions_count }} subscription{{ $category->subscriptions_count !== 1 ? 's' : '' }}</small>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editCategoryModal{{ $category->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST"
                              onsubmit="return confirm('Delete category {{ addslashes($category->name) }}? Subscriptions will not be deleted.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Edit modal for each category --}}
            <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('categories.update', $category) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Name</label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ $category->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Color</label>
                                    <input type="color" name="color" class="form-control form-control-color w-100"
                                           value="{{ $category->color }}" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-tags fs-1 d-block mb-2"></i>
                    No categories yet.
                    <button class="btn btn-link p-0 ms-1" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add one!</button>
                </div>
            </div>
        </div>
    @endforelse
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. Entertainment" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Color <span class="text-danger">*</span></label>
                        <input type="color" name="color" class="form-control form-control-color w-100"
                               value="{{ old('color', '#6366f1') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Re-open add modal if there were validation errors
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('addCategoryModal')).show();
        });
    @endif
</script>
@endpush
@endsection
