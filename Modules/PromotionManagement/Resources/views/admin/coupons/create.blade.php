@extends('adminmodule::layouts.master')

@section('title',translate('add_new_warranty'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('add_new_warranty')}}</h2>
                    </div>
                    <div class="card">
                        <div class="card-body p-30">
                            <form action="{{route('admin.coupon.store')}}" method="POST">
                                @csrf
                                @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                                @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                @if($language)
                                    <ul class="nav nav-tabs mb-4">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active"
                                               href="#"
                                               id="default-link">{{translate('default')}}</a>
                                        </li>
                                        @foreach ($language?->live_values as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link"
                                                   href="#"
                                                   id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <div class="discount-type">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-30">
                                                <select class="js-select theme-input-style w-100" name="coupon_type"
                                                        id="coupon-type" required>
                                                    <option selected
                                                            disabled>{{translate('select_warranty_type')}}</option>
                                                    @foreach(COUPON_TYPES as $index=>$coupon_type)
                                                        <option value="{{$index}}">{{$coupon_type}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-30">
                                                <div class="form-floating form-floating__icon">
                                                    <input type="text" class="form-control" name="coupon_code"
                                                           placeholder="{{translate('warranty_code')}}" required>
                                                    <label>{{translate('warranty_code')}}</label>
                                                    <span class="material-icons">subtitles</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 d-none" id="customer-select__div">
                                            <div class="mb-30">
                                                <select class="js-select theme-input-style w-100" id="customer-select"
                                                        name="customer_user_ids[]" multiple>
                                                    @foreach($customers as $key=>$customer)
                                                        <option
                                                            value="{{$customer->id}}">{{$customer->first_name .' '. $customer->last_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">{{translate('warranty_type')}}</div>
                                    <div class="d-flex flex-wrap align-items-center gap-4 mb-30">
                                        <div class="custom-radio">
                                            <input type="radio" id="category" name="discount_type" value="category"
                                                   checked>
                                            <label for="category">{{translate('machine_category_wise')}}</label>
                                        </div>
                                        <div class="custom-radio">
                                            <input type="radio" id="service" name="discount_type" value="service">
                                            <label for="service">{{translate('service_wise')}}</label>
                                        </div>
                                        <div class="custom-radio">
                                            <input type="radio" id="mixed" name="discount_type" value="mixed">
                                            <label for="mixed">{{translate('mixed')}}</label>
                                        </div>
                                    </div>
                                    @if ($language)
                                        <div class="form-floating form-floating__icon mb-30 lang-form" id="default-form">
                                            <input type="text" name="discount_title[]" class="form-control"
                                                   placeholder="{{translate('discount_title')}}" required>
                                            <label>{{translate('warranty_title')}} ({{ translate('default') }})</label>
                                            <span class="material-icons">title</span>
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                        @foreach ($language?->live_values as $lang)
                                            <div class="form-floating form-floating__icon mb-30 d-none lang-form" id="{{$lang['code']}}-form">
                                                <input type="text" name="discount_title[]" class="form-control"
                                                       placeholder="{{translate('discount_title')}}">
                                                <label>{{translate('discount_title')}} ({{strtoupper($lang['code'])}})</label>
                                                <span class="material-icons">title</span>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                        @endforeach
                                    @else
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="text" name="discount_title[]" class="form-control"
                                                   placeholder="{{translate('discount_title')}}" required>
                                            <label>{{translate('discount_title')}}</label>
                                            <span class="material-icons">title</span>
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    @endif
                                    <div class="mb-30" id="category_selector">
                                        <select class="category-select theme-input-style w-100" name="category_ids[]"
                                                multiple="multiple" id="category_selector__select" required>
                                            <option value="all">{{translate('Select All')}}</option>
                                            @foreach($categories as $category)
                                                <option value="{{$category->id}}">{{$category->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-30 service_selector" id="service_selector">
                                        <select class="service-select theme-input-style w-100" name="service_ids[]"
                                                multiple="multiple" id="service_selector__select">
                                            <option value="all">{{translate('Select All')}}</option>
                                            @foreach($services as $service)
                                                <option value="{{$service->id}}">{{$service->name}} - {{ $service->subCategory->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-30">
                                        <select class="zone-select theme-input-style w-100" name="zone_ids[]"
                                                multiple="multiple" id="zone_selector__select" required>
                                            <option value="all">{{translate('Select All')}}</option>
                                            @foreach($zones as $zone)
                                                <option value="{{$zone->id}}">{{$zone->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="discount-amount-type">
                                    <div class="mb-3">{{translate('discount_amount_type')}}</div>
                                    <div class="d-flex flex-wrap align-items-center gap-4 mb-30">
                                        <div class="custom-radio">
                                            <input type="radio" id="percentage" name="discount_amount_type"
                                                   value="percent" checked>
                                            <label for="percentage">{{translate('percentage')}}</label>
                                        </div>
                                        <div class="custom-radio">
                                            <input type="radio" id="fixed_amount" name="discount_amount_type"
                                                   value="amount">
                                            <label for="fixed_amount">{{translate('fixed_amount')}}</label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="mb-30">
                                                <div class="form-floating form-floating__icon">
                                                    <input type="number" class="form-control" name="discount_amount"
                                                           placeholder="{{translate('amount')}}" id="discount_amount"
                                                           min="0.01" max="100" step="any" value="0">
                                                    <label id="discount_amount__label">{{translate('amount')}}
                                                        (%)</label>
                                                    <span class="material-icons">price_change</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-30">
                                                <div class="form-floating">
                                                    <input type="date" class="form-control" name="start_date"
                                                           value="{{now()->format('Y-m-d')}}">
                                                    <label>{{translate('Start_Date')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-30">
                                                <div class="form-floating">
                                                    <input type="date" class="form-control" name="end_date"
                                                           value="{{now()->addDays(2)->format('Y-m-d')}}">
                                                    <label>{{translate('End_Date')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-30">
                                                <div class="form-floating form-floating__icon">
                                                    <input type="number" class="form-control" step="any"
                                                           name="min_purchase"
                                                           placeholder="{{translate('min_purchase')}} ({{currency_symbol()}})"
                                                           min="0" value="0">
                                                    <label>{{translate('min_purchase')}} ({{currency_symbol()}})</label>
                                                    <span class="material-icons">price_change</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4" id="max_discount_amount">
                                            <div class="mb-30">
                                                <div class="form-floating form-floating__icon">
                                                    <input type="number" class="form-control" step="any"
                                                           name="max_discount_amount"
                                                           placeholder="{{translate('max_discount')}} ({{currency_symbol()}})"
                                                           min="0" value="0">
                                                    <label>{{translate('max_discount')}} ({{currency_symbol()}})</label>
                                                    <span class="material-icons">price_change</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4" id="limit_per_user__div">
                                            <div class="mb-30">
                                                <div class="form-floating form-floating__icon">
                                                    <input type="number" class="form-control" name="limit_per_user"
                                                           id="limit_per_user" placeholder="1" required>
                                                    <label>{{translate('Limit For Same User')}}</label>
                                                    <span class="material-icons">person</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-3">
                                    <button type="reset" class="btn btn--secondary">{{translate('reset')}}</button>
                                    <button type="submit" class="btn btn--primary">{{translate('submit')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use Strict"

        $('#category_selector__select').on('change', function() {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#service_selector__select').on('change', function() {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('#zone_selector__select').on('change', function() {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });
        $('#category').on('click', function () {
            $('#category_selector').show();
            $('#service_selector').hide();

            $('#category_selector__select').prop('required', true);
            $('#service_selector__select').prop('required', false);
        });

        $('#service').on('click', function () {
            $('#category_selector').hide();
            $('#service_selector').show();

            $('#service_selector__select').prop('required', true);
            $('#category_selector__select').prop('required', false);
        });

        $('#mixed').on('click', function () {
            $('#category_selector').show();
            $('#service_selector').show();

            $('#service_selector__select').prop('required', true);
            $('#category_selector__select').prop('required', true);
        });

        $('#percentage').on('click', function () {
            $('#max_discount_amount').show();

            //Attribute Update
            $('#discount_amount').attr({"max": 100});
            $('#discount_amount__label').html('{{translate('amount')}} (%)');
        });

        $('#fixed_amount').on('click', function () {
            $('#max_discount_amount').hide();

            //Attribute Update
            $('#discount_amount').removeAttr('max');
            $('#discount_amount__label').html('{{translate('amount')}} ({{currency_symbol()}})');

        });

        $('#coupon-type').change(function () {
            if ($(this).val() === 'customer_wise') {
                $("#customer-select__div").removeClass('d-none');
                $("#customer-select").prop('required', true);

            } else {
                $("#customer-select__div").addClass('d-none');
                $("#customer-select").prop('required', false);
            }

            if ($(this).val() === 'first_booking') {
                $("#limit_per_user__div").addClass('d-none');
                $("#limit_per_user").prop('required', false);
            } else {
                $("#limit_per_user__div").removeClass('d-none');
                $("#limit_per_user").prop('required', true);
            }
        });

        //Select 2
        $(".category-select").select2({
            placeholder: "{{translate('Select Machine Category')}}",
        });
        $(".service-select").select2({
            placeholder: "{{translate('Select Service')}}",
        });
        $(".zone-select").select2({
            placeholder: "{{translate('Select Zone')}}",
        });

        $(document).ready(function () {
            $("#customer-select").select2({
                placeholder: "{{translate('Select_customer')}}",
            });
        });

        $(".lang_link").on('click', function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            $("#" + lang + "-form").removeClass('d-none');
        });
    </script>
@endpush
