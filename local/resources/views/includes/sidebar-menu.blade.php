
            <div class="left side-menu">
                <div class="sidebar-inner slimscrollleft">
                    <div class="clearfix"></div>
                    <!--- Profile -->
                    <br>
                    <div class="profile-info">
                          <div class="col-xs-4">
                              <?php 
                              $foto = !empty(Auth::user()->foto) ? Config::get('app.paths.foto-user').'/'.Auth::user()->foto : 'images/users/user.png'; 
                              $fotoUser = file_exists($foto) ? $foto : 'images/users/user.png';
                              ?>
                              <a class="rounded-image profile-image"><img src="{{ asset($fotoUser) }}"></a>
                          </div>
                          <div class="col-xs-8">
                            <div class="profile-text">
                                <div style="font-size:16px;"><b>{{ Auth::user()->full_name }}</b></div>
                                <div style="font-size:13px;"><b>{{ Session::has('currentRole') ? Session::get('currentRole')->name : '' }}</b></div>
                                <div style="font-size:12px;">{{ Session::has('currentBranch') ? Session::get('currentBranch')->branch_name : '' }}</div>
                            </div>
                               <!--  <div class="profile-buttons">
                                  <a href="javascript:;"><i class="fa fa-envelope-o pulse"></i></a>
                                  <a href="#connect" class="open-right"><i class="fa fa-comments"></i></a>
                                  <a href="javascript:;" title="Sign Out"><i class="fa fa-power-off text-red-1"></i></a>
                              </div> -->
                          </div>
                      </div>
                      <!--- Divider -->
                      <div class="clearfix"></div>
                      <hr class="divider" />
                      <div class="clearfix"></div>
                      <div id="sidebar-menu">
                        <ul>
                            @foreach($items as $item)
                            <li>
                                <a href="{{ $item->hasChildren() ? '#' : url($item->link->path['url']) }}">
                                    {!! $item->icon !!}<span>{!! $item->title !!}</span>
                                    @if($item->hasChildren())
                                    <span class="pull-right"><i class="fa fa-angle-down"></i></span>
                                    @endif
                                </a>
                                @if($item->hasChildren())
                                <ul>
                                    @foreach($item->children() as $child)
                                    <li>
                                        <a href="{{ $child->hasChildren() ? '#' : url($child->link->path['url']) }}">
                                            <span>{{ $child->title }}</span>
                                            @if($child->hasChildren())
                                            <span class="pull-right"><i class="fa fa-angle-down"></i></span>
                                            @endif
                                        </a>
                                        @if($child->hasChildren())
                                        <ul>
                                            @foreach($child->children() as $grandchild)
                                            <li>
                                                <a href="{{ $grandchild->hasChildren() ? '#' : url($grandchild->link->path['url']) }}">
                                                    <span>{!! $grandchild->title !!}</span>
                                                </a>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="left-footer hidden">
                    <div class="progress progress-xs">
                        <div class="progress-bar bg-green-1" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                    </div>
                    <a data-toggle="tooltip" title="Help" class="btn btn-default">
                        <i class="fa fa-question-circle"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- Left Sidebar End -->
