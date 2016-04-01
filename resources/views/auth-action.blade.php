<ul class="nav navbar-nav navbar-right">
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><b>{{trans('auth.login')}}</b> <span class="caret"></span></a>
        <ul id="login" class="dropdown-menu">
            <li>
                <div class="row">
                    <div class="col-md-12">
                        <form class="form" role="form" method="post" action="{{ url('/login') }}" accept-charset="UTF-8" id="login-nav">
                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label class="sr-only" for="email">{{trans('auth.email_address')}}</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="{{trans('auth.email_address')}}" value="{{ old('email') }}" required>
                            </div>
                            @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label class="sr-only" for="password">{{trans('auth.password')}}</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="{{trans('auth.password')}}" required>
                                <div class="help-block text-right"><a href="{{ url('/password/reset') }}">{{trans('auth.forgot_password')}}</a></div>
                            </div>
                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">{{trans('auth.login')}}</button>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember"> {{trans('auth.remember')}}
                                </label>
                            </div>
                            {!! csrf_field() !!}
                        </form>
                    </div>
                    @if(config('app.user_registration'))
                    <div class="bottom text-center">
                        {{trans('auth.new_here')}} <a href="#"><b>{{trans('auth.join_us')}}</b></a>
                    </div>
                    @endif
                </div>
            </li>
        </ul>
    </li>
</ul>