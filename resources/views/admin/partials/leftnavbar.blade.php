<!-- ========== Left Sidebar Start ========== -->
<div class="left-side-menu">

    <div class="slimscroll-menu">

        <!--- Sidemenu -->
        <!-- <div id="sidebar-menu">

            <ul class="metismenu" id="side-menu">

                <li class="menu-title">Navigation</li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-airplay"></i>
                        <span class="badge badge-success badge-pill float-right">2</span>
                        <span> Dashboard </span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('dashboard1')}}">Dashboard 1</a></li>
                        <li> <a href="{{route('dashboard2')}}">Dashboard 2</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-airplay"></i>
                        <span class="menu-arrow"></span>
                        <span> Permissions </span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('permissions.index')}}">View</a></li>
                        <li><a href="{{route('permissions.create')}}">Create</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-airplay"></i>
                        <span class="menu-arrow"></span>
                        <span> Users </span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('users.index')}}">View</a></li>
                        <li><a href="{{route('users.create')}}">Create</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-airplay"></i>
                        <span class="menu-arrow"></span>
                        <span> Roles </span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('roles.index')}}">View</a></li>
                        <li><a href="{{route('roles.create')}}">Create</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-lock"></i>
                        <span class="menu-arrow"></span>
                        <span> Expense </span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('expense.index')}}">View</a></li>
                        <li><a href="{{route('expense.create')}}">New</a></li>
                    </ul>
                </li>

                {{--<li>
                    <a href="javascript: void(0);">
                        <i class="fe-file-plus"></i>
                        <span> Pages </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('pages.starter')}}">Starter Page</a></li>
                        <li><a href="{{route('pages.login')}}">Login</a></li>
                        <li><a href="{{route('pages.register')}}">Register</a></li>
                        <li><a href="{{route('pages.logout')}}">Logout</a></li>
                        <li><a href="{{route('pages.recoverpw')}}">Recover Password</a></li>
                        <li><a href="{{route('pages.lockscreen')}}">Lock Screen</a></li>
                        <li><a href="{{route('pages.confirmmail')}}">Confirm Mail</a></li>
                        <li><a href="{{route('pages.notfound')}}">Error 404</a></li>
                        <li><a href="{{route('pages.notfoundalt')}}">Error 404-alt</a></li>
                        <li><a href="{{route('pages.500')}}">Error 500</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-plus-square"></i>
                        <span> Extra Pages </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('extra.pages.about')}}">About Us</a></li>
                        <li><a href="{{route('extra.pages.contact')}}">Contact</a></li>
                        <li><a href="{{route('extra.pages.companies')}}">Companies</a></li>
                        <li><a href="{{route('extra.pages.members')}}">Members</a></li>
                        <li><a href="{{route('extra.pages.members2')}}">Members2</a></li>
                        <li><a href="{{route('extra.pages.timeline')}}">Timeline</a></li>
                        <li><a href="{{route('extra.pages.invoice')}}">Invoice</a></li>
                        <li><a href="{{route('extra.pages.maintenance')}}">Maintenance</a></li>
                        <li><a href="{{route('extra.pages.comingsoon')}}">Coming Soon</a></li>
                        <li><a href="{{route('extra.pages.faq')}}">FAQ</a></li>
                        <li><a href="{{route('extra.pages.pricing')}}">Pricing</a></li>
                        <li><a href="{{route('extra.pages.profile')}}">Profile</a></li>
                        <li><a href="{{route('extra.pages.email-template')}}">Email Templates</a></li>
                        <li><a href="{{route('extra.pages.search-result')}}">Search Results</a></li>
                        <li><a href="{{route('extra.pages.sitemap')}}">Site Map</a></li>
                    </ul>
                </li>

                <li class="menu-title">Apps</li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-mail"></i>
                        <span> Email </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('email.inbox')}}">Inbox</a></li>
                        <li><a href="{{route('email.read')}}">Read Email</a></li>
                        <li><a href="{{route('email.compose')}}">Compose Email</a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{route('calendar')}}">
                        <i class="fe-calendar"></i>
                        <span> Calendar </span>
                    </a>
                </li>

                <li>
                    <a href="{{route('tickets')}}">
                        <i class="fe-life-buoy"></i> 
                        <span> Tickets </span>
                        <span class="badge badge-danger badge-pill float-right">New</span>
                    </a>
                </li>

                <li>
                    <a href="{{route('taskboard')}}">
                        <i class="fe-file-text"></i> 
                        <span> Task Board </span>
                    </a>
                </li>

                <li>
                    <a href="{{route('todo')}}">
                        <i class="fe-layers"></i>
                        <span> Todo </span>
                    </a>
                </li>

                <li class="menu-title">Components</li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-target"></i>
                        <span> Admin UI </span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('admin.ui.grid')}}">Grid</a></li>
                        <li><a href="{{route('admin.ui.sweetalert')}}">Sweet Alert</a></li>
                        <li><a href="{{route('admin.ui.tilesbox')}}">Tiles Box</a></li>
                        <li><a href="{{route('admin.ui.nestable')}}">Nestable List</a></li>
                        <li><a href="{{route('admin.ui.rangeslider')}}">Range Slider</a></li>
                        <li><a href="{{route('admin.ui.ratings')}}">Ratings</a></li>
                        <li><a href="{{route('admin.ui.filemanager')}}">File Manager</a></li>
                        <li><a href="{{route('admin.ui.lightbox')}}">Lightbox</a></li>
                        <li><a href="{{route('admin.ui.scrollbar')}}">Scroll bar</a></li>
                        <li><a href="{{route('admin.ui.slider')}}">Slider</a></li>
                        <li><a href="{{route('admin.ui.treeview')}}">Treeview</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-briefcase"></i>
                        <span> UI Kit </span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('admin.ui.kit.typography')}}">Typography</a></li>
                        <li><a href="{{route('admin.ui.kit.cards')}}">Cards</a></li>
                        <li><a href="{{route('admin.ui.kit.buttons')}}">Buttons</a></li>
                        <li><a href="{{route('admin.ui.kit.modals')}}">Modals</a></li>
                        <li><a href="{{route('admin.ui.kit.checkbox-radio')}}">Checkboxs-Radios</a></li>
                        <li><a href="{{route('admin.ui.kit.spinners')}}">Spinners</a></li>
                        <li><a href="{{route('admin.ui.kit.ribbons')}}">Ribbons</a></li>
                        <li><a href="{{route('admin.ui.kit.portlets')}}">Portlets</a></li>
                        <li><a href="{{route('admin.ui.kit.tabs')}}">Tabs</a></li>
                        <li><a href="{{route('admin.ui.kit.progressbars')}}">Progress Bars</a></li>
                        <li><a href="{{route('admin.ui.kit.notifications')}}">Notification</a></li>
                        <li><a href="{{route('admin.ui.kit.carousel')}}">Carousel</a></li>
                        <li><a href="{{route('admin.ui.kit.video')}}">Video</a></li>
                        <li><a href="{{route('admin.ui.kit.tooltips-popovers')}}">Tooltips &amp; Popovers</a></li>
                        <li><a href="{{route('admin.ui.kit.images')}}">Images</a></li>
                        <li><a href="{{route('admin.ui.kit.bootstrap')}}">Bootstrap UI</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-box"></i>
                        <span>  Icons </span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('admin.icons.colored')}}">Colored Icons</a></li>
                        <li><a href="{{route('admin.icons.materialdesign')}}">Material Design</a></li>
                        <li><a href="{{route('admin.icons.dripicons')}}">Dripicons</a></li>
                        <li><a href="{{route('admin.icons.fontawesome')}}">Font awesome</a></li>
                        <li><a href="{{route('admin.icons.feather')}}">Feather Icons</a></li>
                        <li><a href="{{route('admin.icons.simple-line')}}">Simple line Icons</a></li>
                        <li><a href="{{route('admin.icons.flags')}}">Flag Icons</a></li>
                        <li><a href="{{route('admin.icons.file')}}">File Icons</a></li>
                        <li><a href="{{route('admin.icons.pe7')}}">PE7 Icons</a></li>
                        <li><a href="{{route('admin.icons.typicons')}}">Typicons</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-bar-chart-2"></i>
                        <span> Graphs </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('admin.graphs.flot')}}">Flot Chart</a></li>
                        <li><a href="{{route('admin.graphs.morris')}}">Morris Chart</a></li>
                        <li><a href="{{route('admin.graphs.google')}}">Google Chart</a></li>
                        <li><a href="{{route('admin.graphs.echart')}}">Echarts</a></li>
                        <li><a href="{{route('admin.graphs.chartist')}}">Chartist Charts</a></li>
                        <li><a href="{{route('admin.graphs.chartjs')}}">Chartjs Chart</a></li>
                        <li><a href="{{route('admin.graphs.c3')}}">C3 Chart</a></li>
                        <li><a href="{{route('admin.graphs.sparkline')}}">Sparkline Chart</a></li>
                        <li><a href="{{route('admin.graphs.knob')}}">Jquery Knob</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-disc"></i>
                        <span class="badge badge-warning badge-pill float-right">12</span>
                        <span> Forms </span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('admin.form.elements')}}">Form Elements</a></li>
                        <li><a href="{{route('admin.form.advanced')}}">Form Advanced</a></li>
                        <li><a href="{{route('admin.form.layouts')}}">Form Layouts</a></li>
                        <li><a href="{{route('admin.form.validation')}}">Form Validation</a></li>
                        <li><a href="{{route('admin.form.pickers')}}">Form Pickers</a></li>
                        <li><a href="{{route('admin.form.wizard')}}">Form Wizard</a></li>
                        <li><a href="{{route('admin.form.mask')}}">Form Masks</a></li>
                        <li><a href="{{route('admin.form.summernote')}}">Summernote</a></li>
                        <li><a href="{{route('admin.form.quilljs')}}">Quilljs Editor</a></li>
                        <li><a href="{{route('admin.form.typeahead')}}">Typeahead</a></li>
                        <li><a href="{{route('admin.form.editable')}}">X Editable</a></li>
                        <li><a href="{{route('admin.form.uploads')}}">Multiple File Upload</a></li>
                    </ul>
                </li>
    
                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-layout"></i>
                        <span> Tables </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('admin.tables.basic')}}">Basic Tables</a></li>
                        <li><a href="{{route('admin.tables.layouts')}}">Tables Layouts</a></li>
                        <li><a href="{{route('admin.tables.datatable')}}">Data Tables</a></li>
                        <li><a href="{{route('admin.tables.footables')}}">Foo Tables</a></li>
                        <li><a href="{{route('admin.tables.responsive')}}">Responsive Table</a></li>
                        <li><a href="{{route('admin.tables.tablesaw')}}">Tablesaw Tables</a></li>
                        <li><a href="{{route('admin.tables.editable')}}">Editable Tables</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-map"></i>
                        <span> Maps </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="{{route('admin.maps.google')}}">Google Maps</a></li>
                        <li><a href="{{route('admin.maps.vector')}}">Vector Maps</a></li>
                        <li><a href="{{route('admin.maps.mapael')}}">Mapael Maps</a></li>
                    </ul>
                </li>--}}
                <li>
                    <a href="javascript: void(0);">
                        <i class="fe-folder-plus"></i>
                        <span> Multi Level </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level nav" aria-expanded="false">
                        <li>
                            <a href="javascript: void(0);">Level 1.1</a>
                        </li>
                        <li>
                            <a href="javascript: void(0);" aria-expanded="false">Level 1.2
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="nav-third-level nav" aria-expanded="false">
                                <li>
                                    <a href="javascript: void(0);">Level 2.1</a>
                                </li>
                                <li>
                                    <a href="javascript: void(0);">Level 2.2</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
    
            </ul>

        </div> -->

        <div id="sidebar-menu">

            <ul class="metismenu" id="side-menu">

                <li class="menu-title">Navigation</li>
                <li>
                    <a href="{{ route("month-end.index") }}">
                        <i class="fas fa-calendar-check"></i>
                        <span>Month-end Dashboard</span>
                    </a>
                </li>
                @inject('menuService', 'App\Services\MenuService')

                @php
                $menus = $menuService->sidebarMenus(auth()->user());
                @endphp

                @include('admin.partials.sidebar-menu', ['menus' => $menus])
                <!-- End Sidebar -->
            </ul>
        </div>

        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>
<!-- Left Sidebar End -->