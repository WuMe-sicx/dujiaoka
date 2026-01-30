@extends('luna.layouts.default')

@section('content')
    <body>
    @include('luna.layouts._nav')

    <div class="main">
        <div class="layui-row">
            <div class="layui-col-md6 layui-col-md-offset3 layui-col-sm12">
                <div class="main-box" style="margin-top: 50px;">
                    <div class="title" style="border-bottom: 1px solid #f7f7f7;padding-bottom: 10px">
                        <svg t="1602931755138" class="icon" viewBox="0 0 1024 1024" version="1.1"
                             xmlns="http://www.w3.org/2000/svg" p-id="4748" width="20" height="20">
                            <path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64z m0 820c-205.4 0-372-166.6-372-372s166.6-372 372-372 372 166.6 372 372-166.6 372-372 372z" fill="#3C8CE7"></path>
                            <path d="M512 336c-62.9 0-114 51.1-114 114v64h-30v160h288v-160h-30v-64c0-62.9-51.1-114-114-114z m-38 114c0-21 17-38 38-38s38 17 38 38v64h-76v-64z" fill="#3C8CE7"></path>
                        </svg>
                        <span>{{ __('dujiaoka.access_password_required') }}</span>
                    </div>

                    <div style="padding: 20px;">
                        <p class="layui-text" style="color: #666; margin-bottom: 15px;">
                            {{ __('dujiaoka.access_password_hint', ['name' => $goods_name]) }}
                        </p>

                        @if($error)
                            <div class="layui-bg-red" style="padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                                {{ $error }}
                            </div>
                        @endif

                        <form class="layui-form" action="{{ url('buy/' . $goods_id) }}" method="post">
                            @csrf
                            <div class="layui-form-item">
                                <label class="layui-form-label">{{ __('dujiaoka.access_password') }}</label>
                                <div class="layui-input-block">
                                    <input type="password"
                                           name="access_password"
                                           required
                                           lay-verify="required"
                                           placeholder="{{ __('dujiaoka.enter_access_password') }}"
                                           autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn" lay-submit>{{ __('dujiaoka.verify_password') }}</button>
                                    <a href="{{ url('/') }}" class="layui-btn layui-btn-primary">{{ __('dujiaoka.back_to_home') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('luna.layouts._footer')
    </body>
@stop
