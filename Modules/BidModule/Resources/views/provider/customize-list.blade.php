@extends('providermanagement::layouts.master')

@section('title', translate('Request_List'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-wrap mb-3">
                    <h2 class="page-title">{{translate('Customized Booking Requests')}}</h2>
                </div>

                <div
                    class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                    <ul class="nav nav--tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{$custom_request_status=='Pending'?'active':''}}"
                               href="{{url()->current()}}?custom_request_status=Pending">{{translate('new')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$custom_request_status=='Done'?'active':''}}"
                               href="{{url()->current()}}?custom_request_status=Done">{{translate('done')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$custom_request_status=='Reject'?'active':''}}"
                               href="{{url()->current()}}?custom_request_status=Reject">{{translate('Batal')}}</a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link {{$type=='new_booking_request'?'active':''}}"
                               href="{{url()->current()}}?type=new_booking_request">{{translate('No-Bid Request Yet')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$type=='placed_offer'?'active':''}}"
                               href="{{url()->current()}}?type=placed_offer">{{translate('Already Bid Requested')}}</a>
                        </li> --}}
                    </ul>

                    <div class="d-flex gap-2 fw-medium">
                        <span class="opacity-75">{{translate('Total Customized Booking')}} : </span>
                        <span class="title-color">{{$customRequest->total()}}</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                            <form action="{{url()->current()}}?custom_request_status={{$custom_request_status}}" method="POST"
                                  class="search-form search-form_style-two">
                                @csrf
                                <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                    <input type="search" class="theme-input-style search-form__input fz-10"
                                           name="search"
                                           value="{{$search??''}}"
                                           placeholder="{{translate('Search')}}">
                                </div>
                                <button type="submit" class="btn btn--primary text-capitalize">
                                    {{translate('Search')}}</button>
                            </form>

                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="dropdown">
                                    <button type="button"
                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                        <span class="material-icons">file_download</span> {{translate('download')}}
                                    </button>
                                    {{-- <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                        <li><a class="dropdown-item"
                                               href="{{route('admin.booking.post.export', ['type'=>$type, 'search' => $search??''])}}">{{translate('Excel')}}</a>
                                        </li>
                                    </ul> --}}
                                </div>

                            </div>
                        </div>

                        <div class="select-table-wrap">
                            <div
                                class="multiple-select-actions gap-3 flex-wrap align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-wrap gap-2 gap-lg-4">
                                    <div class="ms-sm-1">
                                        <input type="checkbox" class="multi-checker">
                                    </div>
                                    <p><span class="checked-count">2</span> {{translate('Item_Selected')}}</p>
                                </div>

                                <div class="d-flex align-items-center flex-wrap gap-3">
                                    <button class="btn btn--danger" id="multi-remove">{{translate('Delete')}}</button>
                                </div>
                            </div>
                            <div class="table-responsive position-relative">
                                <table class="table align-middle multi-select-table multi-select-table-booking">
                                    <thead>
                                    <tr>
                                        {{-- @if($type == 'new_booking_request')
                                            <th></th>
                                        @endif
                                        @if($type != 'new_booking_request')
                                            <th>{{translate('Booking ID')}}</th>
                                        @endif --}}
                                        <th>{{translate('Customer Info')}}</th>
                                        <th>{{translate('Booking Request Time')}}</th>
                                        {{-- <th>{{translate('Service Time')}}</th> --}}
                                        <th>{{translate('Machine Request')}}</th>
                                        <th>{{translate('Description')}}</th>
                                        <th class="text-center">{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($customRequest as $key=>$post)
                                        <tr>
                                            <td>
                                                @if($post->user)
                                                    <div>
                                                        <div class="customer-name fw-medium">
                                                            {{$post->user?->first_name.' '.$post->user?->last_name}}
                                                        </div>
                                                        <a href="tel:{{$post->user?->phone}}"
                                                           class="fs-12">{{$post->user?->phone}}</a>
                                                    </div>
                                                @else
                                                    <div><small
                                                            class="disabled">{{translate('Customer not available')}}</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{$post->created_at->format('Y-m-d')}}</div>
                                                    <div>{{$post->created_at->format('h:ia')}}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($post->machine_name)
                                                    {{$post->machine_name}}
                                                @else
                                                    <div><small
                                                            class="disabled">{{translate('Category not available')}}</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{Str::limit($post->description,20)}} </td>
                                            <td class="d-flex gap-2 justify-content-center">
                                                <a href="{{route('provider.booking.custom-request.details',[$post->id])}}"
                                                   type="button"
                                                   class="action-btn btn--light-primary fw-medium text-capitalize fz-14" style="--size: 30px">
                                                    <span class="material-icons">visibility</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="7">{{translate('No data available')}}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            {!! $customRequest->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        $('#multi-remove').on('click', function () {
            var request_ids = [];
            $('input:checkbox.multi-check').each(function () {
                if (this.checked) {
                    request_ids.push($(this).val());
                }
            });

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: "{{translate('Do you really want to remove the selected requests')}}?",
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
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.booking.post.multi-remove')}}",
                        data: {
                            post_ids: request_ids,
                        },
                        type: 'post',
                        success: function (response) {
                            toastr.success(response.message)
                            setTimeout(location.reload.bind(location), 1000);
                        },
                        error: function () {

                        }
                    });
                }
            })

        });
    </script>
@endpush
