@extends('unicorn.layouts.default')
@section('content')
    <!-- main start -->
    <section class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card mt-5">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-lock"></i> {{ __('dujiaoka.access_password_required') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                {{ __('dujiaoka.access_password_hint', ['name' => $goods_name]) }}
                            </p>

                            @if($error)
                                <div class="alert alert-danger" role="alert">
                                    {{ $error }}
                                </div>
                            @endif

                            <form method="POST" action="{{ url('buy/' . $goods_id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="access_password" class="form-label">{{ __('dujiaoka.access_password') }}</label>
                                    <input type="password"
                                           class="form-control"
                                           id="access_password"
                                           name="access_password"
                                           required
                                           autofocus
                                           placeholder="{{ __('dujiaoka.enter_access_password') }}">
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('dujiaoka.verify_password') }}
                                    </button>
                                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                                        {{ __('dujiaoka.back_to_home') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- main end -->
@stop
