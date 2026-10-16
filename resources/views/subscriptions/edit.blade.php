@extends('layouts.app')

@section('title', 'Edit Subscription – SubShield')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('subscriptions.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0 fw-bold">Edit — {{ $subscription->name }}</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('subscriptions.update', $subscription) }}" method="POST">
                    @csrf @method('PUT')
                    @include('subscriptions._form')
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>Update Subscription
                        </button>
                        <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
