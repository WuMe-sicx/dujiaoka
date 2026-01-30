@extends('hyper.layouts.default')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">{{ __('dujiaoka.access_password_required') }}</h4>
        </div>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-3">
                    {{ __('dujiaoka.access_password_hint', ['name' => $goods_name]) }}
                </p>

                @if($error)
                    <div class="alert alert-danger" role="alert">
                        {{ $error }}
                    </div>
                @endif

                <form action="{{ url('buy/' . $goods_id) }}" method="post">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="access_password">{{ __('dujiaoka.access_password') }}</label>
                        <input type="password"
                               class="form-control"
                               id="access_password"
                               name="access_password"
                               required
                               autofocus
                               placeholder="{{ __('dujiaoka.enter_access_password') }}">
                    </div>
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            {{ __('dujiaoka.verify_password') }}
                        </button>
                        <a href="{{ url('/') }}" class="btn btn-secondary">
                            {{ __('dujiaoka.back_to_home') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
