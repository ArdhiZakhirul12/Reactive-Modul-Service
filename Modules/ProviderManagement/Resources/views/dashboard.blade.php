@extends('providermanagement::layouts.master')

@section('title',translate('Dashboard'))

@push('css_or_js')

@endpush

@section('content')

    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4 g-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="business-summary business-summary-customers">
                        <h2>{{ $data[7]['total_pending'] }}</h2>
                        <h3>{{ translate('total_pending') }}</h3>
                        <img src="{{ asset('public/assets/admin-module') }}/img/icons/customers.png" class="absolute-img"
                            alt="">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="business-summary business-summary-earning">
                        <h2>{{ $data[7]['total_ongoing'] }}</h2>
                        <h3>{{ translate('total_ongoing') }}</h3>
                        <img src="{{ asset('public/assets/admin-module') }}/img/icons/providers.png"
                            class="absolute-img" alt="">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="business-summary business-summary-providers">
                        <h2>{{ $data[7]['total_completed'] }}</h2>
                        <h3>{{ translate('Total_completed') }}</h3>
                        <img src="{{ asset('public/assets/admin-module') }}/img/icons/total-earning.png"
                            class="absolute-img" alt="">

                    </div>
                </div>
                {{-- <div class="col-lg-3 col-sm-6">
                    <div class="business-summary business-summary-providers">
                        <h2>{{$data[0]['top_cards']['total_customer']}}</h2>
                        <h3>{{translate('customers')}}</h3>
                        <img src="{{asset('public/assets/admin-module')}}/img/icons/providers.png"
                             class="absolute-img"
                             alt="">
                    </div>
                </div> --}}
                <div class="col-lg-3 col-sm-6">
                    <div class="business-summary business-summary-services">
                        <h2>{{ $data[7]['total_custom'] }}</h2>
                        <h3>{{ translate('total_custom_request') }}</h3>
                        <img src="{{ asset('public/assets/admin-module') }}/img/icons/services.png"
                            class="absolute-img" alt="">
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-sm-6">
                    <div class="card top-providers h-100">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h5 class="c1">{{translate('Recent_Bookings')}}</h5>
                            <a href="{{route('provider.booking.list', ['booking_status'=>'pending'])}}"
                               class="btn-link c1">{{translate('View all')}}</a>
                        </div>
                        <div class="card-body">
                            <ul class="common-list">
                                @if(count($data[3]['recent_bookings']) < 1)
                                    <span class="opacity-75">{{translate('No_recent_bookings_are_available')}}</span>
                                @endif
                                @foreach($data[3]['recent_bookings'] as $key=>$booking)
                                    <li class="@if($key==0) pt-0 @endif d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer booking-item"
                                        data-booking="{{$booking->id}}">
                                        <div class="media align-items-center gap-3">
                                            <div class="avatar avatar-lg">
                                                <img class="avatar-img rounded"
                                                     src="{{onErrorImage($booking->detail[0]->service?->thumbnail??'',
                                                            asset('storage/app/public/service').'/' . $booking->detail[0]->service?->thumbnail??'',
                                                            asset('public/assets/placeholder.png') ,
                                                            'provider/logo/')}}"
                                                     alt="">
                                            </div>
                                            <div class="media-body ">
                                                <h5>{{translate('Booking')}}# {{$booking['readable_id']}}</h5>
                                                <p>{{date('d-M-y H:iA', strtotime($booking->created_at))}}</p>
                                            </div>
                                        </div>
                                        <span
                                            class="badge rounded-pill py-2 px-3 badge-info">{{translate($booking['booking_status'])}}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-sm-6">
                    <div class="card recent-activities h-100">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h5 class="c1">{{translate('suku_cadang_tertunda')}}</h5>
                            <a href="{{route('provider.sub_category.subscribed', ['status' => 'all'])}}"
                               class="btn-link c1">{{translate('View all')}}</a>
                        </div>
                        <div class="card-body">
                            <ul class="common-list">                                
                                @foreach ($data[9]['recent_pending_sparepart'] as $booking)
                                    <li class="d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer recent-booking-redirect"
                                        data-route="{{ route('admin.booking.details', [$booking->id]) }}?web_page=details">
                                        <div class="media align-items-center gap-3">
                                            {{-- <div class="avatar avatar-lg">
                                                <img class="avatar-img rounded"
                                                     src="{{onErrorImage($booking->detail[0]->service?->thumbnail??'', asset('storage/app/public/service').'/' . $booking->detail[0]->service?->thumbnail??'',
                                                     asset('public/assets/admin-module/img/placeholder.png') ,'service/')}}"
                                                     alt="{{ translate('provider-logo') }}">
                                            </div> --}}
                                            <div class="media-body ">
                                                <h5>{{ $booking->readable_id }} {{ $booking->customer?->first_name }}
                                                    {{ $booking->customer?->last_name }}</h5>
                                                <p>{{ date('d-m-Y, H:i a', strtotime($booking->created_at)) }}</p>
                                            </div>
                                        </div>
                                        <span
                                            class="badge rounded-pill py-2 px-3 badge-primary text-capitalize">Pending Sparepart</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-sm-6">
                    <div class="card recent-transactions h-100">
                        <div class="card-header d-flex flex-column gap-10">
                            <h5>{{ translate('most_serviced_machine') }}</h5>
                        </div>
                        <div class="card-body booking-statistics-info">
                            {{-- @dd($data[8]['top_category']) --}}
                            {{-- @if (isset($data[5]['zone_wise_bookings'])) --}}
                            <ul class="common-list">
                                @foreach ($data[8]['top_category'] as $category)
                                    <li class="provider-redirect">
                                        <div class="media gap-3">
                                            <div class="avatar avatar-lg">
                                                <img class="avatar-img rounded-circle"
                                                    src="{{ onErrorImage(
                                                        $category['sub_category']['image'] ?? '',
                                                        asset('storage/app/public/category') . '/' . $category['sub_category']['image'] ?? '',
                                                        asset('public/assets/placeholder.png'),
                                                        'business/',
                                                    ) }}">
                                            </div>
                                            <div class="media-body ">
                                                <h5>{{ $category['sub_category']['name'] }}</h5>
                                                <span class="common-list_rating d-flex gap-1">
                                                    <span class="material-icons">task</span>
                                                    {{ $category['category_count'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            {{-- @else
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <span class="opacity-50">{{translate('No Bookings Found')}}</span>
                                </div>
                            @endif --}}
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <div class="card earning-statistics">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h4 class="c1">{{translate('Booking_Statistics')}}</h4>
                                <div
                                    class="position-relative index-2 d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                    <ul class="option-select-btn">
                                        <li>
                                            <label>
                                                <input type="radio" name="statistics" hidden checked>
                                                <span>{{translate('Yearly')}}</span>
                                            </label>
                                        </li>
                                    </ul>

                                    <div class="select-wrap d-flex flex-wrap gap-10">
                                        <select class="js-select" onchange="update_chart(this.value)">
                                            @php($from_year=date('Y'))
                                            @php($to_year=$from_year-10)
                                            @while($from_year!=$to_year)
                                                <option
                                                    value="{{$from_year}}" {{session()->has('dashboard_earning_graph_year') && session('dashboard_earning_graph_year') == $from_year?'selected':''}}>
                                                    {{$from_year}}
                                                </option>
                                                @php($from_year--)
                                            @endwhile
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="apex_line-chart"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="card top-providers h-100">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h5 class="c1">{{translate('Serviceman_List')}}</h5>
                            <a href="{{route('provider.serviceman.list')}}?status=all"
                               class="btn-link c1">{{translate('View all')}}</a>
                        </div>
                        <div class="card-body">
                            <ul class="common-list">
                                @if(count($data[5]['serviceman_list']) < 1)
                                    <span class="opacity-75">{{translate('No_active_servicemen_are_available')}}</span>
                                @endif
                                @for($i = 0; $i < count($servicemenList['id']); $i++)
                                        {{-- @dd($servicemenList) --}}
                                        <li class="d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer serviceman-booking-redirect"
                                            data-route="{{ route('admin.booking.details', [$servicemenList['id'][$i], 'web_page' => 'details']) }}">
                                            <div class="media gap-3">
                                                <div class="avatar avatar-lg">
                                                    {{-- <a href="{{route('admin.booking.details', [$serviceman->id,'web_page'=>'details'])}}"> --}}
                                                    <img class="object-fit rounded-circle"
                                                        src="{{ onErrorImage(
                                                            $servicemenList['photo'][$i],
                                                            asset('storage/app/public/serviceman/profile') . '/' . $servicemenList['photo'][$i],
                                                            asset('public/assets/placeholder.png'),
                                                            'serviceman/profile/',
                                                        ) }}"
                                                        alt="">
                                                    {{-- </a> --}}
                                                </div>
                                                <div class="media-body ">
                                                    {{-- <a href="{{route('admin.booking.details', [$serviceman->id,'web_page'=>'details'])}}"> --}}
                                                    <h5>{{ Str::limit($servicemenList['name'][$i], 30) }}
                                                    </h5>
                                                    {{-- </a> --}}
                                                    <span
                                                        class="badge rounded-pill py-2 px-3 badge-primary text-capitalize">{{ $servicemenList['status'][$i] }}</span>
                                                </div>
                                            </div>
                                        </li>
                                    @endfor
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/provider-module')}}/plugins/apex/apexcharts.min.js"></script>
    <script>
        "use strict";

        var options = {
            series: [{
                    name: "{{ translate('total_booking') }}",

                    data: @json($chart_data['booking'])
                },
                {
                    name: "{{ translate('total_completed_booking') }}",

                    data: @json($chart_data['completed_booking'])
                }
            ],
            chart: {
                height: 386,
                type: 'line',
                dropShadow: {
                    enabled: true,
                    color: '#000',
                    top: 18,
                    left: 7,
                    blur: 10,
                    opacity: 0.2
                },
                toolbar: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    offsetX: 0,
                    formatter: function (value) {
                        return value;
                    }
                },
            },
            colors: ['#82C662', '#4FA7FF'],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth',
            },
            grid: {
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                borderColor: '#CAD2FF',
                strokeDashArray: 5,
            },
            markers: {
                size: 1
            },
            theme: {
                mode: 'light',
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                floating: true,
                offsetY: -10,
                offsetX: 0
            },
            padding: {
                top: 0,
                right: 0,
                bottom: 200,
                left: 10
            },
        };

        if (localStorage.getItem('dir') === 'rtl') {
            options.yaxis.labels.offsetX = -20;
        }

        var chart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
        chart.render();

        function update_chart(year) {
            var url = '{{route('provider.update-dashboard-earning-graph')}}?year=' + year;

            $.getJSON(url, function (response) {
                console.log(response.earning_stats)
                chart.updateSeries([{
                    name: "{{translate('total_earnings')}}",
                    data: response.total_earning
                }])
            });
        }

        $(document).ready(function () {
            let routeName = '{{ route('provider.booking.details', ['id' => ':id']) }}';

            $('.booking-item').on('click', function () {
                var bookingId = $(this).data('booking');
                var url = routeName.replace(':id', bookingId);
                window.location.href = url + '?web_page=details';
            });
        });
    </script>
@endpush
