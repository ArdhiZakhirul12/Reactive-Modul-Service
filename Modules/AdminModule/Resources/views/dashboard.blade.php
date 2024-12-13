@extends('adminmodule::layouts.master')

@section('title', translate('dashboard'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            {{-- @dd() --}}
            @if (access_checker('dashboard'))
                <div class="row mb-4 g-4">
                    <div class="col-lg-3 col-sm-6">
                        <div class="business-summary business-summary-customers">
                            <h2>{{ $data[6]['total_pending'] }}</h2>
                            <h3>{{ translate('total_antrian') }}</h3>
                            <img src="{{ asset('public/assets/admin-module') }}/img/icons/customers.png" class="absolute-img"
                                alt="">
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="business-summary business-summary-earning">
                            <h2>{{ $data[6]['total_ongoing'] }}</h2>
                            <h3>{{ translate('total_berlangsung') }}</h3>
                            <img src="{{ asset('public/assets/admin-module') }}/img/icons/providers.png"
                                class="absolute-img" alt="">
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="business-summary business-summary-providers">
                            <h2>{{ $data[6]['total_completed'] }}</h2>
                            <h3>{{ translate('Total_selesai') }}</h3>
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
                            <h2>{{ $data[6]['total_custom'] }}</h2>
                            <h3>{{ translate('total_permintaan_disesuaikan') }}</h3>
                            <img src="{{ asset('public/assets/admin-module') }}/img/icons/services.png"
                                class="absolute-img" alt="">
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-sm-6">
                        <div class="card top-providers">
                            <div class="card-header d-flex justify-content-between gap-10">
                                <h5>{{ translate('Teknisi') }}</h5>
                                {{-- <a href="{{ route('admin.provider.list') }}"
                                    class="btn-link">{{ translate('view_all') }}</a> --}}
                            </div>
                            <div class="card-body">
                                <ul class="common-list">
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
                    <div class="col-lg-5 col-sm-6">
                        <div class="card recent-activities">
                            <div class="card-header d-flex justify-content-between gap-10">
                                <h5>{{ translate('suku_cadang_tertunda_terbaru') }}</h5>
                                <a href="{{ route('admin.booking.list', ['booking_status' => 'pending']) }}"
                                    class="btn-link">{{ translate('view_all') }}</a>
                            </div>
                            <div class="card-body">
                                <ul class="common-list">
                                    @foreach ($data[7]['recent_pending_sparepart'] as $booking)
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
                                                class="badge rounded-pill py-2 px-3 badge-primary text-capitalize">{{ $booking->booking_status }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="card recent-transactions h-100">
                            <div class="card-body">
                                <h4 class="mb-1">{{ translate('pemesanan_terbaru') }}</h4>
                                {{-- @if (isset($data[2]['recent_transactions']) && count($data[2]['recent_transactions']) > 0)
                                    <div class="d-flex align-items-center gap-3 mb-4">
                                        <img src="{{asset('public/assets/admin-module')}}/img/icons/arrow-up.png"
                                             alt="">
                                        <p class="opacity-75">{{$data[2]['this_month_trx_count']}} {{translate('transactions_this_month')}}</p>
                                    </div>
                                @endif --}}
                                <div class="card-body">
                                    <ul class="common-list">
                                        @foreach ($data[3]['bookings'] as $booking)
                                            <li class="d-flex flex-wrap mt-2 gap-2 align-items-center justify-content-between cursor-pointer recent-booking-redirect"
                                                data-route="{{ route('admin.booking.details', [$booking->id]) }}?web_page=details">
                                                <div class="media align-items-center gap-3">
                                                    {{-- <div class="avatar avatar-lg">
                                                    <img class="avatar-img rounded"
                                                         src="{{onErrorImage($booking->detail[0]->service?->thumbnail??'', asset('storage/app/public/service').'/' . $booking->detail[0]->service?->thumbnail??'',
                                                         asset('public/assets/admin-module/img/placeholder.png') ,'service/')}}"
                                                         alt="{{ translate('provider-logo') }}">
                                                </div> --}}
                                                    <div class="media-body ">
                                                        <h5>{{ translate('Booking') }}# {{ $booking->readable_id }}</h5>
                                                        <p>{{ date('d-m-Y, H:i a', strtotime($booking->created_at)) }}</p>
                                                    </div>
                                                </div>
                                                {{-- <span class="mb-2"></span> --}}

                                                {{-- <span
                                                class="badge rounded-pill py-2 px-3 badge-primary text-capitalize">{{$booking->booking_status}}</span> --}}
                                            </li>
                                        @endforeach

                                    </ul>
                                </div>
                                {{-- <div class="card-body">
                                    <ul class="common-list">
                                        @foreach ($data[7]['recent_pending_sparepart'] as $booking)
                                            <li class="d-flex flex-wrap gap-2 align-items-center justify-content-between cursor-pointer recent-booking-redirect"
                                                data-route="{{route('admin.booking.details',[$booking->id])}}?web_page=details">
                                                <div class="media align-items-center gap-3">
                                                    <div class="avatar avatar-lg">
                                                        <img class="avatar-img rounded"
                                                             src="{{onErrorImage($booking->detail[0]->service?->thumbnail??'', asset('storage/app/public/service').'/' . $booking->detail[0]->service?->thumbnail??'',
                                                             asset('public/assets/admin-module/img/placeholder.png') ,'service/')}}"
                                                             alt="{{ translate('provider-logo') }}">
                                                    </div>
                                                    <div class="media-body ">
                                                        <h5>{{$booking->readable_id}} {{$booking->customer?->first_name}} {{ $booking->customer?->last_name}}</h5>
                                                        <p>{{date('d-m-Y, H:i a',strtotime($booking->created_at))}}</p>
                                                    </div>
                                                </div>
                                                <span
                                                    class="badge rounded-pill py-2 px-3 badge-primary text-capitalize">{{$booking->booking_status}}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div> --}}
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="card earning-statistics">
                            <div class="card-body ps-0">
                                <div class="ps-20 d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <h4>{{ translate('statistik_pemesanan') }}</h4>
                                    <div
                                        class="position-relative index-2 d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                        <ul class="option-select-btn">
                                            <li>
                                                <label>
                                                    <input type="radio" name="statistics" hidden checked>
                                                    <span>{{ translate('yearly') }}</span>
                                                </label>
                                            </li>
                                        </ul>

                                        <div class="select-wrap d-flex flex-wrap gap-10">
                                            <select class="js-select update-chart">
                                                @php($from_year = date('Y'))
                                                @php($to_year = $from_year - 10)
                                                @while ($from_year != $to_year)
                                                    <option value="{{ $from_year }}"
                                                        {{ session()->has('dashboard_earning_graph_year') && session('dashboard_earning_graph_year') == $from_year ? 'selected' : '' }}>
                                                        {{ $from_year }}
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
                        <div class="card top-providers">
                            <div class="card-header d-flex flex-column gap-10">
                                <h5>{{ translate('mesin_paling_sering_diservis') }}</h5>
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
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="text-center">
                                    {{ translate('welcome_to_admin_panel') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('public/assets/admin-module') }}/plugins/apex/apexcharts.min.js"></script>
    <script>
        'use strict';

        $('.js-select.update-chart').on('change', function() {
            var selectedYear = $(this).val();
            update_chart(selectedYear);
        });

        var options = {
            series: [{
                    name: "{{ translate('total_pemesanan') }}",

                    data: @json($chart_data['booking'])
                },
                {
                    name: "{{ translate('total_pemesanan_selesai') }}",

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
                    formatter: function(value) {
                        return Math.abs(value)
                    }
                },
            },
            colors: ['#4FA7FF', '#82C662'],
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
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                floating: false,
                offsetY: -10,
                offsetX: 0,
                itemMargin: {
                    horizontal: 10,
                    vertical: 10
                },
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
            var url = '{{ route('admin.update-dashboard-earning-graph') }}?year=' + year;

            $.getJSON(url, function(response) {
                chart.updateSeries([{
                    name: "{{ translate('total_earning') }}",
                    data: response.total_earning
                }, {
                    name: "{{ translate('admin_commission') }}",
                    data: response.commission_earning
                }])
            });
        }

        $(".provider-redirect").on('click', function() {
            location.href = $(this).data('route');
        });

        $(".recent-booking-redirect").on('click', function() {
            location.href = $(this).data('route');
        });
    </script>
@endpush
