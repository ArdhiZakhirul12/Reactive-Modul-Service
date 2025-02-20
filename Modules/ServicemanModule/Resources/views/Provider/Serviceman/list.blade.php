@extends('providermanagement::layouts.master')

@section('title',translate('Serviceman_List'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Serviceman_List')}}</h2>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$status=='all'?'active':''}}"
                                   href="{{url()->current()}}?status=all">{{translate('All')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='active'?'active':''}}"
                                   href="{{url()->current()}}?status=active">{{translate('Active')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='inactive'?'active':''}}"
                                   href="{{url()->current()}}?status=inactive">{{translate('Inactive')}}</a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Serviceman')}}:</span>
                            <span class="title-color">{{$servicemen->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form action="{{url()->current()}}?status={{$status}}"
                                              class="search-form search-form_style-two"
                                              method="POST">
                                            @csrf
                                            <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                                <input type="search" class="theme-input-style search-form__input"
                                                       value="{{$search}}" name="search"
                                                       placeholder="{{translate('search_here')}}">
                                            </div>
                                            <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                        </form>

                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <div class="dropdown">
                                                <button type="button"
                                                        class="btn btn--secondary text-capitalize dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                    <span
                                                        class="material-icons">file_download</span> {{translate('download')}}
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                    <li>
                                                        <a class="dropdown-item" href="{{route('provider.serviceman.download',['status' => $status, 'search'=>$search])}}">
                                                            {{translate('excel')}}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example" class="table align-middle">
                                            <thead>
                                            <tr>
                                                <th>{{translate('SL')}}</th>
                                                <th>{{translate('Name')}}</th>
                                                <th>{{translate('Contact_Info')}}</th>
                                                <th>{{translate('Status')}}</th>
                                                <th>{{translate('Action')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($servicemen as $key=>$serviceman)
                                                <tr>
                                                    <td>{{$servicemen->firstitem()+$key}}</td>
                                                    <td>
                                                        <a href="{{route('provider.serviceman.show', [$serviceman->serviceman->id])}}">
                                                            {{Str::limit($serviceman->first_name, 25)}} {{Str::limit($serviceman->last_name, 15)}}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        {{$serviceman->email}} <br/>
                                                        {{$serviceman->phone}}
                                                    </td>
                                                    <td>
                                                        <label class="switcher">
                                                            <input class="switcher_input route-alert"
                                                                   data-route="{{route('provider.serviceman.status-update',[$serviceman->id])}}"
                                                                   data-message="{{translate('want_to_update_status')}}"
                                                                   type="checkbox" {{$serviceman->is_active?'checked':''}}>
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <div class="table-actions">
                                                            <a href="{{route('provider.serviceman.edit', [$serviceman->serviceman->id])}}"
                                                               class="action-btn btn--light-primary fw-medium text-capitalize fz-14" style="--size: 30px">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                            <a href="{{route('provider.serviceman.show', [$serviceman->serviceman->id])}}"
                                                               class="action-btn btn--light-primary" style="--size: 30px">
                                                                <span class="material-icons">visibility</span>
                                                            </a>
                                                            <button type="button"
                                                                    data-id="delete-{{$serviceman->serviceman->id}}"
                                                                    data-message="{{translate('want_to_delete_this_serviceman')}}?"
                                                                    class="action-btn btn--danger form-alert" style="--size: 30px">
                                                                <span class="material-symbols-outlined">delete</span>
                                                            </button>
                                                            <form
                                                                action="{{route('provider.serviceman.delete', [$serviceman->serviceman->id])}}"
                                                                method="post"
                                                                id="delete-{{$serviceman->serviceman->id}}"
                                                                class="hidden">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $servicemen->links() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    "use strict"
    $(document).ready(function() {
        $('.js-select').select2();

        $('#example').DataTable({
            "info" : false,
            "ordering": true,
            "paging": false,
            
        })
    });

</script>
<script src="{{ asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('public/assets/admin-module/plugins/dataTables/dataTables.select.min.js') }}"></script>

@endpush
