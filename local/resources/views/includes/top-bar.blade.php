<!-- Top Bar Start -->
<div class="topbar">
    <div class="topbar-left">
        <div class="logo">
            <h1>
                <a href="{{url('/')}}">
                <img src="{{ asset('images/ar-karyati.png') }}" alt="Logo" style="height: 90%;">
                <!-- <div style="color:white;font-size:17pt; padding-left: 17px;" class="text-left"></div> -->
                </a>
            </h1>
        </div>
        <button class="button-menu-mobile open-left">
            <i class="fa fa-bars"></i>
        </button>
    </div>
    <!-- Button mobile view to collapse sidebar menu -->
    <div class="navbar navbar-default" role="navigation">
        <div class="container">
            <div class="navbar-collapse2">
                <ul class="nav navbar-nav navbar-right top-navbar">
                    <li class="dropdown iconify hide-phone">
                      <a href="#" data-toggle="tooltip" data-placement="bottom" title="Full Screen" onclick="javascript:toggle_fullscreen()">
                        <i class="icon-resize-full-2"></i>
                      </a>
                    </li>

                    <?php
                    $notifications = App\Service\NotificationService::getNotifications(Auth::user()->id);
                    $count         = App\Service\NotificationService::getCountNotification(Auth::user()->id);
                    ?>
                    <li id="notifications" class="dropdown iconify hide-phone">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                          <i class="fa fa-envelope"></i>
                          <span class="label label-danger absolute count">{{ $count > 0 ? $count : '' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-message">
                            <li class="dropdown-header notif-header"><i class="icon-mail-2"></i> New Notifications</li>
                            @foreach($notifications as $notification)
                            <?php
                            $createdAt = new \DateTime($notification->created_at);
                            $role = $notification->role;
                            $branch = $notification->branch;
                            ?>
                            <li class="unread">
                                <a href="{{ URL('read-notification/'.$notification->notification_id) }}" class="clearfix notification">
                                    <strong>{{ $notification->category }}</strong>
                                    <i class="pull-right text-right msg-time">
                                        {{ $createdAt->format('d-m-Y H:i') }}<br/>
                                        {{ $role !== null ? $role->name : '' }}<br/>
                                        {{ $branch !== null ? $branch->branch_name : '' }}
                                    </i>
                                    <br />
                                    <p>{{ $notification->message }}</p>
                                </a>
                            </li>
                            @endforeach
                            <li class="dropdown-footer">
                                <div class="">
                                    <a href="{{ URL('notification') }}" class="btn btn-sm btn-block btn-primary">
                                        <i class="icon-mail-2"></i> Show All
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown iconify hide-phone">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-gear"></i></a>
                        <ul class="dropdown-menu" style="width: 300px;">
                            <div id="horizontal-form">
                                <form  role="form" class="form-horizontal" method="post" action="{{ URL('/ganti-role-dan-cabang') }}">
                                    {{ csrf_field() }}
                                    <li class="dropdown-header notif-header"><i class="fa fa-gear"></i> {{ trans('shared/dashboard.ganti-role-cabang') }}</li>
                                    <li style="padding: 5px;">
                                        <div class="form-group">
                                              <label class="col-sm-4 control-label">{{ trans('shared/dashboard.role') }}</label>
                                              <div class="col-sm-8">
                                                  <select class="form-control" name="gantiRole">
                                                    <?php
                                                    $roles = \DB::table('adm.roles')
                                                                ->select('roles.*')
                                                                ->join('adm.user_role', 'roles.id', '=', 'user_role.role_id')
                                                                ->where('user_role.user_id', '=', Auth::user()->id)
                                                                ->where('roles.active', '=', 'Y')
                                                                ->orderBy('roles.name', 'asc')
                                                                ->get();
                                                    ?>
                                                    @foreach($roles as $role)
                                                      <option value="{{ $role->id }}" {{ $role->id == Session::get('currentRole')->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                                    @endforeach
                                                  </select>
                                              </div>
                                          </div>
                                        <div class="form-group">
                                              <label class="col-sm-4 control-label">{{ trans('shared/dashboard.cabang') }}</label>
                                              <div class="col-sm-8">
                                                  <select class="form-control" name="gantiCabang">
                                                    <?php
                                                    $branchs = DB::table('op.mst_branch')
                                                                    ->select('mst_branch.*')
                                                                    ->join('adm.user_role_branch', 'mst_branch.branch_id', '=', 'user_role_branch.branch_id')
                                                                    ->join('adm.user_role', 'user_role_branch.user_role_id', '=', 'user_role.user_role_id')
                                                                    ->where('user_role.user_id', '=', Auth::user()->id)
                                                                    ->where('user_role.role_id', '=', Session::get('currentRole')->id)
                                                                    ->where('mst_branch.active', '=', 'Y')
                                                                    ->orderBy('mst_branch.branch_name', 'asc')
                                                                    ->get();
                                                    ?>
                                                    @foreach($branchs as $branch)
                                                      <option value="{{ $branch->branch_id }}" {{ Session::has('currentBranch') && $branch->branch_id == Session::get('currentBranch')->branch_id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                                    @endforeach
                                                  </select>
                                              </div>
                                          </div>
                                    </li>
                                    <li class="dropdown-footer">
                                        <div class="">
                                            <button href="#" class="btn btn-sm btn-block btn-primary">
                                                <i class="fa fa-save"></i> {{ trans('shared/dashboard.ganti-role-cabang') }}
                                            </button>
                                        </div>
                                    </li>
                                </form>
                            </div>
                        </ul>
                    </li>
                    <li class="dropdown topbar-profile">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><strong>{{ Auth::user()->name }}</strong> <i class="fa fa-caret-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ URL('change-profile') }}"><i class="icon-user-1"></i>{{ trans('shared/dashboard.change-profile') }}</a></li>
                            <li><a href="{{ URL('change-password') }}"><i class="icon-lock-1"></i>{{ trans('shared/dashboard.change-password') }}</a></li>
                            <li class="divider"></li>
                            <li><a href="{{ url('locked') }}"><i class="icon-lock-3"></i> {{ trans('shared/dashboard.lock-me') }}</a></li>
                            <li><a class="md-trigger" data-modal="logout-modal"><i class="icon-logout-1"></i> {{ trans('shared/dashboard.logout') }}</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
    </div>
</div>
<!-- Top Bar End -->
