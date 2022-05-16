{{-- @dd($notifications) --}}
<div class="row navbar navbar-expand-lg navbar-light bg-light w-100 px-2">
    <div class="col-3 d-flex">
        <a class="navbar-brand" href="#">SC</a>
        <form>
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
        </form>
    </div>
    <div class="col-5">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">
                    <i class="fa fa-home" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fa fa-television" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Dropdown
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled">Disabled</a>
            </li>
        </ul>
    </div>
    <div class="col-4 d-flex">
        {{-- @include('app.profile.profile-show') --}}
        {{-- @include('app.profile.profile-edit') --}}
        <a class="text-black w-20 text-decoration-none mt-2" href="{{ route('auth.logout') }}">Logout</a>
        <div class="dropdown mt-2 ms-2">
            <div class="btn dropdown-toggle p-0" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i> Thông báo ({{ $notifications->count() }})
            </div>
            <ul style="width: 100%;" class="dropdown-menu" data-spy="scroll"  data-offset="0">
                @if (!empty($notifications) && $notifications->count() > 0)
                    @foreach ($notifications as $noti)
                        @if ($noti->action == "reject")
                            <li class="border-bottom p-2">
                                <a class="text text-black text-decoration-none"> {{ $noti->data }} </a>
                            </li>
                        @else
                            <li class="border-bottom p-2">
                                <a class="text text-black text-decoration-none" href="{{ route("notices.show", ["notice"=>$noti->id]) }}"> {{ $noti->data }} </a>
                            </li>
                        @endif
                    @endforeach
                @else
                <li>
                    No Notification
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
