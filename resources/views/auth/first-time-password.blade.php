

@extends('layouts/blankLayout')

@section('title', 'Change Password - First Time Login')

@section('content')
<div class="authentication-wrapper authentication-cover">
  <div class="authentication-inner row m-0">
    <!-- Left Text -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
      <div class="w-100 d-flex justify-content-center">
        <img src="{{asset('assets/img/illustrations/girl-doing-yoga-light.png')}}" class="img-fluid" alt="Login image" width="700" data-app-dark-img="illustrations/girl-doing-yoga-dark.png" data-app-light-img="illustrations/girl-doing-yoga-light.png">
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Change Password -->
    <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4">
      <div class="w-px-400 mx-auto">
        <!-- Logo -->
        <div class="app-brand mb-5">
          <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
            <span class="app-brand-text demo text-body fw-bolder">{{config('variables.templateName')}}</span>
          </a>
        </div>
        <!-- /Logo -->

        <h4 class="mb-2">Welcome! 👋</h4>
        <p class="mb-4">You're logging in for the first time. Please change your password to continue.</p>

        @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        @if (session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
        @endif

        <form id="formAuthentication" class="mb-3" action="{{ route('password.update-first-time') }}" method="POST">
          @csrf

          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="current_password">Current Password</label>
            <div class="input-group input-group-merge">
              <input type="password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" placeholder="Enter the password assigned to you" required />
              <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            @error('current_password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password">New Password</label>
            <div class="input-group input-group-merge">
              <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter your new password" required />
              <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Password must be at least 8 characters long</small>
          </div>

          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password_confirmation">Confirm New Password</label>
            <div class="input-group input-group-merge">
              <input type="password" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder="Confirm your new password" required />
              <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            @error('password_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <button class="btn btn-primary d-grid w-100" type="submit">
            Change Password & Continue
          </button>
        </form>

        <div class="text-center">
          <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-link text-muted">
              <i class="bx bx-log-out me-2"></i>
              Logout
            </button>
          </form>
        </div>
      </div>
    </div>
    <!-- /Change Password -->
  </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Password visibility toggle
  $('.form-password-toggle .input-group-text').on('click', function() {
    const input = $(this).siblings('input');
    const icon = $(this).find('i');

    if (input.attr('type') === 'password') {
      input.attr('type', 'text');
      icon.removeClass('bx-hide').addClass('bx-show');
    } else {
      input.attr('type', 'password');
      icon.removeClass('bx-show').addClass('bx-hide');
    }
  });

  // Form validation
  $('#formAuthentication').on('submit', function(e) {
    const password = $('#password').val();
    const confirmPassword = $('#password_confirmation').val();

    if (password !== confirmPassword) {
      e.preventDefault();
      alert('Passwords do not match!');
      return false;
    }

    if (password.length < 8) {
      e.preventDefault();
      alert('Password must be at least 8 characters long!');
      return false;
    }
  });
});
</script>
@endsection
