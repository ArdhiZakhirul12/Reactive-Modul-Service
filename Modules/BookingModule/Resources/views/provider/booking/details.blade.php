@extends('providermanagement::layouts.master')

@section('title',translate('Booking_Details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-4">
                <h2 class="page-title">{{translate('Booking_Details')}} </h2>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                        <h3 class="c1">{{translate('Booking')}} # {{$booking['readable_id']}}</h3>
                        <span class="badge badge-info">{{ucfirst($booking->booking_status)}}</span>
                    </div>
                    <p class="opacity-75 fz-12">{{translate('Booking_Placed')}}
                        : {{date('d-M-Y h:ia',strtotime($booking->created_at))}}</p>
                </div>
                <div class="d-flex flex-wrap flex-xxl-nowrap gap-3">
                    <div class="d-flex gap-3">
                        @php($provider_can_edit_booking = (int)(business_config('provider_can_edit_booking', 'provider_config'))?->live_values)

                        @if($provider_can_edit_booking && $booking->booking_partial_payments->isEmpty())
                            <button class="btn btn--primary" data-bs-toggle="modal"
                                    data-bs-target="#serviceUpdateModal--{{$booking['id']}}"
                                    data-toggle="tooltip"
                                    title="{{translate('Add or remove services')}}">
                                <span
                                    class="material-symbols-outlined">edit</span>{{translate('Edit Services')}}
                            </button>
                        @endif
                        <a href="{{route('provider.booking.invoice',[$booking->id])}}"
                        class="btn btn-primary" target="_blank">
                            <span class="material-icons">description</span>
                            {{translate('Laporan_Servis')}}
                        </a>
                    </div>
                </div>
            </div>

            <div>
                <ul class="nav nav--tabs nav--tabs__style2 mb-4">
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='details'?'active':''}}"
                        href="{{url()->current()}}?web_page=details">{{translate('details')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='status'?'active':''}}"
                        href="{{url()->current()}}?web_page=status">{{translate('status')}}</a>
                    </li>
                </ul>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-body pb-5">
                            <div
                                class="border-bottom py-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                <div>
                                    {{-- <h4 class="mb-2">{{translate('Payment_Method')}}</h4>
                                    <h5 class="c1 mb-2"><span
                                            class="text-capitalize">{{ str_replace(['_', '-'], ' ', $booking->payment_method) }}
                                            @if($booking->payment_method == 'offline_payment' && $booking?->booking_offline_payments?->first()?->method_name)
                                                ({{($booking?->booking_offline_payments?->first()?->method_name)}})
                                            @endif</span>
                                    </h5> --}}
                                    {{-- <p>
                                        <span>{{translate('Amount')}} : </span> {{with_currency_symbol($booking->total_booking_amount)}}
                                    </p> --}}
                                    {{-- @if($booking->payment_method == 'offline_payment')
                                        <h4 class="mb-2">{{translate('Payment_Info')}}</h4>
                                        <div class="d-flex gap-1 flex-column">
                                            @foreach($booking?->booking_offline_payments?->first()?->customer_information??[] as $key=>$item)
                                                <div><span>{{translate($key)}}</span>:
                                                    <span>{{translate($item)}}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif --}}
                                </div>
                                <div>
                                    <p class="mb-2">
                                        <span>{{translate('Status')}} : </span>
                                        <span
                                                    class="badge badge badge-{{$booking->is_paid?'success':'danger'}} radius-50 text-center">
                                                    <span class="dot"></span>
                                                    {{$booking->is_paid?translate(''):translate('')}}
                                                </span>

                                        @if(!$booking->is_paid && $booking->booking_partial_payments->isNotEmpty())
                                            <span
                                                class="small badge badge-info text-success p-1 fz-10">{{translate('Partially paid')}}</span>
                                        @endif
                                    </p>
                                    <h5 class="d-flex gap-1 flex-wrap align-items-center">
                                        <div>{{translate('Schedule_Date')}} :</div>
                                        <div id="service_schedule__span">
                                            @if ($booking->booking_status == 'pending')

                                            <div id="service_schedule__span">
                                                <div>{{ date('d-M-Y', strtotime($booking->service_schedule)) }} <span
                                                        class="text-secondary">{{ $booking?->schedule_histories->count() > 1 ? '(' . translate('Edited') . ')' : '' }}</span>
                                                </div>
                                            @else
                                            <div id="service_schedule__span">
                                                <div>{{ date('d-M-Y h:ia', strtotime($booking->service_schedule)) }} <span
                                                        class="text-secondary">{{ $booking?->schedule_histories->count() > 1 ? '(' . translate('Edited') . ')' : '' }}</span>
                                                </div>
                                            @endif

                                            <div class="timeline-container">
                                                <ul class="timeline-sessions">
                                                    <p class="fs-14">{{translate('Schedule Change Log')}}</p>
                                                    @foreach($booking?->schedule_histories()->orderBy('created_at', 'desc')->get() as $history)
                                                        <li class="{{$booking->service_schedule == $history->schedule ? 'active' : ''}}">
                                                            <div
                                                                class="timeline-date">{{ \Carbon\Carbon::parse($history->schedule)->format('d-M-Y') }}</div>
                                                            <div
                                                                class="timeline-time">{{ \Carbon\Carbon::parse($history->schedule)->format('h:i A') }}</div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </h5>
                                </div>
                            </div>
                            <h3 class="mb-3 mt-4">{{translate('Booking_Summary')}}</h3>
                            <div class="table-responsive border-bottom">
                                <table class="table text-nowrap align-right align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>{{translate('Deskripsi')}}</th>
                                        <th>{{ translate('Machine_Type') }}</th>
                                        {{-- <th>{{translate('Price')}}</th> --}}
                                        {{-- <th>{{translate('Qty')}}</th> --}}
                                        {{-- <th>{{translate('Discount')}}</th> --}}
                                        {{-- <th>{{translate('Total')}}</th> --}}
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php($sub_total=0)
                                    @foreach($booking->detail as $detail)
                                        <tr>
                                            <td class="text-wrap">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex flex-column">
                                                        @if(isset($detail->service))
                                                            <a href="{{route('provider.service.detail',[$detail->service->id])}}"
                                                            class="fw-bold">{{Str::limit($detail->service->name, 30)}}</a>
                                                            <div
                                                                class="text-capitalize">{{Str::limit($detail ? $detail->variant_key : '', 50)}}</div>
                                                            @if($detail->overall_coupon_discount_amount > 0)
                                                                <small
                                                                    class="fz-10 text-capitalize">{{translate('coupon_discount')}}
                                                                    :
                                                                    -{{with_currency_symbol($detail->overall_coupon_discount_amount)}}</small>
                                                            @endif
                                                        @else
                                                            <span
                                                                class="badge badge-pill badge-danger">{{translate('Service not available')}}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{$subCategory->name}}</td>
                                            {{-- <td>{{with_currency_symbol($detail->service_cost)}}</td> --}}
                                            {{-- <td>{{$detail->quantity}}</td> --}}
                                            {{-- <td>
                                                @if($detail?->discount_amount > 0)
                                                    {{with_currency_symbol($detail->discount_amount)}}
                                                @elseif($detail?->campaign_discount_amount > 0)
                                                    {{with_currency_symbol($detail->campaign_discount_amount)}}
                                                    <br><span
                                                        class="fz-12 text-capitalize">{{translate('campaign')}}</span>
                                                @endif
                                            </td> --}}
                                            {{-- <td>{{with_currency_symbol($detail->service_cost*$detail->quantity)}}</td> --}}
                                        </tr>
                                        @php($sub_total+=$detail->service_cost*$detail->quantity)
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="row justify-content-end mt-3">
                                <div class="col-sm-10 col-md-6 col-xl-5">
                                    <div class="table-responsive">
                                        <table class="table table-md title-color align-right w-100">
                                            <tbody>
                                            {{-- <tr>
                                                <td class="text-capitalize">{{translate('service_amount')}} <small
                                                        class="fz-12">({{translate('Vat_Excluded')}})</small></td>
                                                <td>{{with_currency_symbol($sub_total)}}</td>
                                            </tr> --}}
                                            @if ($booking->total_discount_amount > 0)
                                            <tr>
                                                <td colspan="2" class=" text-capitalize">{{translate('service_discount')}}</td>
                                                {{-- <td class=" text--end pe--4">{{with_currency_symbol($booking->total_discount_amount)}}</td> --}}
                                            </tr>
                                            @endif
                                            @if ($booking->total_coupon_discount_amount > 0)
                                            <tr>
                                                <td colspan="2" class=" text-capitalize">{{translate('warranty_discount')}}</td>
                                                {{-- <td class="text--end pe--4">{{with_currency_symbol($booking->total_coupon_discount_amount)}}</td> --}}
                                            </tr>
                                            @endif
                                            @if ($booking->total_campaign_discount_amount > 0)
                                            <tr>
                                                <td colspan="2" class=" text-capitalize">{{translate('campaign_discount')}}</td>
                                                {{-- <td class="text--end pe--4">{{with_currency_symbol($booking->total_campaign_discount_amount)}}</td> --}}
                                            </tr>
                                            @endif
                                            {{-- <tr>
                                                <td class="text-capitalize">{{translate('vat_/_tax')}}</td>
                                                <td>+{{with_currency_symbol($booking->total_tax_amount)}}</td>
                                            </tr> --}}
                                            @if ($booking->extra_fee > 0)
                                                @php($additional_charge_label_name = business_config('additional_charge_label_name', 'booking_setup')->live_values??'Fee')
                                                <tr>
                                                    <td class="text-capitalize">{{$additional_charge_label_name}}</td>
                                                    <td>{{with_currency_symbol($booking->extra_fee)}}</td>
                                                </tr>
                                            @endif

                                            {{-- <tr>
                                                <td><strong>{{translate('Grand_Total')}}</strong></td>
                                                <td>
                                                    <strong>{{with_currency_symbol($booking->total_booking_amount)}}</strong>
                                                </td>
                                            </tr> --}}
                                            {{-- <tr>
                                                <td><strong>{{translate('Grand_Total')}}</strong></td>
                                                <td>
                                                    <strong>{{with_currency_symbol($sub_total)}}</strong>
                                                </td>
                                            </tr> --}}

                                            @if ($booking->booking_partial_payments->isNotEmpty())
                                                @foreach($booking->booking_partial_payments as $partial)
                                                    <tr>
                                                        <td>{{translate('Paid_by')}} {{str_replace('_', ' ',$partial->paid_with)}}</td>
                                                        <td>{{with_currency_symbol($partial->paid_amount)}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif

                                            <?php
                                            $dueAmount = 0;

                                            if (!$booking->is_paid && $booking?->booking_partial_payments?->count() == 1) {
                                                $dueAmount = $booking->booking_partial_payments->first()?->due_amount;
                                            }

                                            if (in_array($booking->booking_status, ['pending', 'accepted', 'ongoing']) && $booking->payment_method != 'cash_after_service' && $booking->additional_charge > 0) {
                                                $dueAmount += $booking->additional_charge;
                                            }

                                            if (!$booking->is_paid && $booking->payment_method == 'cash_after_service') {
                                                $dueAmount = $sub_total;
                                            }
                                            ?>

                                            {{-- @if($dueAmount > 0)
                                                <tr>
                                                    <td>{{ translate('Due_Amount') }}</td>
                                                    <td class="text--end pe--4">{{ with_currency_symbol($dueAmount) }}</td>
                                                </tr>
                                            @endif --}}

                                            @if($booking->payment_method != 'cash_after_service' && $booking->additional_charge < 0)
                                                <tr>
                                                    <td>{{translate('Refund')}}</td>
                                                    <td>{{with_currency_symbol(abs($booking->additional_charge))}}</td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="c1">{{translate('Booking Setup')}}</h3>
                            <hr>
                            {{-- <div class="d-flex justify-content-between align-items-center gap-10 form-control"
                                 id="payment-status-div">
                                <span class="title-color">
                                    {{translate('Payment Status')}}
                                </span>

                                <div class="on-off-toggle">
                                    <input class="on-off-toggle__input switcher_input"
                                           value="{{$booking['is_paid'] ? '1' : '0'}}"
                                           {{$booking['is_paid'] ? 'checked disabled' : ''}} type="checkbox"
                                           id="payment_status"/>
                                    <label for="payment_status" class="on-off-toggle__slider">
                                        <span class="on-off-toggle__on">
                                            <span class="on-off-toggle__text">{{translate('Paid')}}</span>
                                            <span class="on-off-toggle__circle"></span>
                                        </span>
                                        <span class="on-off-toggle__off">
                                            <span class="on-off-toggle__circle"></span>
                                            <span class="on-off-toggle__text">{{translate('Unpaid')}}</span>
                                        </span>
                                    </label>
                                </div>
                            </div> --}}

                            @if ($booking['booking_status'] != 'reschedulingRequest')
                                <div class="mt-3">
                                    <select name="order_status" class="status form-select js-select" id="serviceman_assign">
                                        <option value="no_serviceman">--{{translate('Assign_Serviceman')}}--</option>
                                        @foreach($servicemen as $serviceman)
                                            <option
                                            value="{{$serviceman->id}}" {{$booking->serviceman_id == $serviceman->id ? 'selected' : ''}} >
                                            {{$serviceman->user ? Str::limit($serviceman->user->first_name.' '.$serviceman->user->last_name, 30):''}}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="mt-3">
                                {{-- <select name="order_status" class="status form-select js-select" id="booking_status">
                                    @if($booking->booking_status == 'completed')
                                        <option
                                            value="completed" {{$booking['booking_status'] == 'completed' ? 'selected' : ''}}>{{translate('Completed')}}</option>
                                    @else
                                        <option value="0">--{{translate('Booking_status')}}--</option>
                                        @if($booking->booking_status == 'pending')
                                            <option
                                                value="accepted" {{$booking['booking_status'] == 'accepted' ? 'selected' : ''}} >{{translate('Accepted')}}</option>
                                        @else
                                            @if($booking['booking_status'] == 'customerAgrees')
                                                <option
                                                    value="customerAgrees" {{$booking['booking_status'] == 'customerAgrees' ? 'selected' : ''}}>{{translate('Customer Agrees')}}</option>
                                                <option
                                                    value="completed" {{$booking['booking_status'] == 'completed' ? 'selected' : ''}}>{{translate('Completed')}}</option>

                                            @else
                                                <option
                                                    value="0"
                                                    disabled {{$booking['booking_status'] == 'accepted' ? 'selected' : ''}}>{{translate('Accepted')}}</option>
                                                <option
                                                    value="online" {{$booking['booking_status'] == 'online' ? 'selected' : ''}}>{{translate('Online')}}</option>
                                                <option
                                                    value="ongoing" {{$booking['booking_status'] == 'ongoing' ? 'selected' : ''}}>{{translate('Ongoing')}}</option>
                                                <option
                                                    value="pendingSparepart" {{$booking['booking_status'] == 'pendingSparepart' ? 'selected' : ''}}>{{translate('Pending Sparepart')}}</option>
                                            @endif
                                            @if((business_config('provider_can_cancel_booking', 'provider_config'))->live_values)
                                                <option
                                                    value="canceled" {{$booking['booking_status'] == 'canceled' ? 'selected' : ''}}>{{translate('Canceled')}}</option>
                                            @endif
                                        @endif
                                    @endif
                                </select> --}}
                                <select name="order_status" class="status form-select js-select" id="booking_status">
                                    {{-- @if ($booking['booking_status'] != 'pending' && $booking->booking_status == '')
                                    <option value="0">--{{translate('Booking_status')}}--</option>
                                    @endif --}}
                                    @if((business_config('provider_can_cancel_booking', 'provider_config'))->live_values)
                                                <option
                                                    value="canceled" {{$booking['booking_status'] == 'canceled' ? 'selected' : ''}}>{{translate('Canceled')}}</option>
                                            @endif
                                </select>
                            </div>

                            <div class="mt-3" id="change_schedule">
                                @if(!in_array($booking->booking_status,['ongoing','completed']))
                                    <input type="datetime-local" class="form-control service_schedule"
                                           name="service_schedule"
                                           value="{{$booking->service_schedule}}" id="service_schedule"
                                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                                @endif
                            </div>

                            <div class="py-3 d-flex flex-column gap-3 mb-2">
                                <div class="modal fade" id="upload_picture_modal" data-bs-backdrop="static"
                                     tabindex="-1" aria-labelledby="upload_picture_modalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header border-0">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h3 class="text-center mb-4">{{translate('Upload_Picture_Before_Completing_The')}}
                                                    <br class="d-none d-sm-block"> {{translate('Service')}} ? </h3>
                                                <form id="uploadPictureForm" name="uploadPictureForm"
                                                      enctype="multipart/form-data" action="javascript:void(0)">
                                                    @csrf
                                                    <div class="d-flex justify-content-center">
                                                        <div class="mx-auto">
                                                            <div class="d-flex flex-wrap gap-3 __new-coba"
                                                                 id="evidence-photoss"></div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-4 flex-wrap justify-content-center mt-20">
                                                        <button type="button" class="btn btn--secondary"
                                                                id="skip_button">Skip
                                                        </button>
                                                        <button type="submit"
                                                                class="btn btn--primary">{{translate('Save')}}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="otpModal" tabindex="-1" data-bs-backdrop="static"
                                     aria-labelledby="otpModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header border-0">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form class="otp-form mx-auto" id="otp_form" name="otp_form"
                                                      enctype="multipart/form-data" action="javascript:void(0)">
                                                    <h4 class="text-center mb-5">{{translate('Please Collect OTP from your customer
                                                        &
                                                        Insert Here')}}</h4>
                                                    <div
                                                        class="d-flex gap-2 gap-sm-3 align-items-end justify-content-center">
                                                        <input class="otp-field" type="number" name="otp_field[]"
                                                               maxlength="1" autofocus>
                                                        <input class="otp-field" type="number" name="otp_field[]"
                                                               maxlength="1" autocomplete="off">
                                                        <input class="otp-field" type="number" name="otp_field[]"
                                                               maxlength="1" autocomplete="off">
                                                        <input class="otp-field" type="number" name="otp_field[]"
                                                               maxlength="1" autocomplete="off">
                                                        <input class="otp-field" type="number" name="otp_field[]"
                                                               maxlength="1" autocomplete="off">
                                                        <input class="otp-field" type="number" name="otp_field[]"
                                                               maxlength="1" autocomplete="off">
                                                    </div>

                                                    <input class="otp-value" type="hidden" name="opt-value">

                                                    <div class="d-flex justify-content-between gap-2 mb-5 mt-30">
                                                        <span
                                                            class="text-muted">{{translate('Did not get any OTP')}} ?</span>
                                                        <span
                                                            class="text-muted cursor-pointer resend-otp">{{translate('Resend it')}}</span>
                                                    </div>

                                                    <div class="d-flex justify-content-center mb-4">
                                                        <button type="submit"
                                                                class="btn btn--primary">{{translate('Submit')}}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div></div>
                                @if ($booking->ongoing_photos)
                                    <div class="c1-light-bg radius-10 py-3 px-4">
                                        <div class="d-flex justify-content-start gap-2">
                                            <h4 class="mb-2">{{ translate('ongoing_Images') }}</h4>
                                        </div>

                                        <div>
                                            <div class="d-flex flex-wrap gap-3 justify-content-lg-start">
                                                @foreach ($booking->ongoing_photos ?? [] as $key => $img)
                                                <img src="{{asset('storage/app/public/booking/ongoing').'/'.$img}}" data-bs-toggle="modal" data-bs-target="#ongoing_modal" width="100"  class="max-height-100"
                                                onerror="this.src='{{asset('public/assets/provider-module')}}/img/media/info-details.png'"> @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="ongoing_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                          <div class="modal-content">
                                            <div id="carouselExampleCaptions" class="carousel slide">
                                                <div class="carousel-indicators">
                                                    @for ($i = 0; $i < count($booking->ongoing_photos ?? []); $i++)
                                                        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-current="{{ $i === 0 ? 'true' : '' }}" aria-label=""></button>
                                                    @endfor
                                                </div>
                                                <div class="carousel-inner">
                                                @foreach ($booking->ongoing_photos ?? [] as $key => $img)
                                                     <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                                                       <img src="{{asset('storage/app/public/booking/ongoing').'/'.$img}}" data-bs-toggle="modal" data-bs-target="#ongoing_modal"  class="d-block w-100"
                                                        onerror="this.src='{{asset('public/assets/provider-module')}}/img/media/info-details.png'">
                                                       <div class="carousel-caption d-none d-md-block">
                                                        {{-- <p>{{Str::limit($booking?->ongoing_address??translate('not_available'), 100)}}</p> --}}
                                                       </div>
                                                     </div>
                                                @endforeach
                                                </div>
                                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                                                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                  <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                                                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                  <span class="visually-hidden">Next</span>
                                                </button>
                                              </div>
                                          </div>
                                        </div>
                                      </div>
                                @endif

                                @if($booking->evidence_photos && $booking->booking_status == 'completed')
                                    <div class="c1-light-bg radius-10">
                                        <div
                                            class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4">
                                            <h4 class="d-flex align-items-center gap-2">
                                                <span class="material-icons title-color">image</span>
                                                {{translate('uploaded_Images')}}
                                            </h4>
                                        </div>

                                        <div class="py-3 px-4">
                                            <div class="d-flex flex-wrap gap-3 justify-content-lg-start">
                                                @foreach ($booking->evidence_photos ?? [] as $key => $img)
                                                    <img width="100" class="max-height-100" data-bs-toggle="modal"
                                                    data-bs-target="#completed_modal"
                                                         src="{{onErrorImage($img,asset('storage/app/public/booking/evidence').'/' . $img,
                                                    asset('public/assets/provider-module/img/media/info-details.png') ,
                                                    'booking/evidence/')}}">
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="completed_modal" tabindex="-1"
                                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div id="carouselExampleCaptions" class="carousel slide">
                                                <div class="carousel-indicators">
                                                    @for ($i = 0; $i < count($booking->evidence_photos ?? []); $i++)
                                                        <button type="button" data-bs-target="#carouselExampleCaptions"
                                                            data-bs-slide-to="{{ $i }}"
                                                            class="{{ $key === 0 ? 'active' : '' }}"
                                                            aria-current="{{ $key === 0 ? 'active' : '' }}"
                                                            aria-label=""></button>
                                                    @endfor
                                                </div>
                                                <div class="carousel-inner">
                                                    @foreach ($booking->evidence_photos ?? [] as $key => $img)
                                                        <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/app/public/booking/evidence') . '/' . $img }}"
                                                                data-bs-toggle="modal" data-bs-target="#ongoing_modal"
                                                                class="d-block w-100"
                                                                onerror="this.src='{{ asset('public/assets/provider-module') }}/img/media/info-details.png'">
                                                            <div class="carousel-caption d-none d-md-block">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button class="carousel-control-prev" type="button"
                                                    data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button"
                                                    data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @php($customer_name = $booking?->service_address?->contact_person_name)
                                @php($customer_phone = $booking?->service_address?->contact_person_number)

                                <div class="c1-light-bg radius-10 flex-grow-1">
                                    <div
                                        class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">person</span>
                                            {{translate('Customer_Information')}}
                                        </h4>
                                    </div>

                                    <div class="py-3 px-4">
                                        @php($customer_name = $booking?->service_address?->contact_person_name)
                                        @php($customer_phone = $booking?->service_address?->contact_person_number)

                                        <div class="media gap-2 flex-wrap">
                                            <img width="58" height="58" class="rounded-circle border border-white"
                                                 src="{{onErrorImage($booking?->customer?->profile_image,
                                                    asset('storage/app/public/user/profile_image').'/' . $booking?->customer?->profile_image,
                                                    asset('public/assets/admin-module/img/media/user.png') ,
                                                    'user/profile_image/')}}" alt="{{translate('user_image')}}">
                                            <div class="media-body">
                                                <h5 class="c1 mb-3">
                                                    <span>{{Str::limit($customer_name??'', 30)}}</span>
                                                </h5>
                                                <ul class="list-info">
                                                    @if ($customer_phone)
                                                        <li>
                                                            <span class="material-icons">phone_iphone</span>
                                                            <a href="https://wa.me/{{$customer_phone}}">{{$customer_phone}}</a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <span class="material-icons">map</span>
                                                        <p>{{Str::limit($booking?->service_address?->address??translate('not_available'), 100)}}</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="c1-light-bg radius-10 flex-grow-1">
                                    <div
                                        class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">person</span>
                                            {{translate('Serviceman_Information')}}
                                        </h4>
                                    </div>
                                    @if(isset($booking->serviceman))
                                        <div class="py-3 px-4">
                                            <div class="media gap-2 flex-wrap">
                                                <img width="58" height="58" class="rounded-circle border border-white"
                                                     src="{{onErrorImage($booking?->serviceman?->user?->profile_image,asset('storage/app/public/serviceman/profile').'/' . $booking?->serviceman?->user?->profile_image,
                                                            asset('public/assets/admin-module/img/media/user.png') ,
                                                            'serviceman/profile/')}}" alt="{{translate('serviceman')}}">
                                                <div class="media-body">
                                                    <h5 class="c1 mb-3">{{Str::limit($booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->first_name.' '.$booking->serviceman->user->last_name:'', 30)}}</h5>
                                                    <ul class="list-info">
                                                        <li>
                                                            <span class="material-icons">phone_iphone</span>
                                                            <a href="tel:{{$booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->phone:''}}">
                                                                {{$booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->phone:''}}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-2 mt-30 align-items-center">
                                            <span class="material-icons text-muted fs-2">account_circle</span>
                                            <p class="text-muted text-center fw-medium mb-3">{{translate('No Serviceman Information')}}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="c1-light-bg radius-10 flex-grow-1">
                                    <div
                                        class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">notes</span>
                                            {{translate('Notes')}}
                                        </h4>
                                    </div>
                                    @if(isset($booking->serviceman_notes))
                                    <div class="py-3 px-4">
                                        <div class="media gap-2 flex-wrap">
                                            <div class="media-body">
                                                <ul class="list-info">
                                                    <li>
                                                        <span class="wrap-text"> {{$booking->serviceman_notes}} </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                        <div class="d-flex flex-column gap-2 mt-30 align-items-center">
                                            <span class="material-icons text-muted fs-2">note</span>
                                            <p class="text-muted text-center fw-medium mb-3">{{translate('Belum ada catatan dari teknisi')}}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- information previous service --}}
                                <h3 class="c1">{{ translate('Previous Service') }}</h3>
                                <hr>

                                @foreach ($booking->reschedule as $previous)
                                    <div class="py-3 d-flex flex-column gap-3 mb-2">
                                        @if ($previous->ongoing_photos)
                                            <div class="c1-light-bg radius-10 py-3 px-4">
                                                <div class="d-flex justify-content-start gap-2">
                                                    <h4 class="mb-2">{{ translate('Foto berlangsung') }}</h4>
                                                </div>

                                                <div>
                                                    <div class="d-flex flex-wrap gap-3 justify-content-lg-start">
                                                        @foreach ($previous->ongoing_photos ?? [] as $key => $img)
                                                        <img width="100" class="max-height-100"
                                                        src="{{ onErrorImage(
                                                                    $img,
                                                                    asset('storage/app/public/booking/ongoing') . '/' . $img,
                                                                    asset('public/assets/admin-module/img/media/user.png'),
                                                                    'booking/evidence/',
                                                                    ) }}"
                                                                alt="{{ translate('ongoing-photo') }}">
                                                                @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            @if ($previous->evidence_photos)
                                            <div class="c1-light-bg radius-10 py-3 px-4">
                                                <div class="d-flex justify-content-start gap-2">
                                                    <h4 class="mb-2">{{ translate('Foto selesai') }}</h4>
                                                </div>

                                                <div>
                                                    <div class="d-flex flex-wrap gap-3 justify-content-lg-start">
                                                        @foreach ($previous->evidence_photos ?? [] as $key => $img)
                                                        <img width="100" class="max-height-100"
                                                        src="{{ onErrorImage(
                                                                    $img,
                                                                    asset('storage/app/public/booking/evidence') . '/' . $img,
                                                                    asset('public/assets/admin-module/img/media/user.png'),
                                                                    'booking/evidence/',
                                                                ) }}"
                                                                alt="{{ translate('evidence-photo') }}">
                                                                @endforeach
                                                            </div>
                                                        </div>
                                            </div>
                                            @endif

                                            <div class="c1-light-bg radius-10 serviceman-information">
                                                <div
                                                class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4 mb-2">
                                                <h4 class="d-flex align-items-center gap-2">
                                                    <span class="material-icons title-color">person</span>
                                                    {{ translate('informasi Teknisi') }}
                                                </h4>
                                            </div>
                                            @if (isset($previous->serviceman))
                                                <div class="py-3 px-4">
                                                    <div class="media gap-2 flex-wrap">
                                                        <img width="58" height="58" class="rounded-circle border border-white"
                                                            src="{{ onErrorImage(
                                                                $previous?->serviceman?->user?->profile_image,
                                                                asset('storage/app/public/serviceman/profile') . '/' . $previous?->serviceman?->user->profile_image,
                                                                asset('public/assets/admin-module/img/media/user.png'),
                                                                'serviceman/profile/',
                                                                ) }}"
                                                            alt="{{ translate('serviceman') }}">
                                                            <div class="media-body">
                                                                <h5 class="c1 mb-3">
                                                                {{ Str::limit($previous->serviceman && $previous->serviceman->user ? $previous->serviceman->user->first_name . ' ' . $previous->serviceman->user->last_name : '', 30) }}
                                                            </h5>
                                                            <ul class="list-info">
                                                                <li>
                                                                    <span class="material-icons">phone_iphone</span>
                                                                    <a
                                                                    href="tel:{{ $previous->serviceman && $previous->serviceman->user ? $previous->serviceman->user->phone : '' }}">
                                                                    {{ $previous->serviceman && $previous->serviceman->user ? $previous->serviceman->user->phone : '' }}
                                                                </a>
                                                            </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="d-flex flex-column gap-2 mt-30 align-items-center">
                                                    <span class="material-icons text-muted fs-2">account_circle</span>
                                                    <p class="text-muted text-center fw-medium mb-3">
                                                        {{ translate('Belum ada teknisi') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="c1-light-bg radius-10 flex-grow-1">
                                            <div
                                                class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4">
                                                <h4 class="d-flex align-items-center gap-2">
                                                    <span class="material-icons title-color">notes</span>
                                                    {{translate('Catatan')}}
                                                </h4>
                                            </div>
                                            @if(isset($previous->serviceman_note))
                                            <div class="py-3 px-4">
                                                <div class="media gap-2 flex-wrap">
                                                    <div class="media-body">
                                                        <ul class="list-info">
                                                            <li>
                                                                <span class="wrap-text"> {{$previous->serviceman_note}} </span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                                <div class="d-flex flex-column gap-2 mt-30 align-items-center">
                                                    <span class="material-icons text-muted fs-2">note</span>
                                                    <p class="text-muted text-center fw-medium mb-3">{{translate('Belum ada catatan dari teknisi')}}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeScheduleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         aria-labelledby="changeScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeScheduleModalLabel">{{translate('Change_Booking_Schedule')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="datetime-local" id="service_schedule" name="service_schedule" class="form-control"
                           value="{{$booking->service_schedule}}">
                </div>
                <div class="p-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="button" class="btn btn-primary"
                            id="service_schedule__submit">{{translate('Submit')}}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="serviceAddressModal--{{$booking['id']}}" tabindex="-1"
         aria-labelledby="serviceAddressModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{route('provider.booking.service_address_update', [$booking['id']])}}">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body m-4">
                        <div class="d-flex flex-column gap-2 align-items-center">
                            <img width="75" class="mb-2"
                                 src="{{asset('public/assets/provider-module')}}/img/media/ignore-request.png"
                                 alt="">
                            <h3>{{translate('Update customer service address')}}</h3>

                            <div class="row mt-4">
                                @forelse($customerAddresses as $item)
                                    <div class="form-check col-md-6 col-12">
                                        <input class="form-check-input" type="radio" name="service_address_id"
                                               id="customerAddress-{{$item->id}}"
                                               value="{{$item->id}}" {{$booking->service_address_id == $item->id ? 'checked' : null}}>
                                        <label class="form-check-label" for="customerAddress-{{$item->id}}">
                                            {{$item->address_label}} <br>
                                            <small>{{$item->address}}</small>
                                        </label>

                                    </div>
                                @empty
                                    <span>{{translate('No address available')}}</span>
                                @endforelse
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center gap-3 border-0 pt-0 pb-4">
                        <button type="button" class="btn btn--secondary" data-bs-dismiss="modal" aria-label="Close">
                            {{translate('Cancel')}}</button>
                        <button type="{{isset($customerAddresses[0]) ? 'submit' : 'button'}}"
                                class="btn btn--primary">{{translate('Update')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('bookingmodule::provider.booking.partials.details._service-modal')
@endsection

@push('script')

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('booking_status');
    const currentStatus = '{{$booking->booking_status}}';

    function addOption(value, text) {
        const option = document.createElement('option');
        option.value = value;
        option.text = text;
        selectElement.appendChild(option);
    }
    if (currentStatus === 'pending') {
        addOption('pending', '{{ translate("--booking status--") }}');
        addOption('accepted', '{{ translate("Accepted") }}');
    }
    if (currentStatus === 'accepted') {
        addOption('accepted', '{{ translate("Accepted") }}');
        addOption('online', '{{ translate("Online") }}');
        addOption('ongoing', '{{ translate("Ongoing") }}');
        addOption('pendingSparepart', '{{ translate("Pending Sparepart") }}');
    }
    if (currentStatus === 'online') {
        addOption('online', '{{ translate("Online") }}');
        addOption('ongoing', '{{ translate("Ongoing") }}');
        addOption('pendingSparepart', '{{ translate("Pending Sparepart") }}');
        addOption('servicemanDone', '{{ translate("Serviceman Done") }}');
    }
    if (currentStatus === 'ongoing') {
        addOption('ongoing', '{{ translate("Ongoing") }}');
        addOption('pendingSparepart', '{{ translate("Pending Sparepart") }}');
        addOption('servicemanDone', '{{ translate("Serviceman Done") }}');
    }
    if (currentStatus === 'pendingSparepart') {
        addOption('pendingSparepart', '{{ translate("Pending Sparepart") }}');
        addOption('accepted', '{{ translate("Assign To Serviceman") }}');
    }
    if (currentStatus === 'servicemanDone') {
        addOption('servicemanDone', '{{ translate("Serviceman Done") }}');
        addOption('customerAgrees', '{{ translate("Customer Agrees") }}');
    }
    if (currentStatus === 'customerAgrees') {
        addOption('customerAgrees', '{{ translate("Customer Agrees") }}');
        addOption('completed', '{{ translate("completed") }}');
    }
    if (currentStatus === 'reschedulingRequest') {
        addOption('reschedulingRequest', '{{ translate("Rescheduling Request") }}');
        addOption('reschedule', '{{ translate("Reschedule") }}');
    }
    if (currentStatus === 'reschedule') {
        addOption('reschedule', '{{ translate("Reschedule") }}');
        addOption('reschedulingRequest', '{{ translate("Rescheduling Request") }}');
    }
    if (currentStatus === 'canceled') {
        addOption('canceled', '{{ translate("canceled") }}');
    }




    // Set selected option based on the current status
    selectElement.value = currentStatus;
});
</script>

    <script src="{{ asset('public/assets/admin-module/js/spartan-multi-image-picker.js') }}"></script>
    <script type="text/javascript">
        "use strict";

        $(function () {

            $('.service_schedule').on('input', function () {
                service_schedule_update();
            });

            $("#evidence-photoss").spartanMultiImagePicker({
                fieldName: 'evidence_photos[]',
                maxCount: 6,
                rowHeight: '100px !important',
                groupClassName: 'spartan_item_wrapper min-w-100px max-w-100px',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('public/assets/admin-module')}}/img/media/banner-upload-file.png',
                    width: '100%',
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error(
                        "{{ translate('messages.please_only_input_png_or_jpg_type_file') }}", {
                            CloseButton: true,
                            ProgressBar: true
                        });
                },
                onSizeErr: function (index, file) {
                    toastr.error("{{ translate('messages.file_size_too_big') }}", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        $(document).ready(function () {
            $(".otp-form *:input[type!=hidden]:first").focus();
            let otp_fields = $(".otp-form .otp-field"),
                otp_value_field = $(".otp-form .otp-value");
            otp_fields.first().focus();
            otp_fields
                .on("input", function (e) {
                    $(this).val(
                        $(this)
                            .val()
                            .replace(/[^0-9]/g, "")
                    );
                    let opt_value = "";
                    otp_fields.each(function () {
                        let field_value = $(this).val();
                        if (field_value != "") opt_value += field_value;
                    });
                    otp_value_field.val(opt_value);
                })
                .on("keyup", function (e) {
                    let key = e.keyCode || e.charCode;
                    if (key == 8 || key == 46 || key == 37 || key == 40) {
                        // Backspace or Delete or Left Arrow or Down Arrow
                        $(this).prev().focus();
                    } else if (key == 38 || key == 39 || $(this).val() != "") {
                        // Right Arrow or Top Arrow or Value not empty
                        $(this).next().focus();
                    }
                })
                .on("paste", function (e) {
                    let paste_data = e.originalEvent.clipboardData.getData("text");
                    let paste_data_splitted = paste_data.split("");
                    $.each(paste_data_splitted, function (index, value) {
                        otp_fields.eq(index).val(value);
                    });
                });
        });

        $('#payment_status').on('click', function () {
            let paymentStatus = $(this).is(':checked') === true ? 1 : 0;
            payment_status_change(paymentStatus)
        })

        $('.resend-otp').on('click', function () {
            resend_otp()
        })

        function payment_status_change(paymentStatus) {
            let route = '{{route('provider.booking.payment_update',[$booking->id])}}' + '?paymentStatus=' + paymentStatus;
            update_booking_details(route, '{{translate('want_to_update_status')}}', 'payment_status', paymentStatus);
        }

    </script>


    <script>
        "use strict";

        @if($booking->booking_status != 'customerAgrees')
        $(document).ready(function () {
            selectElementVisibility('payment_status', false);
            $("#payment-status-div").addClass('d-none');
        });
        @endif

        @if($booking->booking_status == 'pending')
        $(document).ready(function () {
            selectElementVisibility('serviceman_assign', false);
            $("#change_schedule").addClass('d-none');
        });
        @endif

        @if($booking->booking_status == 'completed' && !$booking->serviceman)
        $(document).ready(function () {
            selectElementVisibility('serviceman_assign', false);
        });
        @endif

        $("#service_schedule__submit").click(function () {
            var service_schedule = $("#service_schedule").val();
            var route = '{{route('provider.booking.schedule_update',[$booking->id])}}' + '?service_schedule=' + service_schedule;

            update_booking_details(route, '{{translate('want_to_update_status')}}', 'service_schedule__submit', service_schedule);
        });

        $("#booking_status").change(function () {
            var booking_status = $("#booking_status option:selected").val();
            if (parseInt(booking_status) !== 0) {
                var route = '{{route('provider.booking.status_update',[$booking->id])}}' + '?booking_status=' + booking_status;
                update_booking_details(route, '{{translate('want_to_update_status')}}', 'booking_status', booking_status, 'PUT');
            } else {
                toastr.error('{{translate('choose_proper_status')}}');
            }
        });

        $("#serviceman_assign").change(function () {
            var serviceman_id = $("#serviceman_assign option:selected").val();
            if (serviceman_id !== 'no_serviceman') {
                var route = '{{route('provider.booking.serviceman_update',[$booking->id])}}' + '?serviceman_id=' + serviceman_id;

                update_booking_details(route, '{{translate('want_to_assign_the_serviceman')}}?', 'serviceman_assign', serviceman_id);
            } else {
                toastr.error('{{translate('choose_proper_serviceman')}}');
            }
        });

        $("#payment_status").change(function () {
            var payment_status = $("#payment_status option:selected").val();
            if (parseInt(payment_status) !== 0) {
                var route = '{{route('provider.booking.payment_update',[$booking->id])}}' + '?payment_status=' + payment_status;

                update_booking_details(route, '{{translate('want_to_update_status')}}', 'payment_status', payment_status);
            } else {
                toastr.error('{{translate('choose_proper_payment_status')}}');
            }
        });


        function update_booking_details(route, message, componentId, updatedValue, type = 'get') {
            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                cancelButtonText: '{{translate('Cancel')}}',
                confirmButtonText: '{{translate('Yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    let evidence_status = {{ (business_config('service_complete_photo_evidence', 'booking_setup'))->live_values == 1 ? 'true' : 'false' }};
                    let booking_otp_status = {{ (business_config('booking_otp', 'booking_setup'))->live_values == 1 ? 'true' : 'false' }};
                    if (componentId === 'booking_status' && updatedValue === 'completed') {
                        if (evidence_status) {
                            $('#upload_picture_modal').modal('show');
                            $('#uploadPictureForm').on('submit', function (e) {
                                e.preventDefault();

                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });
                                var formData = new FormData(this);

                                $.ajax({
                                    url: "{{route('provider.booking.evidence_photos_upload',[$booking->id])}}",
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    type: 'POST',
                                    success: function (response) {
                                        toastr.success(response.message, {
                                            CloseButton: true,
                                            ProgressBar: true
                                        });
                                        if (booking_otp_status) {
                                            $('#upload_picture_modal').modal('hide');
                                            open_otp_modal();
                                            $('#otp_form').on('submit', function (e) {
                                                e.preventDefault();
                                                var formData = $(this).serialize(); // Serialize the form data
                                                proceed_with_main_ajax_request(route, componentId, updatedValue, 'PUT', formData);
                                            });
                                        } else {
                                            proceed_with_main_ajax_request(route, componentId, updatedValue, 'PUT');
                                        }
                                    },
                                    error: function (error) {
                                    }
                                });
                            });

                            $('#skip_button').on('click', function (e) {
                                $('#otp_form').off('submit');

                                if (booking_otp_status) {
                                    $('#upload_picture_modal').modal('hide');
                                    open_otp_modal();
                                    $('#otp_form').on('submit', function (e) {
                                        e.preventDefault();
                                        var formData = $(this).serialize();
                                        proceed_with_main_ajax_request(route, componentId, updatedValue, 'PUT', formData);
                                    });
                                } else {
                                    proceed_with_main_ajax_request(route, componentId, updatedValue, 'PUT');
                                }
                            });


                        } else if (booking_otp_status) {
                            open_otp_modal();
                            $('#otp_form').on('submit', function (e) {
                                e.preventDefault();
                                var formData = $(this).serialize();
                                proceed_with_main_ajax_request(route, componentId, updatedValue, 'PUT', formData);
                            });
                        } else {
                            proceed_with_main_ajax_request(route, componentId, updatedValue, 'PUT');
                        }
                    } else {
                        proceed_with_main_ajax_request(route, componentId, updatedValue, 'PUT');
                    }
                }
            });
        }

        function proceed_with_main_ajax_request(route, componentId, updatedValue, type = 'get', formData = null) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: route,
                type: type,
                dataType: 'json',
                data: formData,
                beforeSend: function () {
                },
                success: function (data) {
                    update_component(componentId, updatedValue);
                    toastr.success(data.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                },
                complete: function () {
                },
            });
        }

        function open_otp_modal() {
            $('#otpModal').modal('show');
            $('.otp-field:first').focus();
        }

        function update_component(componentId, updatedValue) {

            if (componentId === 'booking_status') {
                if (updatedValue === 'accepted') {
                    location.reload();
                }

                $("#booking_status__span").html(updatedValue);

                selectElementVisibility('serviceman_assign', true);
                selectElementVisibility('payment_status', true);
                if ($("#change_schedule").hasClass('d-none')) {
                    $("#change_schedule").removeClass('d-none');
                }

            } else if (componentId === 'payment_status') {
                $("#payment_status__span").html(updatedValue);
                if (updatedValue === 'paid') {
                    $("#payment_status__span").addClass('text-success').removeClass('text-danger');
                } else if (updatedValue === 'unpaid') {
                    $("#payment_status__span").addClass('text-danger').removeClass('text-success');
                }

            } else if (componentId === 'service_schedule__submit') {
                $('#changeScheduleModal').modal('hide');
                let date = new Date(updatedValue);
                $('#service_schedule__span').html(date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear() + " " +
                    date.getHours() + ":" + date.getMinutes());

            }
        }

        function selectElementVisibility(componentId, visibility) {
            if (visibility === true) {
                $('#' + componentId).next(".select2-container").show();
            } else if (visibility === false) {
                $('#' + componentId).next(".select2-container").hide();
            } else {
            }
        }

        function service_schedule_update() {
            var service_schedule = $("#service_schedule").val();
            var route = '{{route('provider.booking.schedule_update',[$booking->id])}}' + '?service_schedule=' + service_schedule;

            update_booking_details(route, '{{translate('want_to_update_the_booking_schedule')}}', 'service_schedule', service_schedule);
        }

    </script>

    <script>
        $(document).ready(function () {
            $('#category_selector__select').select2({dropdownParent: "#serviceUpdateModal--{{$booking['id']}}"});
            $('#sub_category_selector__select').select2({dropdownParent: "#serviceUpdateModal--{{$booking['id']}}"});
            $('#service_selector__select').select2({dropdownParent: "#serviceUpdateModal--{{$booking['id']}}"});
            $('#service_variation_selector__select').select2({dropdownParent: "#serviceUpdateModal--{{$booking['id']}}"});
        });

        $("#service_selector__select").on('change', function () {
            $("#service_variation_selector__select").html('<option value="" selected disabled>{{translate('Select Service Variant')}}</option>');

            const serviceId = this.value;
            const route = '{{route('provider.booking.service.ajax-get-variant')}}' + '?service_id=' + serviceId + '&zone_id=' + "{{$booking->zone_id}}";

            $.get({
                url: route,
                dataType: 'json',
                data: {},
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (response) {
                    var selectString = '<option value="" selected disabled>{{translate('Select Service Variant')}}</option>';
                    response.content.forEach((item) => {
                        selectString += `<option value="${item.variant_key}">${item.variant}</option>`;
                    });
                    $("#service_variation_selector__select").html(selectString)
                },
                complete: function () {
                    $('.preloader').hide();
                },
                error: function () {
                    toastr.error('{{translate('Failed to load')}}')
                }
            });
        })

        $("#serviceUpdateModal--{{$booking['id']}}").on('hidden.bs.modal', function () {
            $('#service_selector__select').prop('selectedIndex', 0);
            $("#service_variation_selector__select").html('<option value="" selected disabled>{{translate('Select Service Variant')}}</option>');
            $("#service_quantity").val('');
        });

        $("#add-service").on('click', function () {
            const service_id = $("[name='service_id']").val();
            const variant_key = $("[name='variant_key']").val();
            const quantity = parseInt($("[name='service_quantity']").val());
            const zone_id = '{{$booking->zone_id}}';


            if (service_id === '' || service_id === null) {
                toastr.error('{{translate('Select a service')}}', {CloseButton: true, ProgressBar: true});
                return;
            } else if (variant_key === '' || variant_key === null) {
                toastr.error('{{translate('Select a variation')}}', {CloseButton: true, ProgressBar: true});
                return;
            } else if (quantity < 1) {
                toastr.error('{{translate('Quantity must not be empty')}}', {CloseButton: true, ProgressBar: true});
                return;
            }

            let variant_key_array = [];
            $('input[name="variant_keys[]"]').each(function () {
                variant_key_array.push($(this).val());
            });

            if (variant_key_array.includes(variant_key)) {
                const decimal_point = parseInt('{{(business_config('currency_decimal_point', 'business_information'))->live_values ?? 2}}');

                const old_qty = parseInt($(`#qty-${variant_key}`).val());
                const updated_qty = old_qty + quantity;

                const old_total_cost = parseFloat($(`#total-cost-${variant_key}`).text());
                const updated_total_cost = ((old_total_cost * updated_qty) / old_qty).toFixed(decimal_point);

                const old_discount_amount = parseFloat($(`#discount-amount-${variant_key}`).text());
                const updated_discount_amount = ((old_discount_amount * updated_qty) / old_qty).toFixed(decimal_point);


                $(`#qty-${variant_key}`).val(updated_qty);
                $(`#total-cost-${variant_key}`).text(updated_total_cost);
                $(`#discount-amount-${variant_key}`).text(updated_discount_amount);

                toastr.success('{{translate('Added successfully')}}', {CloseButton: true, ProgressBar: true});
                return;
            }

            let query_string = 'service_id=' + service_id + '&variant_key=' + variant_key + '&quantity=' + quantity + '&zone_id=' + zone_id;
            $.ajax({
                type: 'GET',
                url: "{{route('provider.booking.service.ajax-get-service-info')}}" + '?' + query_string,
                data: {},
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (response) {
                    $("#service-edit-tbody").append(response.view);
                    toastr.success('{{translate('Added successfully')}}', {CloseButton: true, ProgressBar: true});
                },
                complete: function () {
                    $('.preloader').hide();
                },
            });
        })

        function removeServiceRow(row) {
            const row_count = $('#service-edit-tbody tr').length;
            if (row_count <= 1) {
                toastr.error('{{translate('Can not remove the only service')}}');
                return;
            }

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: '{{translate('want to remove the service from the booking')}}',
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--c2)',
                confirmButtonColor: 'var(--c1)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $(`#${row}`).remove();
                }
            })
        }
    </script>

    <script>
        function resend_otp() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('provider.booking.otp.resend')}}",
                dataType: 'json',
                data: {
                    'booking_id': '{{$booking->id}}'
                },
                beforeSend: function () {
                },
                success: function (data) {
                    toastr.success(data.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                complete: function () {
                },
            });
        }
    </script>
@endpush
